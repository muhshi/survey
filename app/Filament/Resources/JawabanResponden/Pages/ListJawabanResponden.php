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
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ListJawabanResponden extends ListRecords
{
    protected static string $resource = JawabanRespondenResource::class;

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
                        ->options(Survey::pluck('title', 'id'))
                        ->default(fn () => $this->getTableFilterState('survey_id')['value'] ?? null)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            if (! $state) {
                                $set('fields', []);

                                return;
                            }

                            $survey = Survey::find($state);
                            if (! $survey) {
                                return;
                            }

                            $parsed = $survey->getParsedSchema();
                            $schemaFields = array_keys($parsed['fields']);

                            // If schema is empty, fallback to payload keys
                            if (empty($schemaFields)) {
                                $schemaFields = JawabanResponden::where('survey_id', $state)
                                    ->get()
                                    ->flatMap(fn ($j) => array_keys($j->payload ?? []))
                                    ->unique()
                                    ->values()
                                    ->toArray();
                            }

                            if ($survey->is_quiz) {
                                $defaultFields = ['nama_lengkap', 'email_peserta', 'skor_kuis', 'waktu_submit'];
                            } else {
                                $defaultFields = array_merge(['nama_pewawancara', 'nama_peserta', 'email_peserta', 'waktu_submit'], $schemaFields);
                            }

                            $set('fields', $defaultFields);
                        }),
                    CheckboxList::make('fields')
                        ->label('Kolom yang Diekspor')
                        ->options(function (Get $get) {
                            $surveyId = $get('survey_id');
                            if (! $surveyId) {
                                return [];
                            }

                            $survey = Survey::find($surveyId);
                            if (! $survey) {
                                return [];
                            }

                            $parsed = $survey->getParsedSchema();
                            $schemaFields = $parsed['fields'];

                            $options = [
                                'waktu_submit' => 'Waktu Submit',
                                'skor_kuis' => 'Skor Kuis',
                                'nama_pewawancara' => 'Nama Pewawancara',
                                'nama_peserta' => 'Nama Peserta',
                                'email_peserta' => 'Email Peserta',
                            ];

                            // Add parsed schema fields in exact order
                            foreach ($schemaFields as $key => $title) {
                                $options[$key] = $title;
                            }

                            // If there are keys in payload that aren't in schema, add them at the end
                            $payloadKeys = JawabanResponden::where('survey_id', $surveyId)
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

                            return $options;
                        })
                        ->default(function (Get $get) {
                            $surveyId = $get('survey_id');
                            if (! $surveyId) {
                                return [];
                            }

                            $survey = Survey::find($surveyId);
                            if (! $survey) {
                                return [];
                            }

                            $parsed = $survey->getParsedSchema();
                            $schemaFields = array_keys($parsed['fields']);

                            if (empty($schemaFields)) {
                                $schemaFields = JawabanResponden::where('survey_id', $surveyId)
                                    ->get()
                                    ->flatMap(fn ($j) => array_keys($j->payload ?? []))
                                    ->unique()
                                    ->values()
                                    ->toArray();
                            }

                            if ($survey->is_quiz) {
                                return ['nama_lengkap', 'email_peserta', 'skor_kuis', 'waktu_submit'];
                            } else {
                                return array_merge(['nama_pewawancara', 'nama_peserta', 'email_peserta', 'waktu_submit'], $schemaFields);
                            }
                        })
                        ->columns(3)
                        ->required()
                        ->visible(fn (Get $get) => filled($get('survey_id'))),
                ])
                ->action(function (array $data) {
                    $survey = Survey::find($data['survey_id']);
                    $fileName = Str::slug($survey->title).'_'.date('Y-m-d').'.xlsx';

                    // Get active table filter state
                    $submittedFrom = $this->getTableFilterState('submitted_at')['submitted_from'] ?? null;
                    $submittedUntil = $this->getTableFilterState('submitted_at')['submitted_until'] ?? null;

                    return Excel::download(
                        new JawabanRespondenExport(
                            $data['survey_id'],
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
