<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Survey;
use Illuminate\Database\Seeder;

class AlokasiPetugasSurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Kategori
        $kategori = Kategori::firstOrCreate(
            ['slug' => 'se2026'],
            [
                'name' => 'SE2026',
                'description' => 'Kumpulan survei dan form untuk kegiatan SE2026.',
            ]
        );

        // 2. Skema JSON
        $schema = [
            'title' => 'Alokasi Wilayah Tugas Petugas SE2026',
            'description' => 'Silakan pilih Kecamatan, Desa, SLS, dan maksimal 2 Sub SLS wilayah tugas Anda.',
            'logoPosition' => 'right',
            'pages' => [
                [
                    'name' => 'alokasi_wilayah',
                    'title' => 'Pilih Wilayah Tugas',
                    'elements' => [
                        [
                            'type' => 'dropdown',
                            'name' => 'kecamatan',
                            'title' => 'Kecamatan',
                            'isRequired' => true,
                            'choicesByUrl' => [
                                'url' => '/api/regions/kecamatan',
                                'valueName' => 'value',
                                'titleName' => 'text',
                            ],
                            'placeholder' => 'Pilih Kecamatan...',
                        ],
                        [
                            'type' => 'dropdown',
                            'name' => 'desa',
                            'title' => 'Desa/Kelurahan',
                            'isRequired' => true,
                            'choicesByUrl' => [
                                'url' => '/api/regions/desa?kecamatan={kecamatan}',
                                'valueName' => 'value',
                                'titleName' => 'text',
                            ],
                            'placeholder' => 'Pilih Desa/Kelurahan...',
                            'visibleIf' => '{kecamatan} notempty',
                        ],
                        [
                            'type' => 'dropdown',
                            'name' => 'sls',
                            'title' => 'Satuan Lingkungan Setempat (SLS)',
                            'isRequired' => true,
                            'choicesByUrl' => [
                                'url' => '/api/regions/sls?desa={desa}',
                                'valueName' => 'value',
                                'titleName' => 'text',
                            ],
                            'placeholder' => 'Pilih SLS...',
                            'visibleIf' => '{desa} notempty',
                        ],
                        [
                            'type' => 'tagbox',
                            'name' => 'sub_sls',
                            'title' => 'Sub SLS (Maksimal 2)',
                            'isRequired' => true,
                            'maxSelectedChoices' => 2,
                            'choicesByUrl' => [
                                'url' => '/api/regions/subsls?desa={desa}&sls={sls}',
                                'valueName' => 'value',
                                'titleName' => 'text',
                            ],
                            'placeholder' => 'Pilih Sub SLS...',
                            'visibleIf' => '{sls} notempty',
                        ],
                    ],
                ],
            ],
            'showQuestionNumbers' => 'off',
            'completeText' => 'Simpan Alokasi',
        ];

        // 3. Buat Data Survei di Database
        Survey::updateOrCreate(
            ['slug' => 'alokasi-petugas-se2026'],
            [
                'kategori_id' => $kategori->id,
                'title' => 'Alokasi Petugas SE2026',
                'description' => 'Form pemilihan wilayah tugas untuk petugas survei SE2026',
                'schema' => $schema,
                'mode' => 'single',
                'is_active' => true,
                'access_level' => 'auth',
            ]
        );
    }
}
