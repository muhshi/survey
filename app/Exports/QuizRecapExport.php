<?php

namespace App\Exports;

use App\Models\JawabanResponden;
use App\Models\Survey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuizRecapExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected Survey $survey;

    protected array $selectedFields;

    protected ?string $statusFilter = null;

    protected float $passingScore;

    public function __construct(Survey $survey, array $selectedFields, ?string $statusFilter = null)
    {
        $this->survey = $survey;
        $this->selectedFields = $selectedFields;
        $this->statusFilter = $statusFilter;
        $this->passingScore = floatval($survey->settings['passing_score'] ?? 0);
    }

    public function query()
    {
        $query = User::query()
            ->with(['jawaban_responden' => function ($q) {
                $q->where('survey_id', $this->survey->id);
            }])
            ->whereHas('jawaban_responden', function (Builder $q) {
                $q->where('survey_id', $this->survey->id);
            })
            ->addSelect([
                'best_score' => JawabanResponden::select(DB::raw('MAX(score)'))
                    ->whereColumn('user_id', 'users.id')
                    ->where('survey_id', $this->survey->id),
                'attempts_count' => JawabanResponden::select(DB::raw('COUNT(*)'))
                    ->whereColumn('user_id', 'users.id')
                    ->where('survey_id', $this->survey->id),
                'latest_submission' => JawabanResponden::select(DB::raw('MAX(submitted_at)'))
                    ->whereColumn('user_id', 'users.id')
                    ->where('survey_id', $this->survey->id),
            ])
            ->orderBy('best_score', 'asc');

        if ($this->statusFilter === 'passed') {
            $query->whereHas('jawaban_responden', function (Builder $q) {
                $q->where('survey_id', $this->survey->id)
                    ->where('score', '>=', $this->passingScore);
            });
        } elseif ($this->statusFilter === 'failed') {
            $query->whereDoesntHave('jawaban_responden', function (Builder $q) {
                $q->where('survey_id', $this->survey->id)
                    ->where('score', '>=', $this->passingScore);
            });
        }

        return $query;
    }

    public function headings(): array
    {
        $parsed = $this->survey->getParsedSchema();
        $schemaFields = $parsed['fields'] ?? [];

        return array_map(function ($field) use ($schemaFields) {
            $overrides = [
                'nama_peserta' => 'Peserta',
                'email_peserta' => 'Email',
                'attempts_count' => 'Total Percobaan',
                'best_score' => 'Skor Terbaik',
                'status_lulus' => 'Status Lulus',
                'latest_submission' => 'Submit Terakhir',
            ];

            if (isset($overrides[$field])) {
                return $overrides[$field];
            }

            return $schemaFields[$field] ?? ucwords(str_replace('_', ' ', $field));
        }, $this->selectedFields);
    }

    public function map($user): array
    {
        $row = [];

        // Dapatkan jawaban terbaik (berdasarkan skor tertinggi, jika sama ambil terbaru)
        $bestAttempt = $user->jawaban_responden->sortByDesc(function ($j) {
            return $j->score.'_'.$j->submitted_at->timestamp;
        })->first();

        $payload = $bestAttempt ? ($bestAttempt->payload ?? []) : [];
        $parsed = $this->survey->getParsedSchema();
        $choicesMap = $parsed['choices'] ?? [];

        foreach ($this->selectedFields as $field) {
            switch ($field) {
                case 'nama_peserta':
                    $row[] = html_entity_decode($user->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    break;
                case 'email_peserta':
                    $row[] = $user->email ?? '-';
                    break;
                case 'attempts_count':
                    $row[] = $user->attempts_count ?? 0;
                    break;
                case 'best_score':
                    $row[] = $user->best_score !== null ? number_format((float) $user->best_score, 1).'%' : '-';
                    break;
                case 'status_lulus':
                    $row[] = $user->best_score >= $this->passingScore ? 'Lulus' : 'Belum Lulus';
                    break;
                case 'latest_submission':
                    $row[] = $user->latest_submission ? Carbon::parse($user->latest_submission)->format('Y-m-d H:i:s') : '-';
                    break;
                default:
                    $val = $payload[$field] ?? null;

                    if (is_bool($val)) {
                        $row[] = $val ? 'Ya' : 'Tidak';
                    } elseif (is_array($val)) {
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
