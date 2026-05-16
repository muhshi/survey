<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ImportPesertaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ImportPendaftar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:import-pendaftar {file} {--role=calon_petugas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import pendaftar from JSON file (email as username, dob as password)';

    /**
     * Execute the console command.
     */
    public function handle(ImportPesertaService $service): void
    {
        $file = $this->argument('file');
        if (! file_exists($file)) {
            $this->error("File tidak ditemukan: {$file}");
            return;
        }

        $jsonContent = file_get_contents($file);
        $data = json_decode($jsonContent, true);

        if (! $data) {
            $this->error('Format JSON tidak valid.');
            return;
        }

        $this->info('Mengimpor pendaftar...');
        $count = $service->import($data);

        $this->info("Import selesai! $count pendaftar diproses.");
    }
}
