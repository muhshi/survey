<?php

namespace App\Filament\Resources\Survey\Tables;

use App\Filament\Resources\Survey\SurveyResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SurveyTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('jawabanRespondens'))
            ->columns([
                TextColumn::make('kategori.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->sortable()
                    ->searchable()
                    ->limit(40),
                TextColumn::make('mode')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('access_level')
                    ->label('Akses')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'public' => 'Umum',
                        'auth' => 'Login',
                        'role' => 'Role',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'auth' => 'warning',
                        'role' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('jawaban_respondens_count')
                    ->label('Jawaban')
                    ->counts('jawabanRespondens')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('starts_at')
                    ->label('Berlaku Dari')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label('Berlaku Sampai')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                SelectFilter::make('access_level')
                    ->label('Level Akses')
                    ->options([
                        'public' => 'Umum',
                        'auth' => 'Login',
                        'role' => 'Role',
                    ]),
            ])
            ->recordActions([
                Action::make('design')
                    ->label('Desain')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->url(fn ($record) => SurveyResource::getUrl('design', ['record' => $record])),
                Action::make('viewSubmissions')
                    ->label('Jawaban')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => SurveyResource::getUrl('submissions', ['record' => $record])),
                Action::make('copyLink')
                    ->label('Salin Link')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->action(function ($record, $livewire) {
                        $livewire->js("navigator.clipboard.writeText('".$record->getPublicUrl()."'); \$tooltip('Link disalin!', { timeout: 2000 });");
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
