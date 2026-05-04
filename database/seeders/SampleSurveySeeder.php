<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Survey;
use Illuminate\Database\Seeder;

class SampleSurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Kategori Demo
        $kategori = Kategori::firstOrCreate(
            ['slug' => 'survei-internal-bps'],
            [
                'name' => 'Survei Internal BPS',
                'description' => 'Kumpulan kuesioner untuk kebutuhan internal pegawai dan manajemen.',
            ]
        );

        // 2. Skema JSON lengkap dengan Validasi dan Logika Kondisional
        $schema = [
            'title' => 'Kuesioner Profil Responden',
            'description' => 'Kuesioner ini mencontohkan dua metode perancangan, validasi kustom otomatis, dan logika percabangan.',
            'logoPosition' => 'right',
            'pages' => [
                [
                    'name' => 'identitas_diri',
                    'title' => 'Identitas Diri',
                    'elements' => [
                        [
                            'type' => 'text',
                            'name' => 'nama',
                            'title' => 'Nama Lengkap',
                            'isRequired' => true,
                            'minLength' => 2,
                            'placeholder' => 'Akan ditolak sistem jika kurang dari 2 karakter',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'email',
                            'title' => 'Alamat Email Institusi',
                            'isRequired' => true,
                            'validators' => [
                                [
                                    'type' => 'email',
                                    'text' => 'Format email yang dimasukkan tidak tepat.',
                                ],
                            ],
                        ],
                        [
                            'type' => 'text',
                            'name' => 'nomor_hp',
                            'title' => 'Nomor WhatsApp',
                            'isRequired' => true,
                            'validators' => [
                                [
                                    'type' => 'regex',
                                    'regex' => '^\\d{10,13}$',
                                    'text' => 'Gagal: Harus berupa angka 10-13 digit.',
                                ],
                            ],
                            'placeholder' => 'Contoh: 081234567890',
                        ],
                    ],
                ],
                [
                    'name' => 'pendidikan_dan_karir',
                    'title' => 'Pendidikan & Rencana Karir',
                    'elements' => [
                        [
                            'type' => 'dropdown',
                            'name' => 'pendidikan_terakhir',
                            'title' => 'Pendidikan Terakhir Anda',
                            'isRequired' => true,
                            'choices' => [
                                'SD',
                                'SMP',
                                'SMA',
                                'Kuliah',
                            ],
                        ],
                        [
                            'type' => 'text',
                            'name' => 'jurusan',
                            'title' => 'Jurusan Kuliah (Tampil Berdasarkan Logika)',
                            'visibleIf' => "{pendidikan_terakhir} == 'Kuliah'",
                            'isRequired' => true,
                        ],
                        [
                            'type' => 'comment',
                            'name' => 'rencana_karir',
                            'title' => 'Rencana Karir/Riset ke Depan',
                            'description' => 'Logika Sistem: Pertanyaan ini dilompati jika dropdown di atas memilih SD.',
                            'visibleIf' => "{pendidikan_terakhir} notempty and {pendidikan_terakhir} != 'SD'",
                        ],
                    ],
                ],
            ],
            'showQuestionNumbers' => 'on',
        ];

        // 3. Buat Data Survei di Database
        Survey::updateOrCreate(
            ['slug' => 'kuesioner-profil-responden'],
            [
                'kategori_id' => $kategori->id,
                'title' => 'Kuesioner Profil Responden',
                'description' => 'Contoh implementasi modul pembuat kuesioner dengan standar JSON dan layer validasi.',
                'schema' => $schema,
                'mode' => 'single',
                'is_active' => true,
                'access_level' => 'public',
            ]
        );
    }
}
