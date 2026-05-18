<?php

namespace App\Exports;

use App\Models\JawabanResponden;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JawabanRespondenExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected int $surveyId;

    protected array $selectedFields;

    public function __construct(int $surveyId, array $selectedFields)
    {
        $this->surveyId = $surveyId;
        $this->selectedFields = $selectedFields;
    }

    public function query()
    {
        return JawabanResponden::query()->with('user')->where('survey_id', $this->surveyId);
    }

    public function headings(): array
    {
        return array_map(function ($field) {
            return ucwords(str_replace('_', ' ', $field));
        }, $this->selectedFields);
    }

    public function map($jawaban): array
    {
        $row = [];
        $payload = $jawaban->payload ?? [];

        foreach ($this->selectedFields as $field) {
            switch ($field) {
                case 'nama_pewawancara':
                    $row[] = $jawaban->user ? $jawaban->user->name : 'Anonim';
                    break;
                case 'skor_kuis':
                    $row[] = $jawaban->score !== null ? $jawaban->score : '-';
                    break;
                case 'waktu_submit':
                    $row[] = $jawaban->submitted_at ? $jawaban->submitted_at->format('Y-m-d H:i:s') : '-';
                    break;
                case 'nama_peserta':
                    if (isset($payload['nama_peserta']) && is_numeric($payload['nama_peserta'])) {
                        $peserta = User::find($payload['nama_peserta']);
                        $row[] = $peserta ? $peserta->name : 'Unknown ('.$payload['nama_peserta'].')';
                    } elseif (isset($payload['nama_peserta'])) {
                        $row[] = $payload['nama_peserta'];
                    } else {
                        $row[] = '-';
                    }
                    break;
                case 'email_peserta':
                    if (isset($payload['nama_peserta']) && is_numeric($payload['nama_peserta'])) {
                        $peserta = User::find($payload['nama_peserta']);
                        $row[] = $peserta ? $peserta->email : '-';
                    } else {
                        $row[] = $payload['email_peserta'] ?? '-';
                    }
                    break;
                default:
                    $val = $payload[$field] ?? null;
                    if (is_bool($val)) {
                        $row[] = $val ? 'Ya' : 'Tidak';
                    } elseif (is_array($val)) {
                        $row[] = implode(', ', $val);
                    } else {
                        $row[] = $val;
                    }
                    break;
            }
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
