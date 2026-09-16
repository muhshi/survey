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
            ['search' => 'Abdul Rosid', 'name' => 'Abdul Rosid', 'email' => 'abdul.rosid@bpsdemak.id'],
            ['search' => 'Adek Maya', 'name' => 'Adek Maya', 'email' => 'adek.maya@bpsdemak.id'],
            ['search' => 'Mariska', 'name' => 'Mariska Dewantari', 'email' => 'mariska.dewantari@bpsdemak.id'],
            ['search' => 'Bagas', 'name' => 'Bagas Okfi', 'email' => 'bagas.okfi@bpsdemak.id'],
            ['search' => 'Lukman', 'name' => 'Lukman', 'email' => 'lukman@bpsdemak.id'],
            ['search' => 'Rikha', 'name' => 'Rikha Puspita', 'email' => 'rikha.puspita@bpsdemak.id'],
            ['search' => 'Erna', 'name' => 'Erna', 'email' => 'erna@bpsdemak.id'],
            ['search' => 'Nur amatullah', 'name' => 'Nur Amatullah', 'email' => 'nur.amatullah@bpsdemak.id'],
            ['search' => 'Ahmad zuhri', 'name' => 'Ahmad Zuhri', 'email' => 'ahmad.zuhri@bpsdemak.id'],
            ['search' => 'Nurul Huda', 'name' => 'Nurul Huda', 'email' => 'nurul.huda@bpsdemak.id'],
            ['search' => 'Hanun Alya', 'name' => 'Hanun Alya', 'email' => 'hanun.alya@bpsdemak.id'],
            ['search' => 'Al Hikmah', 'name' => 'Al Hikmah', 'email' => 'al.hikmah@bpsdemak.id'],
            ['search' => 'Dzikrullah', 'name' => 'Dzikrullah', 'email' => 'dzikrullah@bpsdemak.id'],
            ['search' => 'Ita Rahmahwati', 'name' => 'Ita Rahmahwati', 'email' => 'ita.rahmahwati@bpsdemak.id'],
            ['search' => 'Roikhatul', 'name' => 'Roikhatul Miskiyah', 'email' => 'roikhatul.miskiyah@bpsdemak.id'],
            ['search' => 'indah megantara', 'name' => 'Indah Megantara', 'email' => 'indah.megantara@bpsdemak.id'],
            ['search' => 'Ahmad Dany', 'name' => 'Ahmad Dany Naufal Al Faruq', 'email' => 'ahmad.dany@bpsdemak.id'],
        ];

        // 3. Buat / Ambil Group Petugas Pengolahan
        $group = Group::firstOrCreate(
            ['name' => 'Petugas Pengolahan Peta SE2026']
        );

        $userIds = [];
        foreach ($petugasList as $p) {
            $user = User::where('name', 'like', "%{$p['search']}%")
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
        $findJson = fn (string $filename): ?string => file_exists(database_path("surveys/{$filename}"))
            ? database_path("surveys/{$filename}")
            : (file_exists(base_path($filename)) ? base_path($filename) : null);

        $pretestPath = $findJson('pretest-pengolahan-wilkerstat-se2026.json');
        $posttestPath = $findJson('posttest-pengolahan-wilkerstat-se2026.json');

        $pretestSchema = $pretestPath ? json_decode(file_get_contents($pretestPath), true) : null;
        $posttestSchema = $posttestPath ? json_decode(file_get_contents($posttestPath), true) : null;

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
