<?php

namespace App\Filament\Resources\JawabanResponden\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class JawabanRespondenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('survey_id')
                    ->relationship('survey', 'title')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->nullable(),
                KeyValue::make('payload')
                    ->required(),
                KeyValue::make('metadata')
                    ->nullable(),
                DateTimePicker::make('submitted_at'),
            ]);
    }
}
