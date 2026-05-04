<?php

namespace App\Filament\Resources\Survey\Pages;

use App\Filament\Forms\Components\SurveyCreator;
use App\Filament\Resources\Survey\SurveyResource;
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
        return [
            // Kosongkan agar tidak ada tombol delete atau lainnya di sini
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Desain'),
            $this->getCancelFormAction()
                ->label('Kembali'),
        ];
    }
}
