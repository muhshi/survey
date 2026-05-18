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
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Select::make('survey_id')
                        ->label('Pilih Survey')
                        ->options(Survey::pluck('title', 'id'))
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            if (! $state) {
                                $set('fields', []);

                                return;
                            }

                            $survey = Survey::find($state);
                            $keys = JawabanResponden::where('survey_id', $state)
                                ->get()
                                ->flatMap(fn ($j) => array_keys($j->payload ?? []))
                                ->unique()
                                ->values()
                                ->toArray();

                            if ($survey && $survey->is_quiz) {
                                $defaultFields = ['nama_lengkap', 'email_peserta', 'skor_kuis', 'waktu_submit'];
                            } else {
                                $defaultFields = array_merge(['nama_pewawancara', 'nama_peserta', 'email_peserta', 'waktu_submit'], $keys);
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

                            $keys = JawabanResponden::where('survey_id', $surveyId)
                                ->get()
                                ->flatMap(fn ($j) => array_keys($j->payload ?? []))
                                ->unique()
                                ->values()
                                ->toArray();

                            $options = [
                                'waktu_submit' => 'Waktu Submit',
                                'skor_kuis' => 'Skor Kuis',
                                'nama_pewawancara' => 'Nama Pewawancara',
                                'nama_peserta' => 'Nama Peserta',
                                'email_peserta' => 'Email Peserta',
                            ];

                            foreach ($keys as $key) {
                                if (! isset($options[$key])) {
                                    $options[$key] = ucwords(str_replace('_', ' ', $key));
                                }
                            }

                            return $options;
                        })
                        ->columns(3)
                        ->required()
                        ->visible(fn (Get $get) => filled($get('survey_id'))),
                ])
                ->action(function (array $data) {
                    $survey = Survey::find($data['survey_id']);
                    $fileName = Str::slug($survey->title).'_'.date('Y-m-d').'.xlsx';

                    return Excel::download(
                        new JawabanRespondenExport($data['survey_id'], $data['fields']),
                        $fileName
                    );
                }),
            CreateAction::make(),
        ];
    }
}
