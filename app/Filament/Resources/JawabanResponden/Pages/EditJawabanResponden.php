<?php

namespace App\Filament\Resources\JawabanResponden\Pages;

use App\Filament\Resources\JawabanResponden\JawabanRespondenResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJawabanResponden extends EditRecord
{
    protected static string $resource = JawabanRespondenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
