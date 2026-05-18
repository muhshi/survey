<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RekapWawancaraWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Rekap Jumlah Wawancara per Pegawai';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereHas('jawaban_responden', fn ($query) => $query->where('survey_id', 3))
                    ->withCount(['jawaban_responden' => fn ($query) => $query->where('survey_id', 3)])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pegawai / Pewawancara')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('metadata.kecamatan')
                    ->label('Kecamatan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('jawaban_responden_count')
                    ->label('Jumlah Diwawancara')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])
            ->defaultSort('jawaban_responden_count', 'desc');
    }
}
