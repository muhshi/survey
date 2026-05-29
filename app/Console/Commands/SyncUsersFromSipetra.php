<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncUsersFromSipetra extends Command
{
    protected $signature = 'sync:users {--full : Abaikan timestamp, sync semua data}';

    protected $description = 'Sinkronisasi master data pengguna dari Sipetra';

    public function handle(): int
    {
        $baseUrl = config('services.sipetra.base_url');
        $token = config('services.sipetra.api_token');
        $lastSync = $this->option('full') ? null : Cache::get('sipetra_last_synced_at');

        $this->info($lastSync ? "Incremental sync sejak: {$lastSync}" : 'Full sync...');

        $page = 1;
        $created = $updated = 0;

        do {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$baseUrl}/api/master/users", array_filter([
                    'page' => $page,
                    'per_page' => 500,
                    'updated_after' => $lastSync,
                ]));

            if ($response->failed()) {
                $this->error("Gagal: HTTP {$response->status()}");
                Log::error('sync:users failed', ['status' => $response->status(), 'body' => $response->body()]);

                return self::FAILURE;
            }

            $payload = $response->json();
            $lastPage = $payload['meta']['last_page'] ?? 1;
            $syncedAt = $payload['synced_at'] ?? now()->toIso8601String();

            foreach ($payload['data'] as $data) {
                $result = User::updateOrCreate(
                    ['sipetra_id' => $data['sipetra_id']],
                    [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'avatar_url' => $data['avatar_url'],
                        'identity_type' => $data['identity_type'],
                        'nip' => $data['nip'],
                        'nip_baru' => $data['nip_baru'],
                        'jabatan' => $data['jabatan'],
                        'golongan' => $data['golongan'],
                        'unit_kerja' => $data['unit_kerja'],
                        'kd_satker' => $data['kd_satker'],
                        'nomor_hp' => $data['nomor_hp'],
                        'jenis_kelamin' => $data['gender'],
                        'is_active' => $data['is_active'],
                        'period' => $data['period'],
                        'contract_start' => $data['contract_start'],
                        'contract_end' => $data['contract_end'],
                    ]
                );

                $result->wasRecentlyCreated ? $created : $updated;
            }

            $this->line("  Halaman {$page}/{$lastPage} selesai.");

        } while ($page <= $lastPage);

        Cache::put('sipetra_last_synced_at', $syncedAt, now()->addDays(30));

        $this->info("✅ Selesai. Dibuat: {$created}, Diupdate: {$updated}.");

        return self::SUCCESS;
    }
}
