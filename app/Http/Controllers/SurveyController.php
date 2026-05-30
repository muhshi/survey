<?php

namespace App\Http\Controllers;

use App\Enums\SurveyMode;
use App\Models\JawabanResponden;
use App\Models\Kategori;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SurveyController extends Controller
{
    /**
     * Display the public survey listing page.
     */
    public function index(): View
    {
        $surveys = Survey::with('kategori')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->accessibleBy(Auth::user())
            ->orderByDesc('created_at')
            ->get();

        $categories = Kategori::whereHas('surveys', function ($query) {
            $query->where('is_active', true);
        })->get();

        return view('survey.index', compact('surveys', 'categories'));
    }

    /**
     * Display the landing page with stats.
     */
    public function landing(): View
    {
        $totalSurveys = Survey::where('is_active', true)->count();
        $totalResponses = JawabanResponden::count();
        $totalCategories = Kategori::count();

        $recentSurveys = Survey::with('kategori')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->accessibleBy(Auth::user())
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        return view('welcome', compact('totalSurveys', 'totalResponses', 'totalCategories', 'recentSurveys'));
    }

    /**
     * Display the survey runner.
     */
    public function show(Survey $survey): View|RedirectResponse
    {
        // Access control
        if ($survey->requiresAuth() && ! Auth::check()) {
            return redirect()->guest(route('filament.admin.auth.login'));
        }

        // Check if survey is available
        if (! $survey->isAvailableForUser(Auth::user())) {
            return view('survey.closed', [
                'title' => 'Survei Tidak Tersedia / Akses Ditolak',
                'message' => $this->getUnavailableMessage($survey),
            ]);
        }

        if ($survey->access_level === 'role') {
            $allowedRoles = $survey->allowed_roles ?? [];
            if (! Auth::user()->hasAnyRole($allowedRoles) && ! $survey->hasActiveGroupAccess(Auth::user())) {
                return view('survey.closed', [
                    'title' => 'Akses Ditolak',
                    'message' => 'Anda tidak memiliki izin untuk mengisi survei ini.',
                ]);
            }
        }

        // Mode-specific checks
        $alreadySubmitted = false;
        $existingSubmission = null;

        if (Auth::check()) {
            $existingSubmission = JawabanResponden::where('survey_id', $survey->id)
                ->where('user_id', Auth::id())
                ->latest()
                ->first();

            if ($existingSubmission && $survey->mode === SurveyMode::Single) {
                $alreadySubmitted = true;
            }

            // Do not pre-fill form for multi mode so it's always fresh
            if ($survey->mode === SurveyMode::Multi) {
                $existingSubmission = null;
            }
        }

        return view('survey.show', compact('survey', 'alreadySubmitted', 'existingSubmission'));
    }

    /**
     * Handle the survey submission with mode enforcement.
     */
    public function submit(Request $request, Survey $survey): JsonResponse
    {
        // Access control
        if ($survey->requiresAuth() && ! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Harus login terlebih dahulu.'], 401);
        }

        // Availability check
        if (! $survey->isAvailableForUser(Auth::user())) {
            return response()->json(['success' => false, 'message' => 'Survei tidak tersedia atau akses ditolak.'], 403);
        }

        if ($survey->access_level === 'role') {
            $allowedRoles = $survey->allowed_roles ?? [];
            if (! Auth::user()->hasAnyRole($allowedRoles) && ! $survey->hasActiveGroupAccess(Auth::user())) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses.'], 403);
            }
        }

        $request->validate([
            'payload' => 'required|array',
        ]);

        // Mode enforcement
        if (Auth::check() && $survey->mode === SurveyMode::Single) {
            $existing = JawabanResponden::where('survey_id', $survey->id)
                ->where('user_id', Auth::id())
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah mengisi survei ini. Mode sekali isi tidak mengizinkan pengisian ulang.',
                ], 403);
            }
        }

        // Calculate score if it's a quiz
        $score = null;
        if ($survey->is_quiz && $survey->schema) {
            $score = $this->calculateScore($survey, $request->payload);
        }

        // Create new submission
        $jawaban = new JawabanResponden;
        $jawaban->survey_id = $survey->id;
        $jawaban->user_id = Auth::id();
        $jawaban->payload = $request->payload;
        $jawaban->score = $score;
        $jawaban->metadata = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now()->toDateTimeString(),
        ];
        $jawaban->submitted_at = Carbon::now();
        $jawaban->save();

        return response()->json([
            'success' => true,
            'message' => 'Jawaban Anda telah berhasil disimpan.',
        ]);
    }

    /**
     * Handle editing an existing submission (editable mode).
     */
    public function updateSubmission(Request $request, Survey $survey): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Harus login.'], 401);
        }

        if (! $survey->isAvailableForUser(Auth::user())) {
            return response()->json(['success' => false, 'message' => 'Survei tidak tersedia atau akses ditolak.'], 403);
        }

        if ($survey->mode !== SurveyMode::Editable) {
            return response()->json(['success' => false, 'message' => 'Survei ini tidak mendukung edit.'], 403);
        }

        $request->validate([
            'payload' => 'required|array',
        ]);

        $existing = JawabanResponden::where('survey_id', $survey->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        if ($existing) {
            $existing->payload = $request->payload;

            // Recalculate score if it's a quiz
            if ($survey->is_quiz && $survey->schema) {
                $existing->score = $this->calculateScore($survey, $request->payload);
            }

            $existing->metadata = array_merge($existing->metadata ?? [], [
                'last_edited_at' => now()->toDateTimeString(),
                'edit_ip' => $request->ip(),
            ]);
            $existing->save();
        } else {
            // Calculate score if it's a quiz
            $score = null;
            if ($survey->is_quiz && $survey->schema) {
                $score = $this->calculateScore($survey, $request->payload);
            }

            // First submission in editable mode
            $jawaban = new JawabanResponden;
            $jawaban->survey_id = $survey->id;
            $jawaban->user_id = Auth::id();
            $jawaban->payload = $request->payload;
            $jawaban->score = $score;
            $jawaban->metadata = [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'submitted_at' => now()->toDateTimeString(),
            ];
            $jawaban->submitted_at = Carbon::now();
            $jawaban->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Jawaban berhasil diperbarui.',
        ]);
    }

    /**
     * Get unavailable message based on survey state.
     */
    private function getUnavailableMessage(Survey $survey): string
    {
        if (! $survey->is_active) {
            return 'Survei ini sudah dinonaktifkan oleh administrator.';
        }

        if ($survey->starts_at && $survey->starts_at->isFuture()) {
            return 'Survei ini belum dibuka. Akan dimulai pada '.$survey->starts_at->format('d M Y H:i').'.';
        }

        if ($survey->ends_at && $survey->ends_at->isPast()) {
            return 'Periode pengisian survei ini sudah berakhir pada '.$survey->ends_at->format('d M Y H:i').'.';
        }

        return 'Survei tidak tersedia saat ini.';
    }

    /**
     * Calculate score based on survey schema and payload.
     */
    private function calculateScore(Survey $survey, array $payload): float
    {
        $totalPoints = 0;
        $earnedPoints = 0;

        $schema = $survey->schema;
        if (! isset($schema['pages']) || ! is_array($schema['pages'])) {
            return 0;
        }

        foreach ($schema['pages'] as $page) {
            if (! isset($page['elements']) || ! is_array($page['elements'])) {
                continue;
            }

            foreach ($page['elements'] as $element) {
                if (isset($element['correctAnswer'])) {
                    // Get points for this question, default to 1 if not set
                    $points = isset($element['score']) ? (float) $element['score'] : 1;
                    $totalPoints += $points;

                    $questionName = $element['name'];
                    $correctAnswer = $element['correctAnswer'];

                    // Check if question is answered and matches correct answer
                    if (isset($payload[$questionName])) {
                        $userAnswer = $payload[$questionName];

                        // Handle potential different types (SurveyJS radio is often string)
                        if ($userAnswer == $correctAnswer) {
                            $earnedPoints += $points;
                        }
                    }
                }
            }
        }

        if ($totalPoints === 0) {
            return 0;
        }

        return round(($earnedPoints / $totalPoints) * 100, 2);
    }
}
