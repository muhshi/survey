<?php

namespace App\Console\Commands;

use App\Models\Survey;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateSpkrtClasses extends Command
{
    protected $signature = 'survey:update-spkrt-classes';

    protected $description = 'Menyematkan data Kelas Pelatihan ke Skema Survey, Akun User, dan Jawaban Responden yang sudah tersubmit';

    public function handle(): int
    {
        $this->info('Memulai pembaruan data Kelas SPKRT...');

        $classes = [
            'Kelas A' => [
                'inda' => 'Muna',
                'members' => [
                    ['name' => 'Dewi Sartika', 'peran' => 'PPL', 'kec' => 'Demak'],
                    ['name' => 'Musa\'adah', 'peran' => 'PPL', 'kec' => 'Demak'],
                    ['name' => 'Rowaidah', 'peran' => 'PPL', 'kec' => 'Demak'],
                    ['name' => 'Dyah Laila Safriani', 'peran' => 'PPL', 'kec' => 'Demak'],
                    ['name' => 'Ananda Elisa Putri', 'peran' => 'PPL', 'kec' => 'Demak'],
                    ['name' => 'Ika Haryati', 'peran' => 'PML', 'kec' => 'Demak'],
                    ['name' => 'Tarno', 'peran' => 'PML', 'kec' => 'Demak'],
                    ['name' => 'Adib Sulthon Muammal', 'peran' => 'PML', 'kec' => 'Demak'],
                    ['name' => 'Armin Irmasari', 'peran' => 'PPL', 'kec' => 'Gajah'],
                    ['name' => 'Laelatul fitriya', 'peran' => 'PPL', 'kec' => 'Gajah'],
                    ['name' => 'Sri Itawati', 'peran' => 'PPL', 'kec' => 'Gajah'],
                    ['name' => 'Munawaroh', 'peran' => 'PPL', 'kec' => 'Gajah'],
                    ['name' => 'Siti Wiwin Fauzizah', 'peran' => 'PPL', 'kec' => 'Gajah'],
                    ['name' => 'Nuryanto', 'peran' => 'PML', 'kec' => 'Gajah'],
                    ['name' => 'Anjung Budi Cahyono', 'peran' => 'PML', 'kec' => 'Gajah'],
                    ['name' => 'Ihda Luthfiyana', 'peran' => 'PPL', 'kec' => 'Wonosalam'],
                    ['name' => 'Nur Anissah', 'peran' => 'PPL', 'kec' => 'Wonosalam'],
                    ['name' => 'Muhammad Maesuri', 'peran' => 'PPL', 'kec' => 'Wonosalam'],
                    ['name' => 'Elen Dining Riani', 'peran' => 'PPL', 'kec' => 'Wonosalam'],
                    ['name' => 'Tri Suci Ariyani', 'peran' => 'PML', 'kec' => 'Wonosalam'],
                    ['name' => 'Wiwi Wilujeng Kusumawati', 'peran' => 'PML', 'kec' => 'Wonosalam'],
                ],
            ],
            'Kelas B' => [
                'inda' => 'Musyafa\'ah',
                'members' => [
                    ['name' => 'Zakiyyatul Munifah', 'peran' => 'PPL', 'kec' => 'Karangtengah'],
                    ['name' => 'Hanik fatkhiyah', 'peran' => 'PPL', 'kec' => 'Karangtengah'],
                    ['name' => 'Fitriana marinawati', 'peran' => 'PPL', 'kec' => 'Karangtengah'],
                    ['name' => 'Ana Fao azizah', 'peran' => 'PML', 'kec' => 'Karangtengah'],
                    ['name' => 'Dyah Makutaning Dewi', 'peran' => 'PML', 'kec' => 'Karangtengah'],
                    ['name' => 'Kuntum Retno Murti', 'peran' => 'PPL', 'kec' => 'Guntur'],
                    ['name' => 'Muhammad Abdul Manaf', 'peran' => 'PPL', 'kec' => 'Guntur'],
                    ['name' => 'Syaidati Salsabila', 'peran' => 'PPL', 'kec' => 'Guntur'],
                    ['name' => 'Muhammad Guntur Ilham', 'peran' => 'PML', 'kec' => 'Guntur'],
                    ['name' => 'KHOIRIYAH', 'peran' => 'PPL', 'kec' => 'Sayung'],
                    ['name' => 'Nur Faizin', 'peran' => 'PPL', 'kec' => 'Sayung'],
                    ['name' => 'Sri Hartatik', 'peran' => 'PPL', 'kec' => 'Sayung'],
                    ['name' => 'Hamdan', 'peran' => 'PML', 'kec' => 'Sayung'],
                    ['name' => 'Edy Budi Utomo', 'peran' => 'PML', 'kec' => 'Sayung'],
                    ['name' => 'Dwi Purmoni Suciati', 'peran' => 'PPL', 'kec' => 'Mijen'],
                    ['name' => 'Serli Indriyani', 'peran' => 'PPL', 'kec' => 'Mijen'],
                    ['name' => 'Aris Sutikno', 'peran' => 'PML', 'kec' => 'Mijen'],
                    ['name' => 'Faiz Abdul Rokhman', 'peran' => 'PPL', 'kec' => 'Karanganyar'],
                    ['name' => 'Lilik Ermawati Ratna', 'peran' => 'PPL', 'kec' => 'Karanganyar'],
                    ['name' => 'Putriana', 'peran' => 'PPL', 'kec' => 'Karanganyar'],
                    ['name' => 'Himatun Nafiah', 'peran' => 'PPL', 'kec' => 'Karanganyar'],
                    ['name' => 'Yuliani', 'peran' => 'PML', 'kec' => 'Karanganyar'],
                ],
            ],
            'Kelas C' => [
                'inda' => 'Yudia Pratidina Hasibuan',
                'members' => [
                    ['name' => 'Ayu Kartika Sari', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'Indah Gita Cahyani', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'Muhammad Subkan', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'Shinta Liestyaningrum', 'peran' => 'PML', 'kec' => 'Mranggen'],
                    ['name' => 'Ayu Lestari', 'peran' => 'PPL', 'kec' => 'Karangawen'],
                    ['name' => 'Khulaidah', 'peran' => 'PPL', 'kec' => 'Karangawen'],
                    ['name' => 'Rizqi Kurnia Dewi', 'peran' => 'PPL', 'kec' => 'Karangawen'],
                    ['name' => 'Sheva Devina Artarani', 'peran' => 'PPL', 'kec' => 'Karangawen'],
                    ['name' => 'Sri Winarti', 'peran' => 'PPL', 'kec' => 'Karangawen'],
                    ['name' => 'Arif Rohman', 'peran' => 'PML', 'kec' => 'Karangawen'],
                    ['name' => 'Ahmad Kharis', 'peran' => 'PML', 'kec' => 'Karangawen'],
                    ['name' => 'Budhi Setyono', 'peran' => 'PML', 'kec' => 'Karangawen'],
                    ['name' => 'Hadi Nurrohim', 'peran' => 'PPL', 'kec' => 'Dempet'],
                    ['name' => 'Sepia Hartiningsih', 'peran' => 'PPL', 'kec' => 'Dempet'],
                    ['name' => 'siti zumaroh', 'peran' => 'PPL', 'kec' => 'Dempet'],
                    ['name' => 'Muthia Nurul Chusna', 'peran' => 'PML', 'kec' => 'Dempet'],
                    ['name' => 'Supriharto', 'peran' => 'PML', 'kec' => 'Dempet'],
                    ['name' => 'Syamsu Dhuka', 'peran' => 'PPL', 'kec' => 'Kebonagung'],
                    ['name' => 'Dina Ria Sari', 'peran' => 'PPL', 'kec' => 'Kebonagung'],
                    ['name' => 'Nur Haryadi', 'peran' => 'PPL', 'kec' => 'Kebonagung'],
                    ['name' => 'Abdul Khalim', 'peran' => 'PML', 'kec' => 'Kebonagung'],
                ],
            ],
            'Kelas D' => [
                'inda' => 'Nur Kurniawati',
                'members' => [
                    ['name' => 'Naurah Nadlifah', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'NAILUL MUNA', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'Lu\'luil Maknun', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'Indah Khomsati Lestari', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'Apriyani kurnia', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'Fatihul maslahah', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'Riska Ristiyanti', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'nurul kafidhoh', 'peran' => 'PPL', 'kec' => 'Mranggen'],
                    ['name' => 'ACHMAD HAMDANI', 'peran' => 'PML', 'kec' => 'Mranggen'],
                    ['name' => 'Anyta kus indria sussanty', 'peran' => 'PML', 'kec' => 'Mranggen'],
                    ['name' => 'Wasiatun Nikmah', 'peran' => 'PML', 'kec' => 'Mranggen'],
                    ['name' => 'Ma\'ruf Susilo Yuwono', 'peran' => 'PML', 'kec' => 'Mranggen'],
                    ['name' => 'Abdul Ghofur', 'peran' => 'PML', 'kec' => 'Bonang'],
                    ['name' => 'Nur Laili wakhidah', 'peran' => 'PPL', 'kec' => 'Bonang'],
                    ['name' => 'Ali Ridho', 'peran' => 'PPL', 'kec' => 'Bonang'],
                    ['name' => 'Har Sulistiyo', 'peran' => 'PPL', 'kec' => 'Bonang'],
                    ['name' => 'Lavinia Philliandita', 'peran' => 'PML', 'kec' => 'Bonang'],
                    ['name' => 'Ita fatmawati', 'peran' => 'PPL', 'kec' => 'Wedung'],
                    ['name' => 'Qoniatu Saadah', 'peran' => 'PPL', 'kec' => 'Wedung'],
                    ['name' => 'Syahadatul Inayah', 'peran' => 'PPL', 'kec' => 'Wedung'],
                    ['name' => 'Siswo', 'peran' => 'PML', 'kec' => 'Wedung'],
                ],
            ],
        ];

        $normalize = function ($name) {
            $name = strtolower($name);
            $name = preg_replace('/,.*$/', '', $name);
            $name = preg_replace('/[^a-z0-9\s]/', '', $name);

            return trim(preg_replace('/\s+/', ' ', $name));
        };

        $lookup = [];
        foreach ($classes as $kName => $kData) {
            foreach ($kData['members'] as $m) {
                $lookup[$normalize($m['name'])] = [
                    'kelas' => $kName,
                    'inda' => $kData['inda'],
                    'peran' => $m['peran'],
                    'kec' => $m['kec'],
                ];
            }
        }

        $findClassInfo = function ($name) use ($normalize, $lookup) {
            $norm = $normalize($name);
            if (isset($lookup[$norm])) {
                return $lookup[$norm];
            }

            foreach ($lookup as $key => $info) {
                if (str_starts_with($norm, $key) || str_starts_with($key, $norm)) {
                    return $info;
                }
            }

            return null;
        };

        // 1. Update metadata pada tabel Users
        $this->info('1. Memperbarui metadata Kelas pada seluruh Akun User...');
        $updatedUsers = 0;
        foreach (User::all() as $user) {
            $info = $findClassInfo($user->name);
            if ($info) {
                $meta = $user->metadata ?? [];
                $meta['kelas'] = $info['kelas'];
                $meta['inda'] = $info['inda'];
                $meta['bertugas_sebagai'] = $info['peran'];
                $user->update(['metadata' => $meta]);
                $updatedUsers++;
            }
        }
        $this->line("   -> {$updatedUsers} akun user berhasil diperbarui dengan data Kelas & Inda.");

        // 2. Perbarui Skema Survey Pretest SPKRT 2027 di Database
        $this->info('2. Menyematkan elemen dropdown Kelas pada Skema Survey di Database...');
        $surveys = Survey::where('slug', 'like', '%spkrt%')
            ->orWhere('title', 'like', '%SPKRT%')
            ->get();

        $schemaPath = database_path('surveys/pretest-spkrt-2027.json');
        $freshSchema = file_exists($schemaPath) ? json_decode(file_get_contents($schemaPath), true) : null;

        foreach ($surveys as $survey) {
            if ($freshSchema) {
                $survey->update(['schema' => $freshSchema]);
                $this->line("   -> Skema survei '{$survey->title}' (ID: {$survey->id}) berhasil diperbarui.");
            }
        }

        // 3. Perbarui Jawaban Responden yang SUDAH Masuk (Existing Submissions)
        $this->info('3. Menyematkan data Kelas ke jawaban responden yang sudah tersimpan...');
        $totalUpdatedSubmissions = 0;
        foreach ($surveys as $survey) {
            foreach ($survey->jawabanRespondens as $jawaban) {
                $payload = $jawaban->payload ?? [];
                $rawName = $payload['nama_lengkap'] ?? $payload['nama'] ?? ($jawaban->user?->name ?? '');

                $info = $findClassInfo($rawName);
                if ($info) {
                    $payload['kelas'] = $info['kelas'];
                    $payload['inda'] = $info['inda'];
                    $payload['bertugas_sebagai'] = $info['peran'];

                    $jawaban->update(['payload' => $payload]);
                    $totalUpdatedSubmissions++;
                    $this->line("   [OK] {$rawName} -> {$info['kelas']} (Inda: {$info['inda']})");
                } else {
                    $this->warn("   [?] Tidak cocok: {$rawName}");
                }
            }
        }

        $this->info("Pembaruan tuntas! {$totalUpdatedSubmissions} jawaban responden yang sudah mengerjakan kini telah memiliki data Kelas.");

        return self::SUCCESS;
    }
}
