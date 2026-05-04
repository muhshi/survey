<?php

namespace App\Filament\Resources\Survey\Pages;

use App\Filament\Resources\Survey\SurveyResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSurvey extends EditRecord
{
    protected static string $resource = SurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('design')
                ->label('Desain Kuesioner')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->url(fn () => $this->getResource()::getUrl('design', ['record' => $this->getRecord()])),
            DeleteAction::make(),
        ];
    }
}
