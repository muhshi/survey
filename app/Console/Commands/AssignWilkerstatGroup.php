<?php

namespace App\Console\Commands;

use App\Enums\SurveyMode;
use App\Models\Group;
use App\Models\Kategori;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Console\Command;

class AssignWilkerstatGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:assign-wilkerstat-group {--group=Petugas Pengolahan Wilkerstat SE2026 : Nama Kelompok Survei}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mendaftarkan petugas pengolahan ke Kelompok Survei dan menautkannya ke Pre-Test serta Post-Test Wilkerstat SE2026';

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

        // Daftar 17 Petugas Pengolahan yang akan dicari di database
        $candidates = [
            ['search' => 'Abdul Rosid', 'label' => 'Abdul Rosid Wedung'],
            ['search' => 'Adek Maya', 'label' => 'Adek Maya'],
            ['search' => 'Mariska', 'label' => 'Mariska dewantari'],
            ['search' => 'Bagas', 'label' => 'Bagas okfi'],
            ['search' => 'Lukman', 'label' => 'Lukman'],
            ['search' => 'Rikha Puspita', 'label' => 'Rikha Puspita sayung'],
            ['search' => 'Erna', 'label' => 'Erna gajah'],
            ['search' => 'Nur amatullah', 'label' => 'Nur amatullah Mijen'],
            ['search' => 'Ahmad zuhri', 'label' => 'Ahmad zuhri guntur'],
            ['search' => 'Nurul Huda', 'label' => 'Nurul Huda karg tengah'],
            ['search' => 'Hanun Alya', 'label' => 'Hanun Alya Karangawen'],
            ['search' => 'Al Hikmah', 'label' => 'Al Hikmah Kebonagung'],
            ['search' => 'Dzikrullah', 'label' => 'Dzikrullah Bonang'],
            ['search' => 'Ita Rahmahwati', 'label' => 'Ita Rahmahwati Bonang'],
            ['search' => 'Roikhatul', 'label' => 'Roikhatul Miskiyah'],
            ['search' => 'indah megantara', 'label' => 'indah megantara'],
            ['search' => 'Ahmad Dany', 'label' => 'Ahmad Dany Naufal Al Faruq'],
        ];

        $matchedUsers = [];
        $unmatched = [];
        $userIdsToAttach = [];

        foreach ($candidates as $cand) {
            $user = User::where('name', 'like', "%{$cand['search']}%")->first();

            if ($user) {
                $userIdsToAttach[] = $user->id;
                $matchedUsers[] = [
                    'Petugas' => $cand['label'],
                    'Nama DB' => $user->name,
                    'Email' => $user->email,
                    'Status' => 'Ditemukan',
                ];
            } else {
                $unmatched[] = [
                    'Petugas' => $cand['label'],
                    'Nama DB' => '-',
                    'Email' => '-',
                    'Status' => 'Belum ada di DB',
                ];
            }
        }

        if (! empty($userIdsToAttach)) {
            $group->users()->syncWithoutDetaching($userIdsToAttach);
            $this->info('Berhasil memasukkan '.count($userIdsToAttach)." user ke Kelompok '{$group->name}'.");
        }

        $this->table(
            ['Target Petugas', 'Nama di Database', 'Email', 'Status'],
            array_merge($matchedUsers, $unmatched)
        );

        // 3. Tautkan atau Buat Kuis Pre-Test & Post-Test
        $this->info('Memeriksa survei Pre-Test dan Post-Test...');

        $kategori = Kategori::firstOrCreate(
            ['slug' => 'pelatihan-se2026'],
            [
                'name' => 'Pelatihan SE2026',
                'description' => 'Kuesioner evaluasi dan ujian pelatihan Sensus Ekonomi 2026',
            ]
        );

        $pretestPath = base_path('pretest-pengolahan-wilkerstat-se2026.json');
        $posttestPath = base_path('posttest-pengolahan-wilkerstat-se2026.json');

        $pretestSchema = file_exists($pretestPath) ? json_decode(file_get_contents($pretestPath), true) : null;
        $posttestSchema = file_exists($posttestPath) ? json_decode(file_get_contents($posttestPath), true) : null;

        if ($pretestSchema) {
            $pretest = Survey::updateOrCreate(
                ['slug' => 'pretest-pengolahan-wilkerstat-se2026'],
                [
                    'kategori_id' => $kategori->id,
                    'title' => 'Pre-Test Pelatihan Pengolahan Pemutakhiran Kerangka Geospasial dan Muatan Wilkerstat SE2026',
                    'description' => 'Pre-test untuk mengukur pemahaman awal peserta mengenai konsep, aturan topologi spasial, dan alur pengolahan Wilkerstat SE2026.',
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
            $this->line("✅ Pre-Test ditautkan ke Kelompok '{$group->name}': ".route('survey.show', $pretest));
        }

        if ($posttestSchema) {
            $posttest = Survey::updateOrCreate(
                ['slug' => 'posttest-pengolahan-wilkerstat-se2026'],
                [
                    'kategori_id' => $kategori->id,
                    'title' => 'Post-Test Pelatihan Pengolahan Pemutakhiran Kerangka Geospasial dan Muatan Wilkerstat SE2026',
                    'description' => 'Post-test untuk mengevaluasi capaian pemahaman peserta setelah mengikuti seluruh sesi pelatihan pengolahan Wilkerstat SE2026.',
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
            $this->line("✅ Post-Test ditautkan ke Kelompok '{$group->name}': ".route('survey.show', $posttest));
        }

        $this->info('Proses selesai dengan sukses.');

        return self::SUCCESS;
    }
}
