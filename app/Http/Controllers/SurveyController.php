<?php

namespace App\Http\Controllers;

use App\Enums\SurveyMode;
use App\Exports\JawabanRespondenExport;
use App\Exports\QuizRecapExport;
use App\Http\Requests\SubmitSurveyRequest;
use App\Http\Requests\UpdateSurveySubmissionRequest;
use App\Models\JawabanResponden;
use App\Models\Kategori;
use App\Models\Survey;
use App\Services\SurveyAccessService;
use App\Services\SurveySubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SurveyController extends Controller
{
    public function __construct(
        private readonly SurveyAccessService $accessService,
        private readonly SurveySubmissionService $submissionService,
    ) {}

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
        $user = Auth::user();
        $isManager = $this->accessService->isSurveyManager($user);
        $isPreview = request()->boolean('preview') || $isManager;

        if (! $isManager && $survey->requiresAuth() && ! Auth::check()) {
            return redirect()->guest(route('filament.admin.auth.login'));
        }

        $access = $this->accessService->checkAccess($survey, $user, $isPreview);
        if (! $access['allowed']) {
            return view('survey.closed', [
                'title' => $access['title'],
                'message' => $access['message'],
            ]);
        }

        $eligibility = $this->submissionService->checkEligibility($survey, $user);
        $alreadySubmitted = $eligibility['already_submitted'];
        $existingSubmission = $this->submissionService->getExistingSubmission($survey, $user);

        return view('survey.show', compact('survey', 'alreadySubmitted', 'existingSubmission', 'isPreview', 'isManager'));
    }

    /**
     * Handle the survey submission with mode enforcement.
     */
    public function submit(SubmitSurveyRequest $request, Survey $survey): JsonResponse
    {
        $user = Auth::user();
        $isManager = $this->accessService->isSurveyManager($user);

        if (! $isManager && $survey->requiresAuth() && ! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Harus login terlebih dahulu.'], 401);
        }

        $access = $this->accessService->checkAccess($survey, $user);
        if (! $access['allowed']) {
            return response()->json(['success' => false, 'message' => $access['message']], 403);
        }

        $eligibility = $this->submissionService->checkEligibility($survey, Auth::user());
        if (! $eligibility['can_submit']) {
            return response()->json(['success' => false, 'message' => $eligibility['message']], 403);
        }

        $metadata = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now()->toDateTimeString(),
        ];

        $response = $this->submissionService->storeSubmission(
            $survey,
            Auth::user(),
            $request->validated('payload'),
            $metadata
        );

        return response()->json($response);
    }

    /**
     * Handle editing an existing submission (editable mode).
     */
    public function updateSubmission(UpdateSurveySubmissionRequest $request, Survey $survey): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Harus login.'], 401);
        }

        $access = $this->accessService->checkAccess($survey, Auth::user());
        if (! $access['allowed']) {
            return response()->json(['success' => false, 'message' => $access['message']], 403);
        }

        if ($survey->mode !== SurveyMode::Editable) {
            return response()->json(['success' => false, 'message' => 'Survei ini tidak mendukung edit.'], 403);
        }

        $metadata = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now()->toDateTimeString(),
        ];

        $response = $this->submissionService->updateSubmission(
            $survey,
            Auth::user(),
            $request->validated('payload'),
            $metadata
        );

        return response()->json($response);
    }

    /**
     * Export recap / respondent submissions of a survey to Excel.
     */
    public function exportRecap(Survey $survey)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $fileName = ($survey->is_quiz ? 'Rekap_Kuis_' : 'Rekap_Survei_').Str::slug($survey->title).'_'.date('Y-m-d').'.xlsx';

        if ($survey->is_quiz) {
            $parsed = $survey->getParsedSchema();
            $schemaFields = $parsed['fields'] ?? [];

            $fields = array_unique(array_merge([
                'nama_peserta',
                'email_peserta',
                'attempts_count',
                'best_score',
                'status_lulus',
                'latest_submission',
            ], array_keys($schemaFields)));

            return Excel::download(
                new QuizRecapExport($survey, $fields),
                $fileName
            );
        }

        $parsed = $survey->getParsedSchema();
        $schemaFields = $parsed['fields'] ?? [];
        $fields = array_unique(array_merge([
            'nama_peserta',
            'email_peserta',
            'waktu_submit',
            'skor_kuis',
        ], array_keys($schemaFields)));

        return Excel::download(
            new JawabanRespondenExport([$survey->id], $fields),
            $fileName
        );
    }

    /**
     * Export respondent submissions directly via HTTP GET (prevents Livewire AJAX download blocking).
     */
    public function exportJawabanResponden(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $params = [];
        if ($request->filled('token')) {
            $params = Cache::pull('jawaban_export_'.$request->string('token')->toString()) ?? [];
        }

        $surveyIds = (array) ($params['survey_ids'] ?? $request->input('survey_ids', $request->input('survey_id', [])));
        $fields = (array) ($params['fields'] ?? $request->input('fields', []));
        $submittedFrom = $params['submitted_from'] ?? $request->input('submitted_from');
        $submittedUntil = $params['submitted_until'] ?? $request->input('submitted_until');

        if (empty($surveyIds)) {
            abort(400, 'Survey belum dipilih untuk diekspor.');
        }

        $surveys = Survey::whereIn('id', $surveyIds)->get();
        if ($surveys->isEmpty()) {
            abort(404, 'Survey tidak ditemukan.');
        }

        if (empty($fields)) {
            $fields = ['survey_title', 'nama_lengkap', 'email_peserta', 'waktu_submit'];
            foreach ($surveys as $survey) {
                if ($survey->is_quiz) {
                    $fields[] = 'skor_kuis';
                }
                $parsed = $survey->getParsedSchema();
                $fields = array_merge($fields, array_keys($parsed['fields'] ?? []));
            }
            $fields = array_values(array_unique($fields));
        }

        $fileName = count($surveyIds) === 1 && $surveys->first()
            ? 'Jawaban_'.Str::slug($surveys->first()->title).'_'.date('Y-m-d').'.xlsx'
            : 'Jawaban_Responden_'.date('Y-m-d').'.xlsx';

        return Excel::download(
            new JawabanRespondenExport(
                $surveyIds,
                $fields,
                $submittedFrom,
                $submittedUntil
            ),
            $fileName
        );
    }
}
