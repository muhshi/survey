<?php

namespace App\Console\Commands;

use App\Models\User;
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
    public function handle(): void
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

        $roleName = $this->option('role');
        $role = Role::firstOrCreate(['name' => $roleName]);

        $this->info('Mengimpor '.count($data).' pendaftar...');
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();

        foreach ($data as $item) {
            // Mapping field dari JSON (bisa disesuaikan)
            $email = $item['email'] ?? $item['username'] ?? null;
            $name = $item['name'] ?? $item['nama'] ?? null;
            $dob = $item['dob'] ?? $item['tanggal_lahir'] ?? null; // Password (DDMMYYYY)

            if (! $email || ! $dob) {
                $bar->advance();

                continue;
            }

            // Bersihkan format tanggal lahir jika ada pemisah (misal 1990-01-01 -> 01011990)
            // Namun idealnya user sudah menyediakan format yang benar.
            // Di sini kita asumsikan $dob adalah string password mentah.

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name ?? explode('@', $email)[0],
                    'password' => Hash::make($dob),
                    'is_active' => true,
                    'identity_type' => 'mitra',
                ]
            );

            if (! $user->hasRole($roleName)) {
                $user->assignRole($role);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Import selesai!');
    }
}
