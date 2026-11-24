<?php

namespace App\Filament\Resources\Survey\Pages;

use App\Filament\Forms\Components\SurveyCreator;
use App\Filament\Resources\Survey\SurveyResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class DesignSurvey extends EditRecord
{
    protected static string $resource = SurveyResource::class;

    protected static ?string $title = 'Desain Kuesioner';

    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    public function getHeading(): string
    {
        return 'Desain Kuesioner';
    }

    public function getSubheading(): ?string
    {
        $survey = $this->getRecord();
        $mode = $survey->is_quiz ? 'Mode Kuis' : 'Mode Survei';
        $status = $survey->is_active ? 'Aktif' : 'Draft / Nonaktif';

        return "{$survey->title} • {$mode} • {$status}";
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SurveyCreator::make('schema')
                    ->hiddenLabel()
                    ->fullScreen()
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $survey = $this->getRecord();

        return [
            Action::make('back')
                ->label('Kembali ke Edit')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => SurveyResource::getUrl('edit', ['record' => $survey])),

            Action::make('preview')
                ->label('Pratinjau Kuesioner')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => route('survey.show', ['survey' => $survey->slug]))
                ->openUrlInNewTab(),

            Action::make('save_top')
                ->label('Simpan Desain')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Desain')
                ->icon('heroicon-o-check'),
            $this->getCancelFormAction()
                ->label('Kembali ke Edit')
                ->icon('heroicon-o-arrow-left')
                ->url(fn (): string => SurveyResource::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }
}
