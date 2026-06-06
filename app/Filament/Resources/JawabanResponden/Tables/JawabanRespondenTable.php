<?php

namespace App\Filament\Resources\JawabanResponden\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                TextColumn::make('peserta')
                    ->label('Responden / Peserta')
                    ->getStateUsing(function ($record) {
                        // Jika survei wawancara (id 3) dan ada nama_peserta di payload
                        if ($record->survey_id == 3 && isset($record->payload['nama_peserta'])) {
                            $pesertaId = $record->payload['nama_peserta'];
                            $peserta = User::find($pesertaId);
                            if ($peserta) {
                                return $peserta->name.' (Wawancara)';
                            } else {
                                $name = is_string($pesertaId) ? $pesertaId : 'ID: '.$pesertaId;

                                return $name.' (Wawancara)';
                            }
                        }

                        return $record->user ? $record->user->name : 'Anonim';
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('user', function ($uq) use ($search) {
                                $uq->where('name', 'like', "%{$search}%");
                            })->orWhere(function ($pq) use ($search) {
                                $pq->where('survey_id', 3)
                                    ->whereIn('payload->nama_peserta', User::where('name', 'like', "%{$search}%")->pluck('id'));
                            })->orWhere('payload->nama_lengkap', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('user_id', $direction);
                    }),
                TextColumn::make('score')
                    ->label('Skor')
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->sortable()
                    ->suffix('%')
                    ->visible(fn ($record) => true),
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
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Filter::make('submitted_at')
                    ->label('Filter Tanggal')
                    ->form([
                        DatePicker::make('submitted_from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('submitted_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['submitted_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('submitted_at', '>=', $date),
                            )
                            ->when(
                                $data['submitted_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('submitted_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->infolist([
                        Section::make('Informasi Responden / Wawancara')
                            ->schema([
                                TextEntry::make('survey.title')->label('Survey'),
                                TextEntry::make('peserta')
                                    ->label('Peserta / Pewawancara')
                                    ->getStateUsing(function ($record) {
                                        if ($record->survey_id == 3 && isset($record->payload['nama_peserta'])) {
                                            $pesertaId = $record->payload['nama_peserta'];
                                            $peserta = User::find($pesertaId);
                                            $name = $peserta ? $peserta->name : (is_string($pesertaId) ? $pesertaId : 'ID: '.$pesertaId);
                                            $interviewer = $record->user ? $record->user->name : 'Anonim';

                                            return $name." (Diwawancarai oleh: $interviewer)";
                                        }

                                        return $record->user ? $record->user->name : 'Anonim';
                                    }),
                                TextEntry::make('submitted_at')->label('Waktu Submit')->dateTime('d M Y H:i:s'),
                                TextEntry::make('score')
                                    ->label('Skor Kuis')
                                    ->suffix('%')
                                    ->weight('bold')
                                    ->color('primary')
                                    ->visible(fn ($record) => $record?->survey?->is_quiz || $record?->score !== null),
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
