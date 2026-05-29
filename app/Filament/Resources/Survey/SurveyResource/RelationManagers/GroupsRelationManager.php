<?php

namespace App\Filament\Resources\Survey\SurveyResource\RelationManagers;

use App\Models\Group;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
                Action::make('downloadTemplate')
                    ->label('Unduh Template')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->url(fn () => route('survey.groups.template-import-user'))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                Action::make('importUsers')
                    ->label('Import Users')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('file')
                            ->label('File Excel')
                            ->disk('local')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required(),
                    ])
                    ->action(function (Group $record, array $data) {
                        set_time_limit(0);

                        $filePath = Storage::disk('local')->path($data['file']);

                        $import = new class implements ToArray, WithHeadingRow
                        {
                            public $data = [];

                            public function array(array $array)
                            {
                                $this->data = $array;
                            }
                        };

                        Excel::import($import, $filePath);

                        $rows = $import->data;
                        $validRows = [];
                        $emails = [];
                        foreach ($rows as $row) {
                            $email = isset($row['email']) ? strtolower(trim($row['email'])) : null;
                            $name = $row['name'] ?? $row['nama'] ?? null;
                            if ($email) {
                                $emails[] = $email;
                                $validRows[$email] = $name ?? 'User';
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

                        $existingUsers = User::whereIn('email', $emails)->get(['id', 'email']);
                        $existingEmails = $existingUsers->pluck('email')->map(fn ($email) => strtolower($email))->toArray();

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

                            foreach (array_chunk($newUsers, 500) as $chunk) {
                                User::insertOrIgnore($chunk);
                            }

                            $existingUsers = User::whereIn('email', $emails)->get(['id']);
                        }

                        $userIds = $existingUsers->pluck('id')->toArray();
                        foreach (array_chunk($userIds, 500) as $chunk) {
                            $record->users()->syncWithoutDetaching($chunk);
                        }

                        $importedCount = count($userIds);

                        Notification::make()
                            ->title("Berhasil mengimpor $importedCount user ke kelompok {$record->name}.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
