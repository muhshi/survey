<?php

namespace App\Filament\Resources\JawabanResponden\Pages;

use App\Filament\Resources\JawabanResponden\JawabanRespondenResource;
use App\Models\JawabanResponden;
use App\Models\Survey;
use App\Services\QuizRecapService;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListJawabanResponden extends ListRecords
{
    protected static string $resource = JawabanRespondenResource::class;

    protected string $view = 'filament.resources.jawaban-responden.pages.list-jawaban-responden';

    #[Url(as: 'survey_id')]
    public ?int $selectedSurveyId = null;

    #[Url(as: 'view')]
    public string $currentTab = 'grafik';

    #[Url(as: 'exclude_pegawai')]
    public bool $excludePegawai = false;

    public function mount(): void
    {
        parent::mount();

        if (! $this->selectedSurveyId) {
            $this->selectedSurveyId = (int) (
                Survey::whereHas('jawabanRespondens')
                    ->orderByDesc('is_active')
                    ->orderByDesc('created_at')
                    ->value('id')
                ?: Survey::orderByDesc('is_active')
                    ->orderByDesc('created_at')
                    ->value('id')
            );
        }
    }

    public function setTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    public function updatedSelectedSurveyId(): void
    {
        unset($this->currentSurvey, $this->questionStats, $this->quizLeaderboard);
        $this->resetTable();
    }

    public function updatedExcludePegawai(): void
    {
        unset($this->questionStats, $this->quizLeaderboard);
    }

    public function getExportUrl(): string
    {
        $submittedFrom = $this->getTableFilterState('submitted_at')['submitted_from'] ?? null;
        $submittedUntil = $this->getTableFilterState('submitted_at')['submitted_until'] ?? null;
        $tableSurveyIds = $this->getTableFilterState('survey_id')['values'] ?? [];

        $surveyIds = ! empty($tableSurveyIds)
            ? $tableSurveyIds
            : ($this->selectedSurveyId ? [$this->selectedSurveyId] : []);

        return route('jawaban-responden.export', array_filter([
            'survey_ids' => $surveyIds,
            'submitted_from' => $submittedFrom,
            'submitted_until' => $submittedUntil,
            'exclude_pegawai' => $this->excludePegawai ? 1 : null,
        ]));
    }

    protected function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery()?->with(['survey.kategori', 'user']);
        $tableSurveyFilter = $this->getTableFilterState('survey_id')['values'] ?? [];

        if (empty($tableSurveyFilter) && $this->selectedSurveyId && $query) {
            $query->where('survey_id', $this->selectedSurveyId);
        }

        return $query;
    }

    public function getSurveyListProperty()
    {
        return Survey::withCount('jawabanRespondens')
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getCurrentSurveyProperty(): ?Survey
    {
        if (! $this->selectedSurveyId) {
            return null;
        }

        return Survey::find($this->selectedSurveyId);
    }

    /**
     * Top 3 participants per class (highest score, fastest submission tie-breaker) for quiz surveys.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getQuizLeaderboardProperty(): array
    {
        $survey = $this->currentSurvey;
        if (! $survey || ! $survey->is_quiz) {
            return [];
        }

        return app(QuizRecapService::class)->getTopPerformersByClass(
            $survey,
            3,
            null,
            null,
            $this->excludePegawai,
        );
    }

    public function getQuestionStatsProperty(): array
    {
        if (! $this->selectedSurveyId) {
            return [];
        }

        $survey = $this->currentSurvey;
        if (! $survey) {
            return [];
        }

        $parsed = $survey->getParsedSchema();
        $fields = $parsed['fields'];
        $choicesMap = $parsed['choices'];

        // For quiz surveys, summarize using 1 best attempt per participant (highest score, fastest time on tie)
        $submissions = $survey->is_quiz
            ? app(QuizRecapService::class)->getBestParticipantAttempts(
                $survey,
                null,
                null,
                null,
                $this->excludePegawai,
            )->pluck('best_attempt')
            : JawabanResponden::where('survey_id', $this->selectedSurveyId)->get();

        $totalResponses = $submissions->count();

        $stats = [];
        foreach ($fields as $key => $title) {
            $stats[$key] = [
                'key' => $key,
                'title' => $title,
                'total_answered' => 0,
                'counts' => [],
                'recent_text' => [],
            ];
        }

        foreach ($submissions as $sub) {
            $payload = $sub->payload ?? [];
            foreach ($fields as $key => $title) {
                $val = $payload[$key] ?? null;
                if ($val !== null && $val !== '') {
                    $stats[$key]['total_answered']++;
                    if (is_array($val)) {
                        foreach ($val as $item) {
                            $vStr = is_string($item) ? $item : json_encode($item);
                            @$stats[$key]['counts'][$vStr]++;
                        }
                    } else {
                        $vStr = trim((string) $val);
                        if ($vStr !== '') {
                            @$stats[$key]['counts'][$vStr]++;
                            if (count($stats[$key]['recent_text']) < 50) {
                                $stats[$key]['recent_text'][] = $vStr;
                            }
                        }
                    }
                }
            }
        }

        foreach ($stats as $key => &$item) {
            arsort($item['counts']);
            $distinctCount = count($item['counts']);
            $hasPredefinedChoices = isset($choicesMap[$key]);

            $isChart = $hasPredefinedChoices || ($distinctCount > 0 && $distinctCount <= 25);
            $item['is_chart'] = $isChart;
            $item['chart_type'] = ($distinctCount <= 6) ? 'donut' : 'bar';
            $item['distinct_count'] = $distinctCount;
        }

        return [
            'total_responses' => $totalResponses,
            'questions' => $stats,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->resetTable()),
        ];
    }
}
