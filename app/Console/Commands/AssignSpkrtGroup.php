<?php

namespace App\Console\Commands;

use App\Enums\SurveyMode;
use App\Models\Group;
use App\Models\Kategori;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AssignSpkrtGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:assign-spkrt-group {--group=Petugas SPKRT 2027 : Nama Kelompok Survei}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mendaftarkan petugas SPKRT 2027 ke Kelompok Survei dan menautkannya ke Pre-Test SPKRT 2027';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $groupName = $this->option('group');

        $this->info("Menyiapkan Kelompok Survei: {$groupName}...");

        $group = Group::firstOrCreate(
            ['name' => $groupName],
            [
                'starts_at' => now(),
                'ends_at' => null,
            ]
        );

        // 1. Muat data 86 peserta dari file dataset
        $pesertaPath = database_path('data/peserta_spkrt_2027.php');
        if (! file_exists($pesertaPath)) {
            $this->error("File dataset peserta tidak ditemukan di: {$pesertaPath}");

            return self::FAILURE;
        }

        $pesertaList = require $pesertaPath;
        $this->info('Memproses '.count($pesertaList).' peserta (Mitra & Organik BPS)...');

        $userIdsToAttach = [];
        $summary = [];

        foreach ($pesertaList as $p) {
            $user = User::where('email', $p['email'])
                ->orWhere('name', 'like', "%{$p['name']}%")
                ->first();

            $status = 'Eksis';

            if (! $user) {
                $user = User::create([
                    'name' => $p['name'],
                    'email' => $p['email'],
                    'password' => Hash::make('Bps3321!'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'metadata' => $p['metadata'] ?? [],
                ]);
                $status = 'Dibuat Baru';
            } else {
                // Update kelengkapan metadata
                $existingMeta = $user->metadata ?? [];
                $newMeta = array_merge($existingMeta, $p['metadata'] ?? []);
                $user->update(['metadata' => $newMeta]);
            }

            $userIdsToAttach[] = $user->id;
            $summary[] = [
                'Nama' => $p['name'],
                'Email' => $p['email'],
                'Peran' => ucfirst($p['role'] ?? 'Peserta'),
                'Kecamatan' => $p['kecamatan_tugas'] ?? '-',
                'Status Akun' => $status,
            ];
        }

        if (! empty($userIdsToAttach)) {
            $group->users()->syncWithoutDetaching($userIdsToAttach);
            $this->info('Berhasil menyinkronkan '.count($userIdsToAttach)." peserta ke Kelompok '{$group->name}'.");
        }

        // Tampilkan 15 sampel peserta pertama di tabel terminal
        $this->table(
            ['Nama', 'Email', 'Peran', 'Kecamatan', 'Status Akun'],
            array_slice($summary, 0, 15)
        );
        if (count($summary) > 15) {
            $this->line('... dan '.(count($summary) - 15).' peserta lainnya.');
        }

        // 2. Buat / Ambil Kategori & Skema Kuis
        $this->info('Memeriksa survei Pre-Test SPKRT 2027...');

        $kategori = Kategori::firstOrCreate(
            ['slug' => 'pelatihan-spkrt-2027'],
            [
                'name' => 'Pelatihan SPKRT 2027',
                'description' => 'Kuesioner evaluasi dan ujian pelatihan Survei Pola Konsumsi Rumah Tangga (SPKRT) 2027',
            ]
        );

        $findJson = fn (string $filename): ?string => file_exists(database_path("surveys/{$filename}"))
            ? database_path("surveys/{$filename}")
            : (file_exists(base_path($filename)) ? base_path($filename) : null);

        $pretestPath = $findJson('pretest-spkrt-2027.json');
        $pretestSchema = $pretestPath ? json_decode(file_get_contents($pretestPath), true) : null;

        if ($pretestSchema) {
            $pretest = Survey::updateOrCreate(
                ['slug' => 'pretest-spkrt-2027'],
                [
                    'kategori_id' => $kategori->id,
                    'title' => 'Pre-Test Pelatihan Petugas Survei Pola Konsumsi Rumah Tangga (SPKRT) 2027',
                    'description' => 'Pre-test untuk mengukur pemahaman awal peserta mengenai konsep, metodologi pencacahan longitudinal, instrumen VPKRT27-BL/BLp, dan batasan operasional SPKRT 2027.',
                    'schema' => $pretestSchema,
                    'mode' => SurveyMode::Single,
                    'is_quiz' => true,
                    'is_active' => true,
                    'access_level' => 'public',
                    'settings' => [
                        'passing_score' => 70,
                        'allow_retake' => false,
                        'max_retakes' => 1,
                    ],
                ]
            );
            $pretest->groups()->syncWithoutDetaching([$group->id]);
            $this->line("✅ Pre-Test SPKRT 2027 ditautkan ke Kelompok '{$group->name}': ".route('survey.show', $pretest));
        } else {
            $this->warn('File skema database/surveys/pretest-spkrt-2027.json tidak ditemukan.');
        }

        $this->info('Proses integrasi Pre-Test SPKRT 2027 selesai.');

        return self::SUCCESS;
    }
}
