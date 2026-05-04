<?php

namespace App\Filament\Resources\Survey;

use App\Filament\Resources\Survey\Pages\CreateSurvey;
use App\Filament\Resources\Survey\Pages\DesignSurvey;
use App\Filament\Resources\Survey\Pages\EditSurvey;
use App\Filament\Resources\Survey\Pages\ListSurvey;
use App\Filament\Resources\Survey\Pages\ViewSubmissions;
use App\Filament\Resources\Survey\Schemas\SurveyForm;
use App\Filament\Resources\Survey\Tables\SurveyTable;
use App\Models\Survey;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SurveyResource extends Resource
{
    protected static ?string $model = Survey::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Survey';

    protected static ?string $pluralLabel = 'Survey';

    protected static ?string $modelLabel = 'Survey';

    public static function form(Schema $schema): Schema
    {
        return SurveyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SurveyTable::configure($table);
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
            'index' => ListSurvey::route('/'),
            'create' => CreateSurvey::route('/create'),
            'edit' => EditSurvey::route('/{record}/edit'),
            'design' => DesignSurvey::route('/{record}/design'),
            'submissions' => ViewSubmissions::route('/{record}/submissions'),
        ];
    }
}
