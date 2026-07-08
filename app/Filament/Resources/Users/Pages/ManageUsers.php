<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('add_multiple_users')
                ->label('Import / Tambah User')
                ->color('info')
                ->icon('heroicon-o-users')
                ->form([
                    Select::make('role')
                        ->label('Peran (Role)')
                        ->options(Role::all()->pluck('name', 'name'))
                        ->required(),
                    Radio::make('input_method')
                        ->label('Metode Input')
                        ->options([
                            'excel' => 'Upload Excel',
                            'manual' => 'Input Manual Multiple',
                        ])
                        ->default('manual')
                        ->live(),
                    FileUpload::make('file')
                        ->label('File Excel (Kolom: name, email)')
                        ->disk('local')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->visible(fn(Get $get) => $get('input_method') === 'excel')
                        ->required(fn(Get $get) => $get('input_method') === 'excel'),
                    Repeater::make('users')
                        ->label('Daftar User')
                        ->schema([
                            TextInput::make('name')->label('Nama')->required(),
                            TextInput::make('email')->label('Email')->email()->required(),
                        ])
                        ->columns(2)
                        ->defaultItems(1)
                        ->visible(fn(Get $get) => $get('input_method') === 'manual')
                        ->required(fn(Get $get) => $get('input_method') === 'manual'),
                ])
                ->action(function (array $data) {
                    $roleName = $data['role'];
                    $emails = [];
                    $validRows = [];

                    if ($data['input_method'] === 'excel') {
                        $filePath = Storage::disk('local')->path($data['file']);
                        $import = new class implements ToArray, WithHeadingRow {
                            public $data = [];

                            public function array(array $array): void
                            {
                                $this->data = $array;
                            }
                        };
                        Excel::import($import, $filePath);

                        foreach ($import->data as $row) {
                            $email = isset($row['email']) ? strtolower(trim($row['email'])) : null;
                            if ($email) {
                                $emails[] = $email;
                                $validRows[$email] = $row['name'] ?? $row['nama'] ?? 'User';
                            }
                        }
                    } else {
                        foreach ($data['users'] as $user) {
                            $email = strtolower(trim($user['email']));
                            $emails[] = $email;
                            $validRows[$email] = $user['name'];
                        }
                    }

                    $emails = array_unique($emails);

                    if (empty($emails)) {
                        Notification::make()
                            ->title('Tidak ada data user yang valid.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $count = 0;
                    foreach ($emails as $email) {
                        $user = User::firstOrCreate(
                            ['email' => $email],
                            [
                                'name' => $validRows[$email],
                                'password' => 'Mitra3321',
                                'is_active' => true,
                                'identity_type' => 'mitra',
                            ]
                        );
                        if (!$user->hasRole($roleName)) {
                            $user->assignRole($roleName);
                        }
                        $count++;
                    }

                    Notification::make()
                        ->title('Import Berhasil')
                        ->body("$count user telah diproses dan diberi peran $roleName.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(User::count()),
            'pegawai' => Tab::make('Pegawai')
                ->badge(User::pegawai()->count())
                ->modifyQueryUsing(fn($query) => $query->pegawai()),
            'calon_mitra' => Tab::make('Calon Mitra')
                ->badge(User::whereHas('roles', fn($q) => $q->where('name', 'calon_petugas'))->count())
                ->modifyQueryUsing(fn($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'calon_petugas'))),
            'calon_afirmasi' => Tab::make('Calon Afirmasi')
                ->badge(User::whereHas('roles', fn($q) => $q->where('name', 'calon_afirmasi'))->count())
                ->modifyQueryUsing(fn($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'calon_afirmasi'))),
        ];
    }
}
