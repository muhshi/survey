<?php

namespace App\Filament\Resources\Groups\RelationManagers;

use App\Models\Group;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Anggota';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Nama'),
                TextInput::make('email')
                    ->email()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['jawaban_responden']))
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pretest_status')
                    ->label('Pretest')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $submission = $record->jawaban_responden
                            ->firstWhere('survey_id', 4);
                        if ($submission) {
                            return 'Selesai'.($submission->score !== null ? ' ('.$submission->score.'%)' : '');
                        }

                        return 'Belum';
                    })
                    ->color(fn ($state) => str_contains($state, 'Selesai') ? 'success' : 'danger'),
                TextColumn::make('pendalaman_status')
                    ->label('Pendalaman')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $submission = $record->jawaban_responden
                            ->firstWhere('survey_id', 5);
                        if ($submission) {
                            return 'Selesai'.($submission->score !== null ? ' ('.$submission->score.'%)' : '');
                        }

                        return 'Belum';
                    })
                    ->color(fn ($state) => str_contains($state, 'Selesai') ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('pretest_status')
                    ->label('Filter Pretest')
                    ->options([
                        'sudah' => 'Sudah Mengerjakan',
                        'belum' => 'Belum Mengerjakan',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'sudah') {
                            return $query->whereHas('jawaban_responden', fn ($q) => $q->where('survey_id', 4));
                        }
                        if ($data['value'] === 'belum') {
                            return $query->whereDoesntHave('jawaban_responden', fn ($q) => $q->where('survey_id', 4));
                        }

                        return $query;
                    }),
                SelectFilter::make('pendalaman_status')
                    ->label('Filter Pendalaman')
                    ->options([
                        'sudah' => 'Sudah Mengerjakan',
                        'belum' => 'Belum Mengerjakan',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'sudah') {
                            return $query->whereHas('jawaban_responden', fn ($q) => $q->where('survey_id', 5));
                        }
                        if ($data['value'] === 'belum') {
                            return $query->whereDoesntHave('jawaban_responden', fn ($q) => $q->where('survey_id', 5));
                        }

                        return $query;
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelect(fn (Select $select) => $select->multiple())
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->whereDoesntHave('groups', fn (Builder $q) => $q->where('groups.id', $this->getOwnerRecord()->id)
                    )
                    )
                    ->label('Tambah Anggota'),
                Action::make('importUsers')
                    ->label('Import dari Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->form([
                        FileUpload::make('file')
                            ->label('File Excel')
                            ->disk('local')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        /** @var Group $group */
                        $group = $this->getOwnerRecord();
                        $filePath = Storage::disk('local')->path($data['file']);

                        $import = new class implements ToArray, WithHeadingRow
                        {
                            public $data = [];

                            public function array(array $array): void
                            {
                                $this->data = $array;
                            }
                        };

                        Excel::import($import, $filePath);

                        $emails = [];
                        $validRows = [];
                        foreach ($import->data as $row) {
                            $email = isset($row['email']) ? strtolower(trim($row['email'])) : null;
                            if ($email) {
                                $emails[] = $email;
                                $validRows[$email] = $row['name'] ?? $row['nama'] ?? 'User';
                            }
                        }

                        $emails = array_unique($emails);

                        if (empty($emails)) {
                            Notification::make()
                                ->title('Tidak ada data user yang valid untuk diimpor.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $existingEmails = DB::table('users')
                            ->whereIn('email', $emails)
                            ->pluck('email')
                            ->map(fn ($e) => strtolower($e))
                            ->all();

                        $newEmails = array_diff($emails, $existingEmails);

                        if (! empty($newEmails)) {
                            $defaultPassword = Hash::make('Mitra3321');
                            $now = now();
                            $newUsers = [];

                            foreach ($newEmails as $email) {
                                $newUsers[] = [
                                    'email' => $email,
                                    'name' => $validRows[$email],
                                    'password' => $defaultPassword,
                                    'is_active' => true,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }

                            DB::table('users')->insertOrIgnore($newUsers);
                        }

                        $userIds = DB::table('users')
                            ->whereIn('email', $emails)
                            ->pluck('id')
                            ->all();

                        $group->users()->syncWithoutDetaching($userIds);

                        $importedCount = count($userIds);
                        $newCount = count($newEmails);

                        Notification::make()
                            ->title("Berhasil mengimpor $importedCount user ($newCount baru) ke kelompok {$group->name}.")
                            ->success()
                            ->send();
                    }),
                Action::make('downloadTemplate')
                    ->label('Unduh Template')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn () => route('survey.groups.template-import-user'))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                DetachAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Hapus yang dipilih'),
                ]),
            ]);
    }
}
