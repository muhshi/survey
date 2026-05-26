<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;

class ImportCalonAfirmasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:import-calon-afirmasi {file? : Path file Excel (.xlsx)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import calon afirmasi dari file Excel (.xlsx)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file') ?? base_path('petugas afirmasi.xlsx');

        if (! file_exists($filePath)) {
            $this->error("File tidak ditemukan di path: {$filePath}");

            return self::FAILURE;
        }

        $this->info("Membaca file Excel: {$filePath}...");

        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
        } catch (\Exception $e) {
            $this->error('Gagal membaca file Excel: '.$e->getMessage());

            return self::FAILURE;
        }

        $headers = $worksheet->rangeToArray('A1:'.$highestColumn.'1', null, true, false)[0];
        $headerIndices = [
            'nama' => -1,
            'email' => -1,
            'kab_kec' => -1,
            'desa' => -1,
        ];

        foreach ($headers as $index => $header) {
            if ($header === null) {
                continue;
            }
            $headerLower = strtolower(trim($header));
            if ($headerLower === 'nama') {
                $headerIndices['nama'] = $index;
            } elseif ($headerLower === 'email') {
                $headerIndices['email'] = $index;
            } elseif (str_contains($headerLower, 'kab') || str_contains($headerLower, 'kec')) {
                $headerIndices['kab_kec'] = $index;
            } elseif ($headerLower === 'desa') {
                $headerIndices['desa'] = $index;
            }
        }

        // Validasi kolom wajib
        if ($headerIndices['email'] === -1) {
            $this->error("Kolom 'email' tidak ditemukan dalam header Excel.");

            return self::FAILURE;
        }

        $this->info("Menyiapkan role 'calon_afirmasi'...");
        $role = Role::firstOrCreate(['name' => 'calon_afirmasi', 'guard_name' => 'web']);

        $this->info('Mendapatkan nomor urut terakhir...');
        $lastUser = User::whereNotNull('metadata->nomor_urut')
            ->orderByRaw('CAST(json_unquote(json_extract(metadata, "$.nomor_urut")) AS UNSIGNED) DESC')
            ->first();

        $currentNumber = $lastUser ? (int) $lastUser->nomor_urut : 0;

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $this->info('Memulai proses impor...');

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowValues = $worksheet->rangeToArray('A'.$row.':'.$highestColumn.$row, null, true, false)[0];

            $email = $headerIndices['email'] !== -1 && isset($rowValues[$headerIndices['email']])
                ? trim($rowValues[$headerIndices['email']])
                : '';

            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;

                continue;
            }

            $name = $headerIndices['nama'] !== -1 && isset($rowValues[$headerIndices['nama']])
                ? trim($rowValues[$headerIndices['nama']])
                : explode('@', $email)[0];

            $kabKecRaw = $headerIndices['kab_kec'] !== -1 && isset($rowValues[$headerIndices['kab_kec']])
                ? trim($rowValues[$headerIndices['kab_kec']])
                : '';

            $desaRaw = $headerIndices['desa'] !== -1 && isset($rowValues[$headerIndices['desa']])
                ? trim($rowValues[$headerIndices['desa']])
                : '';

            // Parsing kecamatan: "(21) DEMAK - (010) MRANGGEN" -> "MRANGGEN"
            $parts = explode('-', $kabKecRaw);
            $kecamatanRaw = isset($parts[1]) ? trim($parts[1]) : trim($parts[0]);
            $kecamatan = trim(preg_replace('/^\(\d+\)\s*/', '', $kecamatanRaw));

            // Parsing desa: "(008) BATURSARI" -> "BATURSARI"
            $desa = trim(preg_replace('/^\(\d+\)\s*/', '', $desaRaw));

            // Cari user berdasarkan email
            $user = User::where('email', $email)->first();

            if (! $user) {
                $currentNumber++;
                $nomorUrut = str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
            } else {
                $nomorUrut = $user->nomor_urut;
            }

            $userData = [
                'nomor_urut' => $nomorUrut,
                'name' => $name,
                'kecamatan' => $kecamatan,
                'desa' => $desa,
                'is_active' => true,
                'identity_type' => 'mitra',
            ];

            // Set password default jika user baru
            if (! $user) {
                $userData['password'] = Hash::make('password123');
            }

            $user = User::updateOrCreate(
                ['email' => $email],
                $userData
            );

            if ($user->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }

            if (! $user->hasRole('calon_afirmasi')) {
                $user->assignRole($role);
            }
        }

        $this->info('✅ Proses Impor Selesai!');
        $this->info("- Ditambahkan: {$created}");
        $this->info("- Diperbarui: {$updated}");
        $this->info("- Dilewati: {$skipped}");

        return self::SUCCESS;
    }
}
