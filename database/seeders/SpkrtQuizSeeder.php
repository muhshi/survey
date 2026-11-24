<?php

namespace Database\Seeders;

use App\Enums\SurveyMode;
use App\Models\Group;
use App\Models\Kategori;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SpkrtQuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat / Ambil Kategori Pelatihan SPKRT 2027
        $kategori = Kategori::firstOrCreate(
            ['slug' => 'pelatihan-spkrt-2027'],
            [
                'name' => 'Pelatihan SPKRT 2027',
                'description' => 'Kumpulan evaluasi, pre-test, dan post-test pelatihan Survei Pola Konsumsi Rumah Tangga (SPKRT) 2027.',
            ]
        );

        // 2. Buat / Ambil Group Kelompok Petugas SPKRT 2027
        $group = Group::firstOrCreate(
            ['name' => 'Petugas SPKRT 2027']
        );

        // 3. Muat Data Peserta (73 Mitra + 13 Organik BPS)
        $pesertaPath = database_path('data/peserta_spkrt_2027.php');
        $pesertaList = file_exists($pesertaPath) ? require $pesertaPath : [];

        $userIds = [];
        foreach ($pesertaList as $p) {
            $user = User::where('email', $p['email'])
                ->orWhere('name', 'like', "%{$p['name']}%")
                ->first();

            if (! $user) {
                $user = User::create([
                    'name' => $p['name'],
                    'email' => $p['email'],
                    'password' => Hash::make('Bps3321!'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'metadata' => $p['metadata'] ?? [],
                ]);
            } else {
                // Update metadata jika sebelumnya belum lengkap
                $existingMeta = $user->metadata ?? [];
                $newMeta = array_merge($existingMeta, $p['metadata'] ?? []);
                $user->update(['metadata' => $newMeta]);
            }

            $userIds[] = $user->id;
        }

        if (! empty($userIds)) {
            $group->users()->syncWithoutDetaching($userIds);
        }

        // 4. Baca Skema JSON Pre-Test SPKRT 2027
        $findJson = fn (string $filename): ?string => file_exists(database_path("surveys/{$filename}"))
            ? database_path("surveys/{$filename}")
            : (file_exists(base_path($filename)) ? base_path($filename) : null);

        $pretestPath = $findJson('pretest-spkrt-2027.json');
        $pretestSchema = $pretestPath ? json_decode(file_get_contents($pretestPath), true) : null;

        // 5. Buat / Update Survey Pre-Test SPKRT 2027
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

            // Tautkan Survey ke Kelompok Petugas SPKRT 2027
            $pretest->groups()->syncWithoutDetaching([$group->id]);
        }
    }
}
