<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

#[Signature('app:import-participants')]
#[Description('Import participants from participants.json into the users table')]
class ImportParticipants extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = base_path('participants.json');

        if (! file_exists($path)) {
            $this->error('File participants.json not found. Run python script first.');

            return 1;
        }

        $participants = json_decode(ltrim(file_get_contents($path), "\xEF\xBB\xBF"), true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($participants)) {
            $this->error('File participants.json tidak bisa dibaca: '.json_last_error_msg());

            return 1;
        }
        $count = count($participants);
        $this->info("Importing {$count} participants...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $chunks = array_chunk($participants, 50);

        foreach ($chunks as $chunk) {
            DB::transaction(function () use ($chunk, $bar) {
                foreach ($chunk as $p) {
                    $user = User::where('metadata->nip', $p['nip'])->first()
                        ?? User::where('email', $p['email'] ?? ($p['nip'].'@example.com'))->first();

                    $userData = [
                        'name' => $p['name'],
                        'email' => $p['email'] ?? ($p['nip'].'@example.com'),
                        'password' => Hash::make('Mitra3321'),
                        'nomor_hp' => $p['nomor_hp'],
                        'nomor_urut' => $p['nomor_urut'],
                        'kecamatan' => $p['kecamatan'],
                        'desa' => $p['desa'],
                        'identity_type' => 'mitra',
                        'is_active' => true,
                    ];

                    if ($user) {
                        $user->update($userData);
                    } else {
                        $user = User::create($userData);
                    }

                    if (! $user->hasRole('calon_petugas')) {
                        $user->assignRole('calon_petugas');
                    }
                    $bar->advance();
                }
            });
        }

        $bar->finish();
        $this->newLine();
        $this->info('Import completed successfully.');

        return 0;
    }
}
