<?php

namespace App\Filament\Resources\JawabanResponden;

use App\Filament\Resources\JawabanResponden\Pages\CreateJawabanResponden;
use App\Filament\Resources\JawabanResponden\Pages\EditJawabanResponden;
use App\Filament\Resources\JawabanResponden\Pages\ListJawabanResponden;
use App\Filament\Resources\JawabanResponden\Schemas\JawabanRespondenForm;
use App\Filament\Resources\JawabanResponden\Tables\JawabanRespondenTable;
use App\Models\JawabanResponden;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JawabanRespondenResource extends Resource
{
    protected static ?string $model = JawabanResponden::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftEllipsis;

    protected static ?string $navigationLabel = 'Jawaban Responden';

    protected static ?string $pluralLabel = 'Jawaban Responden';

    public static function form(Schema $schema): Schema
    {
        return JawabanRespondenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JawabanRespondenTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJawabanResponden::route('/'),
            'create' => CreateJawabanResponden::route('/create'),
            'edit' => EditJawabanResponden::route('/{record}/edit'),
        ];
    }
}
