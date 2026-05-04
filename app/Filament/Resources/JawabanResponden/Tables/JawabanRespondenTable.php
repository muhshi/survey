<?php

namespace App\Filament\Resources\JawabanResponden\Tables;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JawabanRespondenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('survey.title')
                    ->label('Survey')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('user.name')
                    ->label('Responden')
                    ->default('Anonim')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->label('Waktu Submit')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('metadata.ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                SelectFilter::make('survey_id')
                    ->label('Filter Survey')
                    ->relationship('survey', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make()
                    ->infolist([
                        Section::make('Informasi Responden')
                            ->schema([
                                TextEntry::make('survey.title')->label('Survey'),
                                TextEntry::make('user.name')->label('Nama')->default('Anonim'),
                                TextEntry::make('submitted_at')->label('Waktu Submit')->dateTime('d M Y H:i:s'),
                                TextEntry::make('metadata.ip')->label('IP Address'),
                            ])->columns(2),
                        Section::make('Data Jawaban')
                            ->schema([
                                KeyValueEntry::make('payload')->label('Jawaban'),
                            ]),
                        Section::make('Metadata')
                            ->schema([
                                KeyValueEntry::make('metadata')->label('Info Tambahan'),
                            ])
                            ->collapsed(),
                    ]),
            ])
            ->bulkActions([
                //
            ]);
    }
}
