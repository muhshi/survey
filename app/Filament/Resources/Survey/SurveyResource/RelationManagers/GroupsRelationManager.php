<?php

namespace App\Filament\Resources\Survey\SurveyResource\RelationManagers;

use App\Models\Group;
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
use Illuminate\Support\Facades\DB;
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
                    ->action(function (Group $record, array $data): void {
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

                        $record->users()->syncWithoutDetaching($userIds);

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
