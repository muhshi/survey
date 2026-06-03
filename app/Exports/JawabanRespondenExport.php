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

    protected ?string $submittedFrom = null;

    protected ?string $submittedUntil = null;

    protected ?Collection $usersCache = null;

    protected ?array $parsedSchema = null;

    public function __construct(int $surveyId, array $selectedFields, ?string $submittedFrom = null, ?string $submittedUntil = null)
    {
        $this->surveyId = $surveyId;
        $this->selectedFields = $selectedFields;
        $this->submittedFrom = $submittedFrom;
        $this->submittedUntil = $submittedUntil;
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
        return JawabanResponden::query()
            ->with('user')
            ->where('survey_id', $this->surveyId)
            ->when($this->submittedFrom, fn ($q, $date) => $q->whereDate('submitted_at', '>=', $date))
            ->when($this->submittedUntil, fn ($q, $date) => $q->whereDate('submitted_at', '<=', $date));
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
                    $row[] = $jawaban->user ? html_entity_decode($jawaban->user->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : 'Anonim';
                    break;
                case 'skor_kuis':
                    $row[] = $jawaban->score !== null ? $jawaban->score : '-';
                    break;
                case 'waktu_submit':
                    $row[] = $jawaban->submitted_at ? $jawaban->submitted_at->format('Y-m-d H:i:s') : '-';
                    break;
                case 'nama_peserta':
                case 'pilih_peserta':
                    $pesertaId = $payload['nama_peserta'] ?? $payload['pilih_peserta'] ?? null;
                    if ($pesertaId && is_numeric($pesertaId)) {
                        $peserta = $this->getUsersCache()->get($pesertaId);
                        $row[] = $peserta ? html_entity_decode($peserta->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : 'Unknown ('.$pesertaId.')';
                    } elseif ($pesertaId) {
                        $row[] = html_entity_decode((string) $pesertaId, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    } else {
                        $row[] = '-';
                    }
                    break;
                case 'email_peserta':
                    $pesertaId = $payload['nama_peserta'] ?? $payload['pilih_peserta'] ?? null;
                    if ($pesertaId && is_numeric($pesertaId)) {
                        $peserta = $this->getUsersCache()->get($pesertaId);
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
                                $mapped = $choicesMap[$field][$v] ?? $v;

                                return is_string($mapped) ? html_entity_decode($mapped, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $mapped;
                            }, $val);
                            $row[] = implode(', ', $mappedArray);
                        } else {
                            $row[] = implode(', ', array_map(fn ($v) => is_string($v) ? html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $v, $val));
                        }
                    } else {
                        // Map the single value if choice exists
                        if (isset($choicesMap[$field][$val])) {
                            $mapped = $choicesMap[$field][$val];
                            $row[] = is_string($mapped) ? html_entity_decode($mapped, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $mapped;
                        } else {
                            $row[] = is_string($val) ? html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $val;
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
