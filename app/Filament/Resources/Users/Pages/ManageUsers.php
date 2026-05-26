<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\ImportPesertaService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('import_calon_petugas')
                ->label('Import Calon Petugas')
                ->color('info')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('File JSON (seleksi.json)')
                        ->required()
                        ->disk('local')
                        ->directory('imports'),
                ])
                ->action(function (array $data, ImportPesertaService $service) {
                    $path = storage_path('app/'.$data['file']);

                    if (! file_exists($path)) {
                        Notification::make()
                            ->title('File tidak ditemukan')
                            ->danger()
                            ->send();

                        return;
                    }

                    $jsonContent = file_get_contents($path);
                    $jsonData = json_decode($jsonContent, true);

                    if (! $jsonData) {
                        Notification::make()
                            ->title('Format JSON tidak valid')
                            ->danger()
                            ->send();

                        return;
                    }

                    $count = $service->import($jsonData);

                    Notification::make()
                        ->title('Import Berhasil')
                        ->body("$count pendaftar telah diproses.")
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
                ->modifyQueryUsing(fn ($query) => $query->pegawai()),
            'calon_mitra' => Tab::make('Calon Mitra')
                ->badge(User::whereHas('roles', fn ($q) => $q->where('name', 'calon_petugas'))->count())
                ->modifyQueryUsing(fn ($query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'calon_petugas'))),
            'calon_afirmasi' => Tab::make('Calon Afirmasi')
                ->badge(User::whereHas('roles', fn ($q) => $q->where('name', 'calon_afirmasi'))->count())
                ->modifyQueryUsing(fn ($query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'calon_afirmasi'))),
        ];
    }
}
