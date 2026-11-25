<?php

namespace App\Services;

use App\Models\JawabanResponden;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class QuizRecapService
{
    /**
     * Build deduplicated quiz recap rows (1 per participant: highest score, fastest submission on tie),
     * enriched with class labels and per-class rankings.
     *
     * @return Collection<int, array{
     *     participant_key: string,
     *     name: string,
     *     email: string,
     *     kelas_raw: ?string,
     *     kelas_label: string,
     *     kelas_subtitle: ?string,
     *     best_attempt: JawabanResponden,
     *     best_score: float,
     *     best_submitted_at: ?Carbon,
     *     latest_submission: ?Carbon,
     *     attempts_count: int,
     *     passed: bool,
     *     is_pegawai: bool,
     *     class_rank: int
     * }>
     */
    public function getBestParticipantAttempts(
        Survey $survey,
        ?string $submittedFrom = null,
        ?string $submittedUntil = null,
        ?string $statusFilter = null,
        bool $excludePegawai = false,
    ): Collection {
        $passingScore = (float) ($survey->settings['passing_score'] ?? 0);
        $parsed = $survey->getParsedSchema();
        $choicesMap = $parsed['choices'] ?? [];

        $submissions = JawabanResponden::query()
            ->with('user')
            ->where('survey_id', $survey->id)
            ->when($submittedFrom, fn ($q, $date) => $q->whereDate('submitted_at', '>=', $date))
            ->when($submittedUntil, fn ($q, $date) => $q->whereDate('submitted_at', '<=', $date))
            ->get();

        if ($submissions->isEmpty()) {
            return collect();
        }

        $groupedByParticipant = $submissions->groupBy(function (JawabanResponden $sub): string {
            if ($sub->user_id) {
                return 'user_'.$sub->user_id;
            }

            $email = strtolower(trim((string) ($sub->payload['email_peserta'] ?? $sub->payload['email'] ?? '')));
            if ($email !== '') {
                return 'email_'.$email;
            }

            $name = strtolower(trim((string) ($sub->payload['nama_lengkap'] ?? $sub->payload['nama_peserta'] ?? '')));
            if ($name !== '') {
                return 'name_'.$name;
            }

            return 'row_'.$sub->id;
        });

        $participants = $groupedByParticipant->map(function (Collection $attempts, string $key) use ($passingScore, $choicesMap): array {
            $bestAttempt = $this->pickBestAttempt($attempts);
            $payload = $bestAttempt->payload ?? [];
            $user = $bestAttempt->user;

            $rawName = $user?->name
                ?? $payload['nama_lengkap']
                ?? $payload['nama_peserta']
                ?? $payload['nama']
                ?? 'Anonim';

            $email = $user?->email
                ?? $payload['email_peserta']
                ?? $payload['email']
                ?? '-';

            $kelasRaw = $payload['kelas'] ?? ($user?->metadata['kelas'] ?? null);
            if (is_array($kelasRaw)) {
                $kelasRaw = (string) ($kelasRaw[0] ?? '');
            }
            $kelasRaw = $kelasRaw !== null ? trim((string) $kelasRaw) : null;
            if ($kelasRaw === '') {
                $kelasRaw = null;
            }

            [$kelasLabel, $kelasSubtitle] = $this->resolveClassDisplay($kelasRaw, $choicesMap['kelas'] ?? []);

            $bestScore = (float) ($bestAttempt->score ?? 0);
            $latestSubmission = $attempts->max('submitted_at');

            return [
                'participant_key' => $key,
                'name' => html_entity_decode((string) $rawName, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'email' => (string) $email,
                'kelas_raw' => $kelasRaw,
                'kelas_label' => $kelasLabel,
                'kelas_subtitle' => $kelasSubtitle,
                'best_attempt' => $bestAttempt,
                'best_score' => $bestScore,
                'best_submitted_at' => $bestAttempt->submitted_at,
                'latest_submission' => $latestSubmission,
                'attempts_count' => $attempts->count(),
                'passed' => $bestScore >= $passingScore,
                'is_pegawai' => $this->isPegawai($user, $payload),
                'class_rank' => 0,
            ];
        })->values();

        if ($excludePegawai) {
            $participants = $participants->reject(fn (array $row) => $row['is_pegawai'])->values();
        }

        if ($statusFilter === 'passed') {
            $participants = $participants->filter(fn (array $row) => $row['passed'])->values();
        } elseif ($statusFilter === 'failed') {
            $participants = $participants->filter(fn (array $row) => ! $row['passed'])->values();
        }

        // Rank within each class: highest score first, fastest submission time as tie-breaker.
        $ranked = collect();
        $participants->groupBy('kelas_label')
            ->sortKeys()
            ->each(function (Collection $classGroup) use ($ranked): void {
                $sorted = $this->sortByScoreAndTime($classGroup)->values();
                foreach ($sorted as $index => $entry) {
                    $entry['class_rank'] = $index + 1;
                    $ranked->push($entry);
                }
            });

        return $ranked;
    }

    /**
     * Get top N performers grouped by class for leaderboard presentation.
     *
     * @return array<int, array{
     *     kelas: string,
     *     subtitle: ?string,
     *     total_participants: int,
     *     winners: array<int, array<string, mixed>>
     * }>
     */
    public function getTopPerformersByClass(
        Survey $survey,
        int $limit = 3,
        ?string $submittedFrom = null,
        ?string $submittedUntil = null,
        bool $excludePegawai = false,
    ): array {
        $ranked = $this->getBestParticipantAttempts($survey, $submittedFrom, $submittedUntil, null, $excludePegawai);
        if ($ranked->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($ranked->groupBy('kelas_label') as $kelas => $entries) {
            $first = $entries->first();
            $result[] = [
                'kelas' => (string) $kelas,
                'subtitle' => $first['kelas_subtitle'] ?? null,
                'total_participants' => $entries->count(),
                'winners' => $entries->take($limit)->values()->all(),
            ];
        }

        return $result;
    }

    /**
     * Determine whether a respondent belongs to BPS internal staff (Pegawai).
     *
     * @param  array<string, mixed>  $payload
     */
    public function isPegawai(?User $user, array $payload = []): bool
    {
        if ($user) {
            if ($user->identity_type === 'pegawai') {
                return true;
            }

            if (str_ends_with(strtolower((string) $user->email), '@bps.go.id')) {
                return true;
            }

            if (! empty($user->metadata['nip']) || ! empty($user->metadata['nip_baru'])) {
                return true;
            }
        }

        $email = strtolower(trim((string) ($payload['email_peserta'] ?? $payload['email'] ?? '')));

        return $email !== '' && str_ends_with($email, '@bps.go.id');
    }

    /**
     * Pick the single best attempt: highest score first, earliest submission on tie.
     */
    private function pickBestAttempt(Collection $attempts): JawabanResponden
    {
        return $attempts->sort(function (JawabanResponden $a, JawabanResponden $b): int {
            $scoreA = (float) ($a->score ?? -1);
            $scoreB = (float) ($b->score ?? -1);

            if ($scoreB !== $scoreA) {
                return $scoreB <=> $scoreA;
            }

            $timeA = $a->submitted_at?->getTimestamp() ?? PHP_INT_MAX;
            $timeB = $b->submitted_at?->getTimestamp() ?? PHP_INT_MAX;

            if ($timeA !== $timeB) {
                return $timeA <=> $timeB;
            }

            return $a->id <=> $b->id;
        })->first();
    }

    /**
     * Sort participant rows by highest score, then fastest submission timestamp.
     */
    private function sortByScoreAndTime(Collection $rows): Collection
    {
        return $rows->sort(function (array $a, array $b): int {
            if ($b['best_score'] !== $a['best_score']) {
                return $b['best_score'] <=> $a['best_score'];
            }

            $timeA = $a['best_submitted_at']?->getTimestamp() ?? PHP_INT_MAX;
            $timeB = $b['best_submitted_at']?->getTimestamp() ?? PHP_INT_MAX;

            if ($timeA !== $timeB) {
                return $timeA <=> $timeB;
            }

            return strcmp($a['name'], $b['name']);
        });
    }

    /**
     * Format class label and optional instructor subtitle from raw value and schema choices.
     *
     * @param  array<string, string>  $kelasChoices
     * @return array{0: string, 1: ?string}
     */
    private function resolveClassDisplay(?string $kelasRaw, array $kelasChoices): array
    {
        if ($kelasRaw === null) {
            return ['Tanpa Kelas', null];
        }

        $choiceText = $kelasChoices[$kelasRaw] ?? null;
        if (is_string($choiceText) && str_contains($choiceText, '(')) {
            if (preg_match('/^(.+?)\s*\((.+)\)$/', $choiceText, $matches)) {
                return [trim($matches[1]), trim($matches[2])];
            }
        }

        if (strlen($kelasRaw) === 1 && ctype_alpha($kelasRaw)) {
            return ['Kelas '.strtoupper($kelasRaw), $choiceText !== $kelasRaw ? $choiceText : null];
        }

        return [$kelasRaw, ($choiceText && $choiceText !== $kelasRaw) ? $choiceText : null];
    }
}
