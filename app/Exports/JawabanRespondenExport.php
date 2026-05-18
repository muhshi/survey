<?php

namespace App\Exports;

use App\Models\JawabanResponden;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JawabanRespondenExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected int $surveyId;

    protected array $selectedFields;

    protected ?Collection $usersCache = null;

    protected ?array $parsedSchema = null;

    public function __construct(int $surveyId, array $selectedFields)
    {
        $this->surveyId = $surveyId;
        $this->selectedFields = $selectedFields;
    }

    protected function getUsersCache()
    {
        if ($this->usersCache === null) {
            // Fetch all users once with minimal columns to prevent N+1 and save memory
            $this->usersCache = User::select('id', 'name', 'email')->get()->keyBy('id');
        }

        return $this->usersCache;
    }

    public function query()
    {
        return JawabanResponden::query()->with('user')->where('survey_id', $this->surveyId);
    }

    protected function getParsedSchema()
    {
        if (! isset($this->parsedSchema)) {
            $survey = Survey::find($this->surveyId);
            $this->parsedSchema = $survey ? $survey->getParsedSchema() : ['fields' => [], 'choices' => []];
        }

        return $this->parsedSchema;
    }

    public function headings(): array
    {
        $schemaFields = $this->getParsedSchema()['fields'];

        return array_map(function ($field) use ($schemaFields) {
            // Use standard overrides first
            $overrides = [
                'waktu_submit' => 'Waktu Submit',
                'skor_kuis' => 'Skor Kuis',
                'nama_pewawancara' => 'Nama Pewawancara',
                'nama_peserta' => 'Nama Peserta',
                'email_peserta' => 'Email Peserta',
            ];

            if (isset($overrides[$field])) {
                return $overrides[$field];
            }

            // Fallback to schema title, then raw key
            return $schemaFields[$field] ?? ucwords(str_replace('_', ' ', $field));
        }, $this->selectedFields);
    }

    public function map($jawaban): array
    {
        $row = [];
        $payload = $jawaban->payload ?? [];
        $choicesMap = $this->getParsedSchema()['choices'];

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
                        $peserta = $this->getUsersCache()->get($payload['nama_peserta']);
                        $row[] = $peserta ? $peserta->name : 'Unknown ('.$payload['nama_peserta'].')';
                    } elseif (isset($payload['nama_peserta'])) {
                        $row[] = $payload['nama_peserta'];
                    } else {
                        $row[] = '-';
                    }
                    break;
                case 'email_peserta':
                    if (isset($payload['nama_peserta']) && is_numeric($payload['nama_peserta'])) {
                        $peserta = $this->getUsersCache()->get($payload['nama_peserta']);
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
                        // Map each item in the array if there are choices defined
                        if (isset($choicesMap[$field])) {
                            $mappedArray = array_map(function ($v) use ($choicesMap, $field) {
                                return $choicesMap[$field][$v] ?? $v;
                            }, $val);
                            $row[] = implode(', ', $mappedArray);
                        } else {
                            $row[] = implode(', ', $val);
                        }
                    } else {
                        // Map the single value if choice exists
                        if (isset($choicesMap[$field][$val])) {
                            $row[] = $choicesMap[$field][$val];
                        } else {
                            $row[] = $val;
                        }
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
