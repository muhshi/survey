<?php

namespace App\Console\Commands;

use App\Enums\SurveyMode;
use App\Models\JawabanResponden;
use App\Models\Kategori;
use App\Models\Survey;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('survey:import-gform-se2026 {--file= : Path opsional ke file CSV} {--force : Update data jika sudah ada}')]
#[Description('Import data responden konfirmasi SE2026 dari Google Forms ke sistem survei')]
class ImportGformSe2026 extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->option('file') ?: database_path('data/gform-se2026.csv');

        if (! file_exists($filePath)) {
            $this->error("File CSV tidak ditemukan di: {$filePath}");

            return self::FAILURE;
        }

        $schemaPath = database_path('surveys/konfirmasi-pendataan-se2026.json');
        if (! file_exists($schemaPath)) {
            $this->error("File skema survei tidak ditemukan di: {$schemaPath}");

            return self::FAILURE;
        }

        $schema = json_decode(file_get_contents($schemaPath), true);
        if (! $schema) {
            $this->error('Format skema survei JSON tidak valid.');

            return self::FAILURE;
        }

        $kategoriId = Kategori::where('name', 'Sensus')->value('id') ?: Kategori::first()?->id ?: 1;

        // 1. Dapatkan atau buat survei
        $survey = Survey::firstOrNew(['slug' => 'konfirmasi-pendataan-se2026']);
        $survey->kategori_id = $kategoriId;
        $survey->title = 'Konfirmasi Pendataan Sensus Ekonomi 2026 (SE2026)';
        $survey->description = 'Konfirmasi pendataan Sensus Ekonomi 2026 bagi ASN, PPPK, dan Tenaga Non-ASN di lingkungan Pemerintah Kabupaten Demak.';
        $survey->schema = $schema;
        $survey->mode = SurveyMode::Single;
        $survey->access_level = 'public';
        $survey->is_active = true;
        $survey->is_quiz = false;
        $survey->save();

        $this->info("Survei berhasil dipersiapkan (ID: {$survey->id}, Slug: {$survey->slug})");

        // 2. Baca file CSV
        $fp = fopen($filePath, 'r');
        if (! $fp) {
            $this->error("Gagal membuka file: {$filePath}");

            return self::FAILURE;
        }

        // Baca header
        $header = fgetcsv($fp);
        if (! $header) {
            $this->error('File CSV kosong.');
            fclose($fp);

            return self::FAILURE;
        }

        $this->info('Memulai impor baris responden...');

        $imported = 0;
        $skipped = 0;
        $rowNum = 0;

        // Ambil existing gform_row untuk idempotensi
        $existingRows = JawabanResponden::where('survey_id', $survey->id)
            ->whereNotNull('metadata->gform_row')
            ->get(['id', 'metadata'])
            ->mapWithKeys(fn ($item) => [($item->metadata['gform_row'] ?? null) => $item->id])
            ->filter()
            ->toArray();

        $recordsToInsert = [];
        $now = now();

        while (($row = fgetcsv($fp)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }

            $rowNum++;

            // Timestamp parsing: "18/09/2026 10:05:54"
            $timestampRaw = trim($row[0] ?? '');
            $submittedAt = null;
            if ($timestampRaw) {
                try {
                    $submittedAt = Carbon::createFromFormat('d/m/Y H:i:s', $timestampRaw);
                } catch (\Exception $e) {
                    $submittedAt = null;
                }
            }
            if (! $submittedAt) {
                $submittedAt = $now;
            }

            $email = trim($row[1] ?? '');
            $namaOpd = trim($row[2] ?? '');
            $namaLengkap = trim($row[3] ?? '');
            $nik = trim($row[4] ?? '');
            $statusKepegawaian = trim($row[5] ?? '');
            $namaKk = trim($row[6] ?? '');
            $statusPendataan = trim($row[7] ?? '');
            $kecamatan = trim($row[8] ?? '');
            $desa = trim($row[9] ?? '');
            $sls = trim($row[10] ?? '');
            $alamatLengkap = trim($row[11] ?? '');
            $noHp = trim($row[12] ?? '');
            $tindakLanjut = trim($row[13] ?? '');

            $payload = [
                'email' => $email,
                'nama_opd' => $namaOpd,
                'nama_lengkap' => $namaLengkap,
                'nik' => $nik,
                'status_kepegawaian' => $statusKepegawaian,
                'nama_kk' => $namaKk,
                'status_pendataan' => $statusPendataan,
                'kecamatan' => $kecamatan,
                'desa' => $desa,
                'sls' => $sls,
                'alamat_lengkap' => $alamatLengkap,
                'no_hp' => $noHp,
            ];

            if ($tindakLanjut !== '') {
                $payload['tindak_lanjut_fasih'] = $tindakLanjut;
            }

            $metadata = [
                'source' => 'google_forms',
                'gform_row' => $rowNum,
                'original_timestamp' => $timestampRaw,
            ];

            if (isset($existingRows[$rowNum]) && ! $this->option('force')) {
                $skipped++;

                continue;
            }

            if (isset($existingRows[$rowNum]) && $this->option('force')) {
                JawabanResponden::where('id', $existingRows[$rowNum])->update([
                    'payload' => json_encode($payload),
                    'metadata' => json_encode($metadata),
                    'submitted_at' => $submittedAt,
                    'updated_at' => $now,
                ]);
                $imported++;

                continue;
            }

            $recordsToInsert[] = [
                'survey_id' => $survey->id,
                'user_id' => null,
                'payload' => json_encode($payload),
                'score' => null,
                'metadata' => json_encode($metadata),
                'submitted_at' => $submittedAt->toDateTimeString(),
                'created_at' => $submittedAt->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ];

            $imported++;

            // Batch insert setiap 200 record
            if (count($recordsToInsert) >= 200) {
                JawabanResponden::insert($recordsToInsert);
                $recordsToInsert = [];
            }
        }

        if (! empty($recordsToInsert)) {
            JawabanResponden::insert($recordsToInsert);
        }

        fclose($fp);

        $this->newLine();
        $this->info("Impor selesai! Berhasil: {$imported}, Dilewati (sudah ada): {$skipped}, Total Baris: {$rowNum}");
        $this->info("URL Survei Publik: /survey/{$survey->slug}");

        return self::SUCCESS;
    }
}
