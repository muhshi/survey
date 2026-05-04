<?php

namespace App\Filament\Resources\JawabanResponden\Pages;

use App\Filament\Resources\JawabanResponden\JawabanRespondenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJawabanResponden extends ListRecords
{
    protected static string $resource = JawabanRespondenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
