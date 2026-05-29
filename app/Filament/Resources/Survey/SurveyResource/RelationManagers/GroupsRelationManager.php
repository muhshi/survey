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
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required(),
                    ])
                    ->action(function (Group $record, array $data) {
                        $filePath = storage_path('app/public/'.$data['file']);

                        $import = new class implements ToArray, WithHeadingRow
                        {
                            public array $data = [];

                            public function array(array $array)
                            {
                                $this->data = array_merge($this->data, $array);
                            }
                        };

                        Excel::import($import, $filePath);

                        $rows = $import->data;
                        $importedCount = 0;

                        foreach ($rows as $row) {
                            $email = $row['email'] ?? null;
                            $name = $row['name'] ?? $row['nama'] ?? null;

                            if (! $email) {
                                continue;
                            }

                            $user = User::firstOrCreate(
                                ['email' => $email],
                                [
                                    'name' => $name ?? 'User',
                                    'password' => Hash::make('Mitra3321'),
                                    'is_active' => true,
                                ]
                            );

                            $record->users()->syncWithoutDetaching([$user->id]);
                            $importedCount++;
                        }

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
