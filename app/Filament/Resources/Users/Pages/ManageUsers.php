<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            \Filament\Actions\Action::make('import_calon_petugas')
                ->label('Import Calon Petugas')
                ->color('info')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('File JSON (seleksi.json)')
                        ->required()
                        ->disk('local')
                        ->directory('imports'),
                ])
                ->action(function (array $data, \App\Services\ImportPesertaService $service) {
                    $path = storage_path('app/' . $data['file']);
                    
                    if (!file_exists($path)) {
                        \Filament\Notifications\Notification::make()
                            ->title('File tidak ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }

                    $jsonContent = file_get_contents($path);
                    $jsonData = json_decode($jsonContent, true);

                    if (!$jsonData) {
                        \Filament\Notifications\Notification::make()
                            ->title('Format JSON tidak valid')
                            ->danger()
                            ->send();
                        return;
                    }

                    $count = $service->import($jsonData);

                    \Filament\Notifications\Notification::make()
                        ->title("Import Berhasil")
                        ->body("$count pendaftar telah diproses.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
