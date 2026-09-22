<?php

namespace App\Filament\Resources\JawabanResponden\Pages;

use App\Exports\JawabanRespondenExport;
use App\Filament\Resources\JawabanResponden\JawabanRespondenResource;
use App\Models\JawabanResponden;
use App\Models\Survey;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;

class ListJawabanResponden extends ListRecords
{
    protected static string $resource = JawabanRespondenResource::class;

    protected string $view = 'filament.resources.jawaban-responden.pages.list-jawaban-responden';

    #[Url(as: 'survey_id')]
    public ?int $selectedSurveyId = null;

    #[Url(as: 'view')]
    public string $currentTab = 'grafik';

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
        $this->resetTable();
    }

    protected function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();

        if ($this->selectedSurveyId) {
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

        $submissions = JawabanResponden::where('survey_id', $this->selectedSurveyId)->get();
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
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Select::make('survey_id')
                        ->label('Pilih Survey')
                        ->multiple()
                        ->options(Survey::pluck('title', 'id'))
                        ->default(fn () => $this->selectedSurveyId ? [$this->selectedSurveyId] : ($this->getTableFilterState('survey_id')['values'] ?? null))
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            if (empty($state)) {
                                $set('fields', []);

                                return;
                            }

                            $surveys = Survey::whereIn('id', (array) $state)->get();
                            $schemaFields = [];
                            $hasQuiz = false;
                            $hasNonQuiz = false;

                            foreach ($surveys as $survey) {
                                if ($survey->is_quiz) {
                                    $hasQuiz = true;
                                } else {
                                    $hasNonQuiz = true;

                                    $parsed = $survey->getParsedSchema();
                                    $surveySchemaFields = array_keys($parsed['fields']);

                                    // If schema is empty, fallback to payload keys
                                    if (empty($surveySchemaFields)) {
                                        $surveySchemaFields = JawabanResponden::where('survey_id', $survey->id)
                                            ->get()
                                            ->flatMap(fn ($j) => array_keys($j->payload ?? []))
                                            ->unique()
                                            ->values()
                                            ->toArray();
                                    }
                                    $schemaFields = array_merge($schemaFields, $surveySchemaFields);
                                }
                            }
                            $schemaFields = array_unique($schemaFields);

                            $defaultFields = ['survey_title'];
                            if ($hasNonQuiz) {
                                $defaultFields = array_merge($defaultFields, ['nama_pewawancara', 'nama_peserta', 'email_peserta']);
                            } else {
                                $defaultFields = array_merge($defaultFields, ['nama_lengkap', 'email_peserta']);
                            }
                            if ($hasQuiz) {
                                $defaultFields[] = 'skor_kuis';
                            }
                            $defaultFields[] = 'waktu_submit';

                            $set('fields', array_values(array_unique(array_merge($defaultFields, $schemaFields))));
                        }),
                    CheckboxList::make('fields')
                        ->label('Kolom yang Diekspor')
                        ->bulkToggleable()
                        ->options(function (Get $get) {
                            $surveyIds = $get('survey_id');
                            if (empty($surveyIds)) {
                                return [];
                            }

                            $surveys = Survey::whereIn('id', (array) $surveyIds)->get();
                            if ($surveys->isEmpty()) {
                                return [];
                            }

                            $options = [
                                'survey_title' => 'Judul Survey',
                                'waktu_submit' => 'Waktu Submit',
                                'skor_kuis' => 'Skor Kuis',
                                'nama_pewawancara' => 'Nama Pewawancara',
                                'nama_peserta' => 'Nama Peserta',
                                'email_peserta' => 'Email Peserta',
                            ];

                            foreach ($surveys as $survey) {
                                $parsed = $survey->getParsedSchema();
                                $schemaFields = $parsed['fields'];

                                // Add parsed schema fields
                                foreach ($schemaFields as $key => $title) {
                                    $options[$key] = $title;
                                }

                                // If there are keys in payload that aren't in schema, add them
                                $payloadKeys = JawabanResponden::where('survey_id', $survey->id)
                                    ->get()
                                    ->flatMap(fn ($j) => array_keys($j->payload ?? []))
                                    ->unique()
                                    ->values()
                                    ->toArray();

                                foreach ($payloadKeys as $key) {
                                    if (! isset($options[$key])) {
                                        $options[$key] = ucwords(str_replace('_', ' ', $key));
                                    }
                                }
                            }

                            return $options;
                        })
                        ->default(function (Get $get) {
                            $surveyIds = $get('survey_id');
                            if (empty($surveyIds)) {
                                return [];
                            }

                            $surveys = Survey::whereIn('id', (array) $surveyIds)->get();
                            if ($surveys->isEmpty()) {
                                return [];
                            }

                            $schemaFields = [];
                            $hasQuiz = false;
                            $hasNonQuiz = false;

                            foreach ($surveys as $survey) {
                                if ($survey->is_quiz) {
                                    $hasQuiz = true;
                                } else {
                                    $hasNonQuiz = true;

                                    $parsed = $survey->getParsedSchema();
                                    $surveySchemaFields = array_keys($parsed['fields']);

                                    if (empty($surveySchemaFields)) {
                                        $surveySchemaFields = JawabanResponden::where('survey_id', $survey->id)
                                            ->get()
                                            ->flatMap(fn ($j) => array_keys($j->payload ?? []))
                                            ->unique()
                                            ->values()
                                            ->toArray();
                                    }
                                    $schemaFields = array_merge($schemaFields, $surveySchemaFields);
                                }
                            }
                            $schemaFields = array_unique($schemaFields);

                            $defaultFields = ['survey_title'];
                            if ($hasNonQuiz) {
                                $defaultFields = array_merge($defaultFields, ['nama_pewawancara', 'nama_peserta', 'email_peserta']);
                            } else {
                                $defaultFields = array_merge($defaultFields, ['nama_lengkap', 'email_peserta']);
                            }
                            if ($hasQuiz) {
                                $defaultFields[] = 'skor_kuis';
                            }
                            $defaultFields[] = 'waktu_submit';

                            return array_values(array_unique(array_merge($defaultFields, $schemaFields)));
                        })
                        ->columns(3)
                        ->required()
                        ->visible(fn (Get $get) => filled($get('survey_id'))),
                ])
                ->action(function (array $data) {
                    $fileName = 'Jawaban_Responden_'.date('Y-m-d').'.xlsx';

                    // Get active table filter state
                    $submittedFrom = $this->getTableFilterState('submitted_at')['submitted_from'] ?? null;
                    $submittedUntil = $this->getTableFilterState('submitted_at')['submitted_until'] ?? null;

                    return Excel::download(
                        new JawabanRespondenExport(
                            (array) $data['survey_id'],
                            $data['fields'],
                            $submittedFrom,
                            $submittedUntil
                        ),
                        $fileName
                    );
                }),
            CreateAction::make(),
        ];
    }
}
