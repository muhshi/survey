<?php

namespace App\Services;

use App\Enums\SurveyMode;
use App\Models\JawabanResponden;
use App\Models\Survey;
use App\Models\User;
use Carbon\Carbon;

class SurveySubmissionService
{
    /**
     * Check submission eligibility for the user based on mode and quiz retake rules.
     *
     * @return array{can_submit: bool, already_submitted: bool, message: string}
     */
    public function checkEligibility(Survey $survey, ?User $user): array
    {
        if (! $user) {
            return [
                'can_submit' => true,
                'already_submitted' => false,
                'message' => '',
            ];
        }

        if ($survey->mode === SurveyMode::Single) {
            $existing = JawabanResponden::where('survey_id', $survey->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($existing) {
                if ($survey->is_quiz) {
                    $settings = $survey->settings ?? [];
                    $allowRetake = filter_var($settings['allow_retake'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $maxRetakes = intval($settings['max_retakes'] ?? 1);

                    $attempts = JawabanResponden::where('survey_id', $survey->id)
                        ->where('user_id', $user->id)
                        ->count();

                    if (! $allowRetake) {
                        return [
                            'can_submit' => false,
                            'already_submitted' => true,
                            'message' => 'Anda sudah mengisi kuis ini dan tidak diizinkan untuk mengulang.',
                        ];
                    }

                    if ($attempts >= $maxRetakes) {
                        return [
                            'can_submit' => false,
                            'already_submitted' => true,
                            'message' => 'Batas maksimal pengulangan kuis ('.$maxRetakes.' kali percobaan) telah habis.',
                        ];
                    }

                    // Quota remains
                    return [
                        'can_submit' => true,
                        'already_submitted' => false,
                        'message' => '',
                    ];
                }

                return [
                    'can_submit' => false,
                    'already_submitted' => true,
                    'message' => 'Anda sudah mengisi survei ini. Mode sekali isi tidak mengizinkan pengisian ulang.',
                ];
            }
        }

        return [
            'can_submit' => true,
            'already_submitted' => false,
            'message' => '',
        ];
    }

    /**
     * Get existing submission for runner pre-filling if allowed by mode.
     */
    public function getExistingSubmission(Survey $survey, ?User $user): ?JawabanResponden
    {
        if (! $user) {
            return null;
        }

        // Never pre-fill answers for single mode, multi mode, or quizzes
        if ($survey->mode === SurveyMode::Single || $survey->mode === SurveyMode::Multi || $survey->is_quiz) {
            return null;
        }

        // Only pre-fill for editable mode
        return JawabanResponden::where('survey_id', $survey->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();
    }

    /**
     * Calculate score based on survey schema and payload.
     */
    public function calculateScore(Survey $survey, array $payload): float
    {
        $totalPoints = 0;
        $earnedPoints = 0;

        $schema = $survey->schema;
        if (! isset($schema['pages']) || ! is_array($schema['pages'])) {
            return 0;
        }

        foreach ($schema['pages'] as $page) {
            if (! isset($page['elements']) || ! is_array($page['elements'])) {
                continue;
            }

            foreach ($page['elements'] as $element) {
                if (isset($element['correctAnswer'])) {
                    // Get points for this question, default to 1 if not set
                    $points = isset($element['score']) ? (float) $element['score'] : 1;
                    $totalPoints += $points;

                    $questionName = $element['name'];
                    $correctAnswer = $element['correctAnswer'];

                    // Check if question is answered and matches correct answer
                    if (isset($payload[$questionName])) {
                        $userAnswer = $payload[$questionName];

                        // Handle potential different types (SurveyJS radio is often string)
                        if ($userAnswer == $correctAnswer) {
                            $earnedPoints += $points;
                        }
                    }
                }
            }
        }

        if ($totalPoints === 0) {
            return 0;
        }

        return round(($earnedPoints / $totalPoints) * 100, 2);
    }

    /**
     * Store a new survey submission and construct response data.
     *
     * @return array{success: bool, message: string, quiz?: array<string, mixed>}
     */
    public function storeSubmission(Survey $survey, ?User $user, array $payload, array $metadata): array
    {
        $score = null;
        if ($survey->is_quiz && $survey->schema) {
            $score = $this->calculateScore($survey, $payload);
        }

        $jawaban = new JawabanResponden;
        $jawaban->survey_id = $survey->id;
        $jawaban->user_id = $user?->id;
        $jawaban->payload = $payload;
        $jawaban->score = $score;
        $jawaban->metadata = $metadata;
        $jawaban->submitted_at = Carbon::now();
        $jawaban->save();

        $responseData = [
            'success' => true,
            'message' => 'Jawaban Anda telah berhasil disimpan.',
        ];

        if ($survey->is_quiz && $score !== null) {
            $settings = $survey->settings ?? [];
            $passingScore = floatval($settings['passing_score'] ?? 0);
            $allowRetake = filter_var($settings['allow_retake'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $maxRetakes = intval($settings['max_retakes'] ?? 1);

            $attempts = $user
                ? JawabanResponden::where('survey_id', $survey->id)
                    ->where('user_id', $user->id)
                    ->count()
                : 1;

            $passed = $passingScore > 0 && $score >= $passingScore;
            $canRetake = $allowRetake && $attempts < $maxRetakes;

            $responseData['quiz'] = [
                'score' => $score,
                'passing_score' => $passingScore,
                'passed' => $passed,
                'can_retake' => $canRetake,
                'attempts' => $attempts,
                'max_retakes' => $maxRetakes,
            ];
        }

        return $responseData;
    }

    /**
     * Update an existing submission (editable mode).
     *
     * @return array{success: bool, message: string}
     */
    public function updateSubmission(Survey $survey, User $user, array $payload, array $metadata): array
    {
        $existing = JawabanResponden::where('survey_id', $survey->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $score = null;
        if ($survey->is_quiz && $survey->schema) {
            $score = $this->calculateScore($survey, $payload);
        }

        if ($existing) {
            $existing->payload = $payload;
            if ($survey->is_quiz) {
                $existing->score = $score;
            }

            $existing->metadata = array_merge($existing->metadata ?? [], [
                'last_edited_at' => now()->toDateTimeString(),
                'edit_ip' => $metadata['ip'] ?? null,
            ]);
            $existing->save();
        } else {
            $jawaban = new JawabanResponden;
            $jawaban->survey_id = $survey->id;
            $jawaban->user_id = $user->id;
            $jawaban->payload = $payload;
            $jawaban->score = $score;
            $jawaban->metadata = $metadata;
            $jawaban->submitted_at = Carbon::now();
            $jawaban->save();
        }

        return [
            'success' => true,
            'message' => 'Jawaban berhasil diperbarui.',
        ];
    }
}
