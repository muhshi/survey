<?php

namespace App\Exports;

use App\Models\Survey;
use App\Services\QuizRecapService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuizRecapExport extends DefaultValueBinder implements FromCollection, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles
{
    protected Survey $survey;

    protected array $selectedFields;

    protected ?string $statusFilter = null;

    protected ?string $submittedFrom = null;

    protected ?string $submittedUntil = null;

    protected bool $excludePegawai = false;

    protected float $passingScore;

    public function __construct(
        Survey $survey,
        array $selectedFields,
        ?string $statusFilter = null,
        ?string $submittedFrom = null,
        ?string $submittedUntil = null,
        bool $excludePegawai = false,
    ) {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $this->survey = $survey;
        $this->selectedFields = $selectedFields;
        $this->statusFilter = $statusFilter;
        $this->submittedFrom = $submittedFrom;
        $this->submittedUntil = $submittedUntil;
        $this->excludePegawai = $excludePegawai;
        $this->passingScore = (float) ($survey->settings['passing_score'] ?? 0);
    }

    public function collection(): Collection
    {
        return app(QuizRecapService::class)->getBestParticipantAttempts(
            $this->survey,
            $this->submittedFrom,
            $this->submittedUntil,
            $this->statusFilter,
            $this->excludePegawai,
        );
    }

    public function headings(): array
    {
        $parsed = $this->survey->getParsedSchema();
        $schemaFields = $parsed['fields'] ?? [];

        return array_map(function ($field) use ($schemaFields) {
            $overrides = [
                'nama_peserta' => 'Peserta',
                'email_peserta' => 'Email',
                'tipe_peserta' => 'Kategori Peserta',
                'kelas' => 'Kelas',
                'peringkat_kelas' => 'Peringkat Kelas',
                'attempts_count' => 'Total Percobaan',
                'best_score' => 'Skor Terbaik',
                'status_lulus' => 'Status Lulus',
                'waktu_tercepat' => 'Waktu Skor Terbaik',
                'latest_submission' => 'Submit Terakhir',
            ];

            if (isset($overrides[$field])) {
                return $overrides[$field];
            }

            return $schemaFields[$field] ?? ucwords(str_replace('_', ' ', $field));
        }, $this->selectedFields);
    }

    public function map($entry): array
    {
        $row = [];
        $bestAttempt = $entry['best_attempt'];
        $payload = $bestAttempt ? ($bestAttempt->payload ?? []) : [];
        $parsed = $this->survey->getParsedSchema();
        $choicesMap = $parsed['choices'] ?? [];

        foreach ($this->selectedFields as $field) {
            switch ($field) {
                case 'nama_peserta':
                    $row[] = $entry['name'];
                    break;
                case 'email_peserta':
                    $row[] = $entry['email'];
                    break;
                case 'tipe_peserta':
                    $row[] = $entry['is_pegawai'] ? 'Pegawai' : 'Mitra';
                    break;
                case 'kelas':
                    $row[] = $entry['kelas_label'];
                    break;
                case 'peringkat_kelas':
                    $row[] = $entry['class_rank'];
                    break;
                case 'attempts_count':
                    $row[] = $entry['attempts_count'];
                    break;
                case 'best_score':
                    $row[] = number_format((float) $entry['best_score'], 1).'%';
                    break;
                case 'status_lulus':
                    $row[] = $entry['passed'] ? 'Lulus' : 'Belum Lulus';
                    break;
                case 'waktu_tercepat':
                    $row[] = $entry['best_submitted_at']
                        ? Carbon::parse($entry['best_submitted_at'])->format('Y-m-d H:i:s')
                        : '-';
                    break;
                case 'latest_submission':
                    $row[] = $entry['latest_submission']
                        ? Carbon::parse($entry['latest_submission'])->format('Y-m-d H:i:s')
                        : '-';
                    break;
                default:
                    $val = $payload[$field] ?? null;
                    $row[] = $this->formatFieldValue($val, $choicesMap[$field] ?? []);
                    break;
            }
        }

        return $row;
    }

    /**
     * Format a single payload field value cleanly for Excel output.
     *
     * @param  array<string, string>  $fieldChoices
     */
    private function formatFieldValue(mixed $val, array $fieldChoices): mixed
    {
        if (is_bool($val)) {
            return $val ? 'Ya' : 'Tidak';
        }

        if (is_array($val)) {
            $isAssoc = $val !== [] && array_keys($val) !== range(0, count($val) - 1);
            if ($isAssoc) {
                $mappedAssoc = [];
                foreach ($val as $subKey => $subVal) {
                    $subValStr = is_array($subVal) || is_object($subVal)
                        ? json_encode($subVal, JSON_UNESCAPED_UNICODE)
                        : (string) $subVal;
                    $mappedAssoc[] = "{$subKey}: ".html_entity_decode($subValStr, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }

                return implode(' | ', $mappedAssoc);
            }

            return implode(', ', array_map(function ($v) use ($fieldChoices) {
                if (is_array($v) || is_object($v)) {
                    return json_encode($v, JSON_UNESCAPED_UNICODE);
                }
                $mapped = $fieldChoices[$v] ?? $v;

                return is_string($mapped) ? html_entity_decode($mapped, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $mapped;
            }, $val));
        }

        if (is_scalar($val) && isset($fieldChoices[$val])) {
            $mapped = $fieldChoices[$val];

            return is_string($mapped) ? html_entity_decode($mapped, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $mapped;
        }

        if (is_object($val)) {
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        }

        return is_string($val) ? html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $val;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $strVal = (string) $value;
            if (strlen($strVal) >= 10 || (strlen($strVal) > 1 && str_starts_with($strVal, '0'))) {
                $cell->setValueExplicit($strVal, DataType::TYPE_STRING);

                return true;
            }
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        $formats = [];
        foreach ($this->selectedFields as $index => $field) {
            $colLetter = Coordinate::stringFromColumnIndex($index + 1);
            $fieldLower = strtolower($field);

            if (
                str_contains($fieldLower, 'nik') ||
                str_contains($fieldLower, 'kk') ||
                str_contains($fieldLower, 'hp') ||
                str_contains($fieldLower, 'wa') ||
                str_contains($fieldLower, 'telp') ||
                str_contains($fieldLower, 'sls') ||
                str_contains($fieldLower, 'kode')
            ) {
                $formats[$colLetter] = NumberFormat::FORMAT_TEXT;
            }
        }

        return $formats;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
