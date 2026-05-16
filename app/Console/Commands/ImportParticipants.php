<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        $participants = json_decode(file_get_contents($path), true);
        $count = count($participants);
        $this->info("Importing {$count} participants...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        DB::transaction(function () use ($participants, $bar) {
            foreach ($participants as $p) {
                $user = User::updateOrCreate(
                    ['nip' => $p['nip']],
                    [
                        'name' => $p['name'],
                        'email' => $p['email'] ?? ($p['nip'].'@example.com'),
                        'nomor_hp' => $p['nomor_hp'],
                        'nomor_urut' => $p['nomor_urut'],
                        'kecamatan' => $p['kecamatan'],
                        'desa' => $p['desa'],
                        'identity_type' => 'mitra',
                        'is_active' => true,
                    ]
                );

                if (! $user->hasRole('calon_petugas')) {
                    $user->assignRole('calon_petugas');
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Import completed successfully.');

        return 0;
    }
}
