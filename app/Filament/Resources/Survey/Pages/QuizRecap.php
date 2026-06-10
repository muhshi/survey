<?php

namespace App\Filament\Resources\Survey\Pages;

use App\Filament\Resources\Survey\SurveyResource;
use App\Models\JawabanResponden;
use App\Models\Survey;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class QuizRecap extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = SurveyResource::class;

    protected string $view = 'filament.resources.survey.pages.quiz-recap';

    protected static ?string $title = 'Rekap Kuis';

    public Survey $record;

    public function mount(Survey $record): void
    {
        $this->record = $record;

        if (! $this->record->is_quiz) {
            abort(404, 'Bukan survei kuis');
        }
    }

    public function table(Table $table): Table
    {
        $passingScore = floatval($this->record->settings['passing_score'] ?? 0);

        return $table
            ->query(
                User::query()
                    ->whereHas('jawaban_responden', function (Builder $query) {
                        $query->where('survey_id', $this->record->id);
                    })
                    ->addSelect([
                        'best_score' => JawabanResponden::select(DB::raw('MAX(score)'))
                            ->whereColumn('user_id', 'users.id')
                            ->where('survey_id', $this->record->id),
                        'attempts_count' => JawabanResponden::select(DB::raw('COUNT(*)'))
                            ->whereColumn('user_id', 'users.id')
                            ->where('survey_id', $this->record->id),
                        'latest_submission' => JawabanResponden::select(DB::raw('MAX(submitted_at)'))
                            ->whereColumn('user_id', 'users.id')
                            ->where('survey_id', $this->record->id),
                    ])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Peserta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('attempts_count')
                    ->label('Total Percobaan')
                    ->sortable(),
                TextColumn::make('best_score')
                    ->label('Skor Terbaik')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 1).'%' : '-'),
                TextColumn::make('passed')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (User $record) use ($passingScore) {
                        return $record->best_score >= $passingScore ? 'Lulus' : 'Belum Lulus';
                    })
                    ->color(function (User $record) use ($passingScore) {
                        return $record->best_score >= $passingScore ? 'success' : 'danger';
                    }),
                TextColumn::make('latest_submission')
                    ->label('Submit Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('best_score', 'asc')
            ->filters([
                Filter::make('passed')
                    ->label('Status Lulus')
                    ->form([
                        Select::make('status')
                            ->options([
                                'passed' => 'Lulus',
                                'failed' => 'Belum Lulus',
                            ])
                            ->label('Pilih Status'),
                    ])
                    ->query(function (Builder $query, array $data) use ($passingScore) {
                        if (empty($data['status'])) {
                            return $query;
                        }

                        if ($data['status'] === 'passed') {
                            $query->whereHas('jawaban_responden', function (Builder $q) use ($passingScore) {
                                $q->where('survey_id', $this->record->id)
                                    ->where('score', '>=', $passingScore);
                            });
                        } elseif ($data['status'] === 'failed') {
                            $query->whereDoesntHave('jawaban_responden', function (Builder $q) use ($passingScore) {
                                $q->where('survey_id', $this->record->id)
                                    ->where('score', '>=', $passingScore);
                            });
                        }

                        return $query;
                    }),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    CheckboxList::make('fields')
                        ->label('Kolom yang Diekspor')
                        ->bulkToggleable()
                        ->options(function () {
                            $options = [
                                'nama_peserta' => 'Peserta',
                                'email_peserta' => 'Email',
                                'attempts_count' => 'Total Percobaan',
                                'best_score' => 'Skor Terbaik',
                                'status_lulus' => 'Status Lulus',
                                'latest_submission' => 'Submit Terakhir',
                            ];

                            $parsed = $this->record->getParsedSchema();
                            $schemaFields = $parsed['fields'] ?? [];

                            foreach ($schemaFields as $key => $title) {
                                $options[$key] = $title;
                            }

                            return $options;
                        })
                        ->default(function () {
                            $defaults = [
                                'nama_peserta',
                                'email_peserta',
                                'attempts_count',
                                'best_score',
                                'status_lulus',
                                'latest_submission',
                            ];

                            $parsed = $this->record->getParsedSchema();
                            $schemaFields = array_keys($parsed['fields'] ?? []);

                            return array_values(array_unique(array_merge($defaults, $schemaFields)));
                        })
                        ->columns(3)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $fileName = 'Rekap_Kuis_'.Str::slug($this->record->title).'_'.date('Y-m-d').'.xlsx';

                    $statusFilter = $this->getTableFilterState('passed')['status'] ?? null;

                    return Excel::download(
                        new QuizRecapExport($this->record, $data['fields'], $statusFilter),
                        $fileName
                    );
                }),
        ];
    }
}
