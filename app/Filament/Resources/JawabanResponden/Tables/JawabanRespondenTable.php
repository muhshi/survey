<?php

namespace App\Filament\Resources\JawabanResponden\Tables;

use App\Filament\Resources\JawabanResponden\Pages\ListJawabanResponden;
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

                TextColumn::make('peserta')
                    ->label('Responden / Peserta')
                    ->getStateUsing(function ($record) {
                        // 1. User login terdaftar
                        if ($record->user) {
                            return $record->user->name;
                        }

                        // 2. Survei wawancara (id 3)
                        if ($record->survey_id == 3 && isset($record->payload['nama_peserta'])) {
                            $pesertaId = $record->payload['nama_peserta'];
                            $peserta = is_numeric($pesertaId) ? User::find($pesertaId) : null;

                            return $peserta ? $peserta->name.' (Wawancara)' : ($pesertaId ?: 'Anonim');
                        }

                        // 3. Nama lengkap di payload
                        if (! empty($record->payload['nama_lengkap'])) {
                            return $record->payload['nama_lengkap'];
                        }

                        // 4. Nama peserta di payload
                        if (! empty($record->payload['nama_peserta'])) {
                            return $record->payload['nama_peserta'];
                        }

                        // 5. Nama KK di payload
                        if (! empty($record->payload['nama_kk'])) {
                            return $record->payload['nama_kk'].' (KK)';
                        }

                        return 'Anonim';
                    })
                    ->description(function ($record) {
                        $items = [];
                        if (! empty($record->payload['nama_opd'])) {
                            $items[] = '🏢 '.$record->payload['nama_opd'];
                        }
                        if (! empty($record->payload['nik'])) {
                            $items[] = 'NIK: '.$record->payload['nik'];
                        } elseif (! empty($record->payload['email_peserta'])) {
                            $items[] = $record->payload['email_peserta'];
                        } elseif ($record->user?->email) {
                            $items[] = $record->user->email;
                        } elseif (! empty($record->payload['no_hp'])) {
                            $items[] = 'WA: '.$record->payload['no_hp'];
                        }

                        return implode(' • ', $items);
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                                ->orWhere('payload->nama_lengkap', 'like', "%{$search}%")
                                ->orWhere('payload->nama_peserta', 'like', "%{$search}%")
                                ->orWhere('payload->nama_opd', 'like', "%{$search}%")
                                ->orWhere('payload->nik', 'like', "%{$search}%")
                                ->orWhere('payload->no_hp', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('user_id', $direction);
                    }),

                TextColumn::make('survey.title')
                    ->label('Survei')
                    ->limit(28)
                    ->description(function ($record) {
                        $kategori = $record->survey?->kategori?->name ?? 'Umum';
                        $mode = $record->survey?->is_quiz ? 'Kuis' : ucfirst($record->survey?->mode?->value ?? 'Survei');

                        return "{$kategori} • {$mode}";
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('opd')
                    ->label('Instansi / OPD')
                    ->getStateUsing(fn ($record) => $record->payload['nama_opd'] ?? null)
                    ->placeholder('-')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->payload['nama_opd'] ?? null)
                    ->searchable(query: function ($query, $search) {
                        $query->where('payload->nama_opd', 'like', "%{$search}%");
                    })
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        if (! empty($record->payload['status_pendataan'])) {
                            return $record->payload['status_pendataan'];
                        }
                        if (! empty($record->payload['status_kepegawaian'])) {
                            return $record->payload['status_kepegawaian'];
                        }

                        return null;
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                        'sudah', 'selesai' => 'success',
                        'belum', 'proses' => 'warning',
                        'pns', 'pppk' => 'info',
                        default => 'gray',
                    })
                    ->placeholder('-')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('payload->status_pendataan', $direction)
                            ->orderBy('payload->status_kepegawaian', $direction);
                    })
                    ->toggleable(),

                TextColumn::make('wilayah')
                    ->label('Wilayah / Domisili')
                    ->getStateUsing(function ($record) {
                        $parts = array_filter([
                            $record->payload['desa'] ?? null,
                            $record->payload['kecamatan'] ?? null,
                        ]);
                        if (! empty($parts)) {
                            return implode(', Kec. ', $parts);
                        }
                        if (! empty($record->payload['sls'])) {
                            return $record->payload['sls'];
                        }
                        if (! empty($record->payload['alamat_lengkap'])) {
                            return $record->payload['alamat_lengkap'];
                        }

                        return null;
                    })
                    ->placeholder('-')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->payload['alamat_lengkap'] ?? null)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('score')
                    ->label('Skor Kuis')
                    ->getStateUsing(function ($record) {
                        if ($record->score !== null) {
                            return round($record->score);
                        }

                        return null;
                    })
                    ->badge()
                    ->color(fn (?float $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->suffix(fn ($record) => $record->score !== null ? '%' : '')
                    ->placeholder('-')
                    ->sortable()
                    ->visible(function ($livewire) {
                        if ($livewire instanceof ListJawabanResponden) {
                            return (bool) $livewire->currentSurvey?->is_quiz;
                        }

                        return false;
                    })
                    ->toggleable(),

                TextColumn::make('submitted_at')
                    ->label('Waktu Submit')
                    ->dateTime('d M Y, H:i')
                    ->description(fn ($record) => $record->submitted_at?->diffForHumans())
                    ->sortable(),

                TextColumn::make('metadata.ip')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
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
                                            $peserta = is_numeric($pesertaId) ? User::find($pesertaId) : null;
                                            $name = $peserta ? $peserta->name : (is_string($pesertaId) ? $pesertaId : 'ID: '.$pesertaId);
                                            $interviewer = $record->user ? $record->user->name : 'Anonim';

                                            return $name." (Diwawancarai oleh: {$interviewer})";
                                        }

                                        if ($record->user) {
                                            return $record->user->name;
                                        }

                                        return $record->payload['nama_lengkap']
                                            ?? $record->payload['nama_peserta']
                                            ?? $record->payload['nama_kk']
                                            ?? 'Anonim';
                                    }),
                                TextEntry::make('submitted_at')->label('Waktu Submit')->dateTime('d M Y H:i:s'),
                                TextEntry::make('score')
                                    ->label('Skor Kuis')
                                    ->suffix('%')
                                    ->weight('bold')
                                    ->color('primary')
                                    ->visible(fn ($record) => (bool) $record?->survey?->is_quiz),
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
