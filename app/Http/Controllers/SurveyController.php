<?php

namespace App\Http\Controllers;

use App\Enums\SurveyMode;
use App\Models\JawabanResponden;
use App\Models\Kategori;
use App\Models\Survey;
use App\Models\User;
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

        // Check if survey is available and get detailed access status
        $access = $this->checkAccess($survey, Auth::user());
        if (! $access['allowed']) {
            return view('survey.closed', [
                'title' => $access['title'],
                'message' => $access['message'],
            ]);
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
                if ($survey->is_quiz) {
                    $settings = $survey->settings ?? [];
                    $passingScore = floatval($settings['passing_score'] ?? 0);
                    $allowRetake = filter_var($settings['allow_retake'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $maxRetakes = intval($settings['max_retakes'] ?? 1);

                    $attempts = JawabanResponden::where('survey_id', $survey->id)
                        ->where('user_id', Auth::id())
                        ->count();

                    $highestScore = JawabanResponden::where('survey_id', $survey->id)
                        ->where('user_id', Auth::id())
                        ->max('score');
                    $highestScore = $highestScore !== null ? floatval($highestScore) : 0;

                    if ($highestScore >= $passingScore && $passingScore > 0) {
                        $alreadySubmitted = true;
                    } elseif (! $allowRetake) {
                        $alreadySubmitted = true;
                    } elseif ($attempts >= $maxRetakes) {
                        $alreadySubmitted = true;
                    } else {
                        $alreadySubmitted = false;
                    }
                } else {
                    $alreadySubmitted = true;
                }

                // Do not pre-fill answers for single mode — user cannot edit anyway or starting fresh
                $existingSubmission = null;
            }

            // Do not pre-fill form for multi mode so it's always fresh
            if ($survey->mode === SurveyMode::Multi) {
                $existingSubmission = null;
            }

            // Never pre-fill answers for quizzes — each attempt must start fresh
            if ($survey->is_quiz) {
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
        $access = $this->checkAccess($survey, Auth::user());
        if (! $access['allowed']) {
            return response()->json(['success' => false, 'message' => $access['message']], 403);
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
                if ($survey->is_quiz) {
                    $settings = $survey->settings ?? [];
                    $passingScore = floatval($settings['passing_score'] ?? 0);
                    $allowRetake = filter_var($settings['allow_retake'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $maxRetakes = intval($settings['max_retakes'] ?? 1);

                    $attempts = JawabanResponden::where('survey_id', $survey->id)
                        ->where('user_id', Auth::id())
                        ->count();

                    $highestScore = JawabanResponden::where('survey_id', $survey->id)
                        ->where('user_id', Auth::id())
                        ->max('score');
                    $highestScore = $highestScore !== null ? floatval($highestScore) : 0;

                    if ($highestScore >= $passingScore && $passingScore > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Anda sudah lulus kuis ini dengan nilai memenuhi standar.',
                        ], 403);
                    } elseif (! $allowRetake) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Anda sudah mengisi kuis ini dan tidak diizinkan untuk mengulang.',
                        ], 403);
                    } elseif ($attempts >= $maxRetakes) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Batas maksimal pengulangan kuis ('.$maxRetakes.' kali percobaan) telah habis.',
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah mengisi survei ini. Mode sekali isi tidak mengizinkan pengisian ulang.',
                    ], 403);
                }
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

        // Build quiz result data for frontend notification
        $responseData = [
            'success' => true,
            'message' => 'Jawaban Anda telah berhasil disimpan.',
        ];

        if ($survey->is_quiz && $score !== null) {
            $settings = $survey->settings ?? [];
            $passingScore = floatval($settings['passing_score'] ?? 0);
            $allowRetake = filter_var($settings['allow_retake'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $maxRetakes = intval($settings['max_retakes'] ?? 1);

            $attempts = JawabanResponden::where('survey_id', $survey->id)
                ->where('user_id', Auth::id())
                ->count();

            $passed = $passingScore > 0 && $score >= $passingScore;
            $canRetake = ! $passed && $allowRetake && $attempts < $maxRetakes;

            $responseData['quiz'] = [
                'score' => $score,
                'passing_score' => $passingScore,
                'passed' => $passed,
                'can_retake' => $canRetake,
                'attempts' => $attempts,
                'max_retakes' => $maxRetakes,
            ];
        }

        return response()->json($responseData);
    }

    /**
     * Handle editing an existing submission (editable mode).
     */
    public function updateSubmission(Request $request, Survey $survey): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Harus login.'], 401);
        }

        $access = $this->checkAccess($survey, Auth::user());
        if (! $access['allowed']) {
            return response()->json(['success' => false, 'message' => $access['message']], 403);
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
     * Check detailed access status for the user on a survey.
     *
     * @return array{allowed: bool, title: string, message: string}
     */
    private function checkAccess(Survey $survey, ?User $user): array
    {
        if (! $survey->is_active) {
            return [
                'allowed' => false,
                'title' => 'Survei Tidak Aktif',
                'message' => 'Survei ini sudah dinonaktifkan oleh administrator.',
            ];
        }

        // Check groups constraint first
        if ($survey->groups()->exists()) {
            if (! $user) {
                return [
                    'allowed' => false,
                    'title' => 'Harus Login',
                    'message' => 'Silakan masuk terlebih dahulu untuk mengakses survei kelompok ini.',
                ];
            }

            // Find if user is in any of the groups associated with this survey
            $userGroups = $survey->groups()
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->get();

            // If user is not in any group: check if they have role-based fallback access
            if ($userGroups->isEmpty()) {
                if ($survey->access_level === 'role' && ! empty($survey->allowed_roles)) {
                    if ($user->hasAnyRole($survey->allowed_roles)) {
                        // Role-based global check
                        if ($survey->starts_at && $survey->starts_at->isFuture()) {
                            return [
                                'allowed' => false,
                                'title' => 'Survei Belum Dimulai',
                                'message' => 'Survei ini belum dibuka. Akan dimulai pada '.$survey->starts_at->format('d M Y H:i').'.',
                            ];
                        }
                        if ($survey->ends_at && $survey->ends_at->isPast()) {
                            return [
                                'allowed' => false,
                                'title' => 'Survei Sudah Ditutup',
                                'message' => 'Periode pengisian survei ini sudah berakhir pada '.$survey->ends_at->format('d M Y H:i').'.',
                            ];
                        }

                        return [
                            'allowed' => true,
                            'title' => '',
                            'message' => '',
                        ];
                    }
                }

                return [
                    'allowed' => false,
                    'title' => 'Akses Ditolak',
                    'message' => 'Akun Anda ('.$user->email.') tidak terdaftar dalam kelompok peserta survei ini.',
                ];
            }

            // User is in group(s). Let's see if at least one group has a valid active time window.
            $hasFutureAccess = false;
            $hasPastAccess = false;
            $earliestStart = null;
            $latestEnd = null;

            foreach ($userGroups as $group) {
                // Determine effective starts_at/ends_at from pivot or fallback to group defaults
                $effectiveStart = $group->pivot->starts_at ?? $group->starts_at;
                $effectiveEnd = $group->pivot->ends_at ?? $group->ends_at;

                if (is_string($effectiveStart)) {
                    $effectiveStart = \Carbon\Carbon::parse($effectiveStart);
                }
                if (is_string($effectiveEnd)) {
                    $effectiveEnd = \Carbon\Carbon::parse($effectiveEnd);
                }

                $startOk = ! $effectiveStart || $effectiveStart->isPast();
                $endOk = ! $effectiveEnd || $effectiveEnd->isFuture();

                if ($startOk && $endOk) {
                    // Found an active group access window
                    return [
                        'allowed' => true,
                        'title' => '',
                        'message' => '',
                    ];
                }

                if ($effectiveStart && $effectiveStart->isFuture()) {
                    $hasFutureAccess = true;
                    if ($earliestStart === null || $effectiveStart->lt($earliestStart)) {
                        $earliestStart = $effectiveStart;
                    }
                }

                if ($effectiveEnd && $effectiveEnd->isPast()) {
                    $hasPastAccess = true;
                    if ($latestEnd === null || $effectiveEnd->gt($latestEnd)) {
                        $latestEnd = $effectiveEnd;
                    }
                }
            }

            // If we are here, none of the user's groups are active.
            if ($hasFutureAccess) {
                return [
                    'allowed' => false,
                    'title' => 'Akses Belum Dibuka',
                    'message' => 'Periode pengisian kuesioner untuk kelompok Anda belum dimulai. Survei akan dibuka pada '.$earliestStart->format('d M Y H:i').'.',
                ];
            }

            if ($hasPastAccess) {
                return [
                    'allowed' => false,
                    'title' => 'Akses Sudah Ditutup',
                    'message' => 'Periode pengisian kuesioner untuk kelompok Anda sudah berakhir pada '.$latestEnd->format('d M Y H:i').'.',
                ];
            }

            return [
                'allowed' => false,
                'title' => 'Akses Ditolak',
                'message' => 'Periode akses kelompok Anda untuk survei ini saat ini tidak tersedia.',
            ];
        }

        // Global (non-group) availability checks
        if ($survey->starts_at && $survey->starts_at->isFuture()) {
            return [
                'allowed' => false,
                'title' => 'Survei Belum Dimulai',
                'message' => 'Survei ini belum dibuka. Akan dimulai pada '.$survey->starts_at->format('d M Y H:i').'.',
            ];
        }

        if ($survey->ends_at && $survey->ends_at->isPast()) {
            return [
                'allowed' => false,
                'title' => 'Survei Sudah Ditutup',
                'message' => 'Periode pengisian survei ini sudah berakhir pada '.$survey->ends_at->format('d M Y H:i').'.',
            ];
        }

        // Check role requirement
        if ($survey->access_level === 'role') {
            $allowedRoles = $survey->allowed_roles ?? [];
            if (! $user || ! $user->hasAnyRole($allowedRoles)) {
                return [
                    'allowed' => false,
                    'title' => 'Akses Ditolak',
                    'message' => 'Anda tidak memiliki peran (role) yang diizinkan untuk mengakses survei ini.',
                ];
            }
        }

        return [
            'allowed' => true,
            'title' => '',
            'message' => '',
        ];
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
