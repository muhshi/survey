<?php

namespace App\Filament\Resources\Survey\Tables;

use App\Filament\Resources\Survey\SurveyResource;
use App\Models\JawabanResponden;
use App\Models\Survey;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class SurveyTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('jawabanRespondens')->with(['groups', 'kategori']))
            ->recordUrl(null)
            ->columns([
                TextColumn::make('title')
                    ->label('Survei')
                    ->sortable()
                    ->searchable(['title', 'slug', 'kategori.name'])
                    ->view('filament.tables.columns.survey-title')
                    ->wrap()
                    ->width('440px'),
                TextColumn::make('mode')
                    ->badge()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable()
                    ->afterStateUpdated(function (Survey $record, bool $state) {
                        Notification::make()
                            ->title($state ? "Survei '{$record->title}' diaktifkan" : "Survei '{$record->title}' dinonaktifkan")
                            ->color($state ? 'success' : 'warning')
                            ->send();
                    }),
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
                    })
                    ->tooltip('Klik untuk mengubah level akses')
                    ->action(
                        Action::make('changeAccessLevel')
                            ->label('Ubah Akses')
                            ->modalHeading(fn (Survey $record) => "Ubah Akses Survei: {$record->title}")
                            ->modalDescription('Pilih pengaturan hak akses untuk survei ini.')
                            ->modalIcon('heroicon-o-lock-closed')
                            ->modalWidth('md')
                            ->modalSubmitActionLabel('Simpan')
                            ->fillForm(fn (Survey $record): array => [
                                'access_level' => $record->access_level,
                                'allowed_roles' => $record->allowed_roles ?? [],
                            ])
                            ->form([
                                Select::make('access_level')
                                    ->label('Level Akses')
                                    ->options([
                                        'public' => 'Umum (Tanpa Login)',
                                        'auth' => 'Harus Login',
                                        'role' => 'Role Spesifik',
                                    ])
                                    ->required()
                                    ->live(),
                                Select::make('allowed_roles')
                                    ->label('Role yang Diizinkan')
                                    ->options(fn () => Role::pluck('name', 'name')->toArray())
                                    ->multiple()
                                    ->visible(fn (Get $get) => $get('access_level') === 'role')
                                    ->required(fn (Get $get) => $get('access_level') === 'role'),
                            ])
                            ->action(function (Survey $record, array $data): void {
                                $record->update([
                                    'access_level' => $data['access_level'],
                                    'allowed_roles' => $data['access_level'] === 'role' ? ($data['allowed_roles'] ?? []) : null,
                                ]);

                                Notification::make()
                                    ->title('Level akses berhasil diperbarui')
                                    ->success()
                                    ->send();
                            })
                    )
                    ->sortable(),
                TextColumn::make('jawaban_respondens_count')
                    ->label('Jawaban')
                    ->getStateUsing(function ($record) {
                        $groups = $record->groups;

                        if ($groups->isEmpty()) {
                            return $record->jawaban_respondens_count;
                        }

                        $groupIds = $groups->pluck('id')->all();

                        $totalGroupUsers = DB::table('group_user')
                            ->whereIn('group_id', $groupIds)
                            ->distinct('user_id')
                            ->count('user_id');

                        if ($totalGroupUsers === 0) {
                            return $record->jawaban_respondens_count;
                        }

                        $submittedGroupUsers = $record->jawabanRespondens()
                            ->whereIn('user_id', function ($query) use ($groupIds) {
                                $query->select('user_id')
                                    ->from('group_user')
                                    ->whereIn('group_id', $groupIds);
                            })
                            ->distinct('user_id')
                            ->count('user_id');

                        return "{$submittedGroupUsers} / {$totalGroupUsers}";
                    })
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->tooltip('Klik untuk melihat siapa yang sudah & belum mengisi')
                    ->action(
                        Action::make('statusRespondenColumn')
                            ->modalHeading(fn (Survey $record): string => "Status Responden: {$record->title}")
                            ->modalDescription('Pantau peserta yang sudah dan belum mengisi survei untuk memudahkan pengingatan.')
                            ->modalIcon('heroicon-o-user-group')
                            ->modalWidth('5xl')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->modalContent(fn (Survey $record) => view('filament.resources.survey.modals.respondents-status', [
                                'record' => $record,
                                'data' => static::getRespondentsStatusData($record),
                            ]))
                    ),
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
                    ->tooltip('Buka Form Builder')
                    ->url(fn ($record) => SurveyResource::getUrl('design', ['record' => $record])),
                ActionGroup::make([
                    Action::make('copyLinkMenu')
                        ->label('Salin Link Survei')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('info')
                        ->action(function (Survey $record) {
                            Notification::make()
                                ->title('Tautan survei berhasil disalin!')
                                ->success()
                                ->send();
                        })
                        ->extraAttributes(fn (Survey $record) => [
                            'x-on:click.stop' => "navigator.clipboard.writeText('{$record->getPublicUrl()}')",
                        ]),
                    Action::make('openSurvey')
                        ->label('Buka Survei (Tab Baru)')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('gray')
                        ->url(fn (Survey $record) => $record->getPublicUrl())
                        ->openUrlInNewTab(),
                    Action::make('viewSubmissions')
                        ->label('Lihat Semua Jawaban')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn ($record) => SurveyResource::getUrl('submissions', ['record' => $record])),
                    Action::make('exportRecap')
                        ->label(fn (Survey $record) => $record->is_quiz ? 'Export Rekap Kuis (Excel)' : 'Export Jawaban (Excel)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->url(fn (Survey $record) => route('survey.export-recap', $record))
                        ->openUrlInNewTab(),
                    Action::make('recapQuiz')
                        ->label('Halaman Rekap Kuis')
                        ->icon('heroicon-o-chart-bar')
                        ->color('success')
                        ->url(fn ($record) => SurveyResource::getUrl('recap', ['record' => $record]))
                        ->visible(fn ($record) => $record->is_quiz),
                    EditAction::make(),
                    ReplicateAction::make()
                        ->label('Duplikat')
                        ->excludeAttributes(['slug', 'created_at', 'updated_at'])
                        ->form([
                            TextInput::make('title')
                                ->label('Judul Survei/Kuis Baru')
                                ->required(),
                            Select::make('kategori_id')
                                ->label('Kategori')
                                ->relationship('kategori', 'name')
                                ->required(),
                            Toggle::make('is_quiz')
                                ->label('Jadikan Mode Kuis?'),
                        ])
                        ->mutateRecordDataUsing(function (array $data): array {
                            $data['title'] = $data['title'].' (Copy)';

                            return $data;
                        })
                        ->beforeReplicaSaved(function (Model $replica): void {
                            $replica->offsetUnset('jawaban_respondens_count');
                        })
                        ->successRedirectUrl(fn (Model $replica): string => SurveyResource::getUrl('edit', ['record' => $replica])),
                    DeleteAction::make(),
                ])
                    ->tooltip('Aksi Lainnya')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Compile complete respondents status data (who has and who hasn't answered yet).
     *
     * @return array<string, mixed>
     */
    public static function getRespondentsStatusData(Survey $record): array
    {
        $record->loadMissing(['groups.users']);
        $groups = $record->groups;
        $hasGroups = $groups->isNotEmpty();

        if ($hasGroups) {
            $groupIds = $groups->pluck('id')->all();
            $targetUsers = User::query()
                ->whereIn('id', function ($query) use ($groupIds) {
                    $query->select('user_id')->from('group_user')->whereIn('group_id', $groupIds);
                })
                ->orderBy('name')
                ->get();
            $targetScope = 'Kelompok: '.$groups->pluck('name')->implode(', ');
        } elseif ($record->access_level === 'role' && ! empty($record->allowed_roles)) {
            $targetUsers = User::role($record->allowed_roles)->active()->orderBy('name')->get();
            $targetScope = 'Role: '.implode(', ', $record->allowed_roles);
        } elseif ($record->access_level === 'auth') {
            $targetUsers = User::active()->orderBy('name')->get();
            $targetScope = 'Semua Pengguna Terdaftar (Login)';
        } else {
            $targetUsers = collect();
            $targetScope = 'Publik (Tanpa Kelompok Khusus)';
        }

        $submissions = JawabanResponden::query()
            ->where('survey_id', $record->id)
            ->with('user')
            ->orderBy('submitted_at', 'desc')
            ->get();

        $submittedByUser = $submissions->whereNotNull('user_id')->groupBy('user_id');
        $submittedUserIds = $submissions->whereNotNull('user_id')->pluck('user_id')->unique()->all();

        $sudah = [];
        $passingScore = floatval($record->settings['passing_score'] ?? 0);

        // 1) Registered users who submitted
        foreach ($submittedByUser as $userId => $items) {
            $user = $items->first()->user;
            $latest = $items->first();
            $bestScore = $record->is_quiz ? $items->max('score') : null;
            $phone = $user?->nomor_hp ?: ($user?->metadata['nomor_hp'] ?? null);

            $sudah[] = [
                'id' => $userId,
                'is_registered' => true,
                'name' => $user?->name ?? 'User #'.$userId,
                'email' => $user?->email ?? '-',
                'nomor_hp' => $phone,
                'unit_kerja' => $user?->unit_kerja ?: ($user?->metadata['unit_kerja'] ?? '-'),
                'jabatan' => $user?->jabatan ?: ($user?->metadata['jabatan'] ?? '-'),
                'desa' => $user?->desa ?: ($user?->metadata['desa'] ?? null),
                'kecamatan' => $user?->kecamatan ?: ($user?->metadata['kecamatan'] ?? null),
                'attempts' => $items->count(),
                'latest_submitted_at' => $latest?->submitted_at?->format('d M Y H:i') ?? '-',
                'raw_submitted_at' => $latest?->submitted_at,
                'best_score' => $bestScore !== null ? number_format((float) $bestScore, 1) : null,
                'passed' => $record->is_quiz ? ($bestScore >= $passingScore) : null,
            ];
        }

        // 2) Guest / Anonymous submissions (user_id is null)
        $guestSubmissions = $submissions->whereNull('user_id');
        foreach ($guestSubmissions as $guest) {
            $payload = $guest->payload ?? [];
            $name = $payload['nama_lengkap'] ?? $payload['nama'] ?? ($payload['nama_peserta'] ?? 'Responden Anonim #'.$guest->id);
            $email = $payload['email_peserta'] ?? $payload['email'] ?? '-';
            $phone = $payload['nomor_hp'] ?? ($payload['telepon'] ?? ($payload['no_hp'] ?? null));

            $sudah[] = [
                'id' => 'guest_'.$guest->id,
                'is_registered' => false,
                'name' => $name,
                'email' => $email,
                'nomor_hp' => $phone,
                'unit_kerja' => $payload['unit_kerja'] ?? ($payload['kantor'] ?? '-'),
                'jabatan' => $payload['jabatan'] ?? '-',
                'desa' => $payload['desa'] ?? null,
                'kecamatan' => $payload['kecamatan'] ?? null,
                'attempts' => 1,
                'latest_submitted_at' => $guest->submitted_at?->format('d M Y H:i') ?? '-',
                'raw_submitted_at' => $guest->submitted_at,
                'best_score' => $guest->score !== null ? number_format((float) $guest->score, 1) : null,
                'passed' => $record->is_quiz ? ($guest->score >= $passingScore) : null,
            ];
        }

        $belum = [];
        $publicUrl = $record->getPublicUrl();
        $tipe = $record->is_quiz ? 'kuis' : 'survei';

        if ($targetUsers->isNotEmpty()) {
            $targetUsers->reject(fn ($u) => in_array($u->id, $submittedUserIds))->values()->each(function ($u) use (&$belum, $record, $publicUrl, $tipe) {
                $phone = $u->nomor_hp ?: ($u->metadata['nomor_hp'] ?? null);
                $cleanPhone = null;
                $waLink = null;

                if ($phone) {
                    $digits = preg_replace('/\D+/', '', (string) $phone);
                    if (str_starts_with($digits, '0')) {
                        $digits = '62'.substr($digits, 1);
                    } elseif (str_starts_with($digits, '8')) {
                        $digits = '62'.$digits;
                    }
                    if (strlen($digits) >= 9 && strlen($digits) <= 15) {
                        $cleanPhone = $digits;
                        $msg = "Halo Bapak/Ibu {$u->name},\n\nMengingatkan untuk dapat segera mengisi {$tipe}:\n*\"{$record->title}\"*\n\nTautan: {$publicUrl}\n\nTerima kasih atas kerjasamanya.";
                        $waLink = 'https://wa.me/'.$cleanPhone.'?text='.rawurlencode($msg);
                    }
                }

                $belum[] = [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'nomor_hp' => $phone,
                    'clean_phone' => $cleanPhone,
                    'wa_link' => $waLink,
                    'unit_kerja' => $u->unit_kerja ?: ($u->metadata['unit_kerja'] ?? '-'),
                    'jabatan' => $u->jabatan ?: ($u->metadata['jabatan'] ?? '-'),
                    'desa' => $u->desa ?: ($u->metadata['desa'] ?? null),
                    'kecamatan' => $u->kecamatan ?: ($u->metadata['kecamatan'] ?? null),
                    'identity_type' => $u->identity_type ?? 'User',
                ];
            });
        }

        $totalTargetCount = $targetUsers->count();
        $totalSudahCount = count($sudah);
        $totalBelumCount = count($belum);

        $belumEmails = collect($belum)
            ->pluck('email')
            ->filter(fn ($e) => ! empty($e) && filter_var($e, FILTER_VALIDATE_EMAIL))
            ->values()
            ->implode(', ');

        $belumPhones = collect($belum)
            ->pluck('nomor_hp')
            ->filter()
            ->values()
            ->implode(', ');

        $reminderText = '*[PENGINGAT PENGISIAN '.strtoupper($tipe)."]*\n\n"
            .'Yth. Rekan-rekan yang belum mengisi '.$tipe.":\n"
            .'📋 *"'.$record->title."\"*\n"
            .'🔗 Link: '.$publicUrl."\n\n"
            .'Daftar peserta yang belum menyelesaikan pengisian ('.$totalBelumCount." orang):\n";

        foreach ($belum as $idx => $item) {
            $num = $idx + 1;
            $unit = ($item['unit_kerja'] !== '-') ? ' - '.$item['unit_kerja'] : '';
            $reminderText .= "{$num}. {$item['name']}{$unit}\n";
        }

        $reminderText .= "\nMohon kerjasamanya untuk dapat segera mengisi dan menyelesaikan. Terima kasih banyak! 🙏";

        $percentage = $totalTargetCount > 0 ? min(100, round((($totalTargetCount - $totalBelumCount) / $totalTargetCount) * 100)) : null;

        return [
            'has_target' => $targetUsers->isNotEmpty(),
            'target_scope' => $targetScope,
            'total_target' => $totalTargetCount,
            'total_target_submitted' => $targetUsers->isNotEmpty() ? ($totalTargetCount - $totalBelumCount) : $totalSudahCount,
            'total_sudah' => $totalSudahCount,
            'total_belum' => $totalBelumCount,
            'percentage' => $percentage,
            'sudah' => $sudah,
            'belum' => $belum,
            'all_belum_emails' => $belumEmails,
            'all_belum_phones' => $belumPhones,
            'reminder_text' => $reminderText,
            'public_url' => $publicUrl,
            'is_quiz' => (bool) $record->is_quiz,
            'passing_score' => $passingScore,
        ];
    }
}
