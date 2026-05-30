<?php

namespace App\Filament\Resources\Survey\SurveyResource\RelationManagers;

use App\Filament\Resources\Groups\GroupResource;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    protected static ?string $title = 'Kelompok Survei (Cohorts)';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kelompok')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Anggota')
                    ->sortable()
                    ->badge()
                    ->url(fn ($record) => GroupResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('starts_at')
                    ->label('Mulai (Default Kelompok)')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label('Berakhir (Default Kelompok)')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pivot.starts_at')
                    ->label('Mulai (Khusus Survei)')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('pivot.ends_at')
                    ->label('Berakhir (Khusus Survei)')
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
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Kelompok Baru')
                    ->form([
                        TextInput::make('name')
                            ->required()
                            ->label('Nama Kelompok'),
                        DateTimePicker::make('starts_at')
                            ->label('Mulai (Default Kelompok)')
                            ->helperText('Waktu default untuk kelompok ini di seluruh kuesioner'),
                        DateTimePicker::make('ends_at')
                            ->label('Berakhir (Default Kelompok)')
                            ->helperText('Waktu default untuk kelompok ini di seluruh kuesioner'),
                    ]),
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action) => [
                        $action->getRecordSelect(),
                        DateTimePicker::make('starts_at')
                            ->label('Mulai (Khusus Survei Ini)')
                            ->helperText('Kosongkan untuk menggunakan waktu default kelompok'),
                        DateTimePicker::make('ends_at')
                            ->label('Berakhir (Khusus Survei Ini)')
                            ->helperText('Kosongkan untuk menggunakan waktu default kelompok'),
                    ]),
            ])
            ->actions([
                Action::make('view_group')
                    ->label('Buka Kelompok')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn ($record) => GroupResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->label('Nama Kelompok')
                            ->disabled(),
                        DateTimePicker::make('starts_at')
                            ->label('Mulai (Khusus Survei Ini)')
                            ->helperText('Kosongkan untuk menggunakan waktu default kelompok'),
                        DateTimePicker::make('ends_at')
                            ->label('Berakhir (Khusus Survei Ini)')
                            ->helperText('Kosongkan untuk menggunakan waktu default kelompok'),
                    ]),
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
