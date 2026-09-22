<?php

namespace App\Console\Commands;

use App\Models\JawabanResponden;
use App\Models\Survey;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('survey:curate-gform-se2026')]
#[Description('Kurasi dan bersihkan data responden konfirmasi SE2026 hasil migrasi GForm')]
class CurateGformSe2026 extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $survey = Survey::where('slug', 'konfirmasi-pendataan-se2026')->first();

        if (! $survey) {
            $this->error('Survei konfirmasi-pendataan-se2026 tidak ditemukan.');

            return self::FAILURE;
        }

        $records = JawabanResponden::where('survey_id', $survey->id)->get();
        $this->info("Memulai kurasi untuk {$records->count()} data responden...");

        $stats = [
            'blud_normalized' => 0,
            'nik_cleaned' => 0,
            'hp_cleaned' => 0,
            'status_pendataan_normalized' => 0,
            'text_trimmed' => 0,
            'duplicates_removed' => 0,
        ];

        // 1. Kurasi isi data tiap baris
        foreach ($records as $record) {
            $payload = $record->payload ?? [];
            $modified = false;

            // A. Penyatuan Status BLUD
            $statusPeg = trim($payload['status_kepegawaian'] ?? '');
            if ($statusPeg === 'BLUD') {
                $payload['status_kepegawaian'] = 'BLUD/Karyawan Daerah';
                $stats['blud_normalized']++;
                $modified = true;
            }

            // B. Pembersihan Teks NIK
            $rawNik = $payload['nik'] ?? '';
            $cleanNik = preg_replace('/[^0-9]/', '', (string) $rawNik);
            if ($cleanNik !== $rawNik && $cleanNik !== '') {
                $payload['nik'] = $cleanNik;
                $stats['nik_cleaned']++;
                $modified = true;
            }

            // C. Pembersihan No HP / WA
            $rawHp = $payload['no_hp'] ?? '';
            $cleanHp = preg_replace('/[^0-9+]/', '', (string) $rawHp);
            if (str_starts_with($cleanHp, '628')) {
                $cleanHp = '08'.substr($cleanHp, 3);
            }
            if ($cleanHp !== $rawHp && $cleanHp !== '') {
                $payload['no_hp'] = $cleanHp;
                $stats['hp_cleaned']++;
                $modified = true;
            }

            // D. Penyelarasan Status Pendataan 'Tempat tinggal di luar Kab Demak'
            $statusPend = trim($payload['status_pendataan'] ?? '');
            if ($statusPend === 'Tempat tinggal di luar Kab Demak') {
                $payload['status_pendataan'] = 'Belum';
                if (empty($payload['kecamatan']) || $payload['kecamatan'] === 'Demak') {
                    $payload['kecamatan'] = 'DI LUAR KABUPATEN DEMAK';
                }
                $stats['status_pendataan_normalized']++;
                $modified = true;
            }

            // E. Pembersihan Whitespace pada teks
            foreach (['nama_opd', 'nama_lengkap', 'nama_kk', 'desa', 'sls', 'alamat_lengkap'] as $field) {
                if (isset($payload[$field]) && is_string($payload[$field])) {
                    $trimmed = trim(preg_replace('/\s+/', ' ', $payload[$field]));
                    if ($trimmed !== $payload[$field]) {
                        $payload[$field] = $trimmed;
                        $stats['text_trimmed']++;
                        $modified = true;
                    }
                }
            }

            if ($modified) {
                $metadata = $record->metadata ?? [];
                $metadata['curated_at'] = now()->toDateTimeString();

                $record->payload = $payload;
                $record->metadata = $metadata;
                $record->save();
            }
        }

        // 2. Deteksi dan Penghapusan Duplikat (Menyimpan hanya pengisian terakhir)
        $recordsAfterUpdate = JawabanResponden::where('survey_id', $survey->id)
            ->orderBy('submitted_at', 'asc')
            ->get();

        $groupedByNik = [];
        foreach ($recordsAfterUpdate as $r) {
            $nik = preg_replace('/[^0-9]/', '', $r->payload['nik'] ?? '');
            if ($nik) {
                $groupedByNik[$nik][] = $r;
            }
        }

        foreach ($groupedByNik as $nik => $entries) {
            if (count($entries) > 1) {
                // Urutkan berdasarkan submitted_at ascending, record terakhir dipertahankan
                $latest = array_pop($entries);
                foreach ($entries as $oldEntry) {
                    $this->warn("Menghapus data duplikat lama ID: {$oldEntry->id} ({$oldEntry->payload['nama_lengkap']} - {$oldEntry->submitted_at}) | Dipertahankan ID: {$latest->id} ({$latest->submitted_at})");
                    $oldEntry->delete();
                    $stats['duplicates_removed']++;
                }
            }
        }

        $remainingCount = JawabanResponden::where('survey_id', $survey->id)->count();

        $this->newLine();
        $this->info('Kurasi data berhasil diselesaikan!');
        $this->table(
            ['Parameter Kurasi', 'Jumlah Terpengaruh'],
            [
                ['Status BLUD diseragamkan ke "BLUD/Karyawan Daerah"', $stats['blud_normalized']],
                ['Pembersihan karakter non-angka NIK (spasi, titik, awalan Nik.)', $stats['nik_cleaned']],
                ['Pembersihan No. HP / WhatsApp (format angka standar)', $stats['hp_cleaned']],
                ['Penyelarasan status "Tempat tinggal di luar Kab Demak" ke Belum', $stats['status_pendataan_normalized']],
                ['Normalisasi spasi teks (Nama, OPD, Alamat)', $stats['text_trimmed']],
                ['Duplikat input lama dihapus (menyimpan submit terakhir)', $stats['duplicates_removed']],
                ['Total data akhir responden', $remainingCount],
            ]
        );

        return self::SUCCESS;
    }
}
