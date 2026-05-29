<?php

namespace App\Filament\Resources\Groups;

use App\Filament\Resources\Groups\Pages\EditGroup;
use App\Filament\Resources\Groups\Pages\ListGroups;
use App\Filament\Resources\Groups\RelationManagers\UsersRelationManager;
use App\Models\Group;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Kelompok Survei';

    protected static ?string $pluralLabel = 'Kelompok Survei';

    protected static ?string $modelLabel = 'Kelompok';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('survey_id')
                    ->relationship('survey', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Survei'),
                TextInput::make('name')
                    ->required()
                    ->label('Nama Kelompok'),
                DateTimePicker::make('starts_at')
                    ->label('Mulai'),
                DateTimePicker::make('ends_at')
                    ->label('Berakhir'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('survey.title')
                    ->label('Survei')
                    ->sortable()
                    ->searchable()
                    ->limit(30),
                TextColumn::make('name')
                    ->label('Nama Kelompok')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Anggota')
                    ->sortable()
                    ->badge(),
                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Berakhir')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroups::route('/'),
            'edit' => EditGroup::route('/{record}/edit'),
        ];
    }
}
