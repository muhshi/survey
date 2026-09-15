<?php

namespace Database\Seeders;

use App\Enums\SurveyMode;
use App\Models\Group;
use App\Models\Kategori;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WilkerstatQuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat / Ambil Kategori
        $kategori = Kategori::firstOrCreate(
            ['slug' => 'pelatihan-se2026'],
            [
                'name' => 'Pelatihan SE2026',
                'description' => 'Kumpulan evaluasi, pre-test, dan post-test pelatihan Sensus Ekonomi 2026.',
            ]
        );

        // 2. Daftar 17 Petugas Pengolahan
        $petugasList = [
            ['name' => 'Abdul Rosid', 'wilayah' => 'Wedung', 'email' => 'abdul.rosid@bpsdemak.id'],
            ['name' => 'Adek Maya', 'wilayah' => 'Demak', 'email' => 'adek.maya@bpsdemak.id'],
            ['name' => 'Mariska Dewantari', 'wilayah' => 'Demak', 'email' => 'mariska.dewantari@bpsdemak.id'],
            ['name' => 'Bagas Okfi', 'wilayah' => 'Demak', 'email' => 'bagas.okfi@bpsdemak.id'],
            ['name' => 'Lukman', 'wilayah' => 'Demak', 'email' => 'lukman@bpsdemak.id'],
            ['name' => 'Rikha Puspita', 'wilayah' => 'Sayung', 'email' => 'rikha.puspita@bpsdemak.id'],
            ['name' => 'Erna', 'wilayah' => 'Gajah', 'email' => 'erna@bpsdemak.id'],
            ['name' => 'Nur Amatullah', 'wilayah' => 'Mijen', 'email' => 'nur.amatullah@bpsdemak.id'],
            ['name' => 'Ahmad Zuhri', 'wilayah' => 'Guntur', 'email' => 'ahmad.zuhri@bpsdemak.id'],
            ['name' => 'Nurul Huda', 'wilayah' => 'Karangtengah', 'email' => 'nurul.huda@bpsdemak.id'],
            ['name' => 'Hanun Alya', 'wilayah' => 'Karangawen', 'email' => 'hanun.alya@bpsdemak.id'],
            ['name' => 'Al Hikmah', 'wilayah' => 'Kebonagung', 'email' => 'al.hikmah@bpsdemak.id'],
            ['name' => 'Dzikrullah', 'wilayah' => 'Bonang', 'email' => 'dzikrullah@bpsdemak.id'],
            ['name' => 'Ita Rahmahwati', 'wilayah' => 'Bonang', 'email' => 'ita.rahmahwati@bpsdemak.id'],
            ['name' => 'Roikhatul Miskiyah', 'wilayah' => 'Demak', 'email' => 'roikhatul.miskiyah@bpsdemak.id'],
            ['name' => 'Indah Megantara', 'wilayah' => 'Demak', 'email' => 'indah.megantara@bpsdemak.id'],
            ['name' => 'Ahmad Dany Naufal Al Faruq', 'wilayah' => 'Demak', 'email' => 'ahmad.dany@bpsdemak.id'],
        ];

        // 3. Buat / Ambil Group Petugas Pengolahan
        $group = Group::firstOrCreate(
            ['name' => 'Petugas Pengolahan Wilkerstat SE2026']
        );

        $userIds = [];
        foreach ($petugasList as $p) {
            $user = User::where('name', 'like', "%{$p['name']}%")
                ->orWhere('email', $p['email'])
                ->first();

            if (! $user) {
                $user = User::create([
                    'name' => $p['name'],
                    'email' => $p['email'],
                    'password' => Hash::make('Bps3321!'),
                    'email_verified_at' => now(),
                ]);
            }

            $userIds[] = $user->id;
        }

        $group->users()->syncWithoutDetaching($userIds);

        // 4. Baca Skema JSON Pre-test & Post-test
        $pretestPath = base_path('pretest-pengolahan-wilkerstat-se2026.json');
        $posttestPath = base_path('posttest-pengolahan-wilkerstat-se2026.json');

        $pretestSchema = file_exists($pretestPath) ? json_decode(file_get_contents($pretestPath), true) : null;
        $posttestSchema = file_exists($posttestPath) ? json_decode(file_get_contents($posttestPath), true) : null;

        // 5. Buat / Update Survey Pre-Test
        if ($pretestSchema) {
            $pretest = Survey::updateOrCreate(
                ['slug' => 'pretest-pengolahan-wilkerstat-se2026'],
                [
                    'kategori_id' => $kategori->id,
                    'title' => 'Pre-Test Pelatihan Pengolahan Pemutakhiran Kerangka Geospasial dan Muatan Wilkerstat SE2026',
                    'description' => 'Pre-test untuk mengukur pemahaman awal peserta mengenai konsep, aturan topologi spasial, dan tahapan pengolahan Wilkerstat SE2026.',
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
        }

        // 6. Buat / Update Survey Post-Test
        if ($posttestSchema) {
            $posttest = Survey::updateOrCreate(
                ['slug' => 'posttest-pengolahan-wilkerstat-se2026'],
                [
                    'kategori_id' => $kategori->id,
                    'title' => 'Post-Test Pelatihan Pengolahan Pemutakhiran Kerangka Geospasial dan Muatan Wilkerstat SE2026',
                    'description' => 'Post-test untuk mengevaluasi penguasaan materi setelah mengikuti seluruh rangkaian pelatihan pengolahan Wilkerstat SE2026.',
                    'schema' => $posttestSchema,
                    'mode' => SurveyMode::Single,
                    'is_quiz' => true,
                    'is_active' => true,
                    'access_level' => 'public',
                    'settings' => [
                        'passing_score' => 70,
                        'allow_retake' => true,
                        'max_retakes' => 2,
                    ],
                ]
            );
            $posttest->groups()->syncWithoutDetaching([$group->id]);
        }
    }
}
