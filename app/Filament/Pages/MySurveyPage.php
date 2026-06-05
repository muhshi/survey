<?php

namespace App\Filament\Pages;

use App\Models\JawabanResponden;
use App\Models\Survey;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MySurveyPage extends Page
{
    protected static string $view = 'filament.pages.my-survey-page';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'My Survey';

    protected static ?string $title = 'Survey Saya';

    protected static ?int $navigationSort = -1;

    /**
     * Get the data to pass to the view.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = Auth::user();

        /** @var array<int, array<string, mixed>> $submissions */
        $submissions = JawabanResponden::with('survey.kategori')
            ->where('user_id', $user->id)
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function (JawabanResponden $jawaban) {
                $survey = $jawaban->survey;
                $settings = $survey?->settings ?? [];
                $passingScore = floatval($settings['passing_score'] ?? 0);
                $allowRetake = filter_var($settings['allow_retake'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $maxRetakes = intval($settings['max_retakes'] ?? 1);

                $score = $jawaban->score !== null ? floatval($jawaban->score) : null;
                $isQuiz = $survey?->is_quiz ?? false;

                $passed = null;
                $canRetake = false;

                if ($isQuiz && $score !== null && $passingScore > 0) {
                    $passed = $score >= $passingScore;
                    $canRetake = ! $passed && $allowRetake;
                }

                return [
                    'id' => $jawaban->id,
                    'survey' => $survey,
                    'submitted_at' => $jawaban->submitted_at,
                    'score' => $score,
                    'passing_score' => $passingScore,
                    'is_quiz' => $isQuiz,
                    'passed' => $passed,
                    'can_retake' => $canRetake,
                    'allow_retake' => $allowRetake,
                    'max_retakes' => $maxRetakes,
                ];
            })
            ->groupBy(fn ($item) => $item['survey']?->id)
            ->map(function ($group) {
                /** @var array<string, mixed> $latest */
                $latest = $group->first();

                // Count total attempts for this survey
                $totalAttempts = $group->count();

                // Find the best score attempt
                $bestScore = $group->max('score');

                // Check if any attempt passed
                $hasPassed = $group->contains('passed', true);

                /** @var array<string, mixed> $latestAttempt */
                $latestAttempt = $latest;

                return [
                    'survey' => $latest['survey'],
                    'latest_submitted_at' => $latest['submitted_at'],
                    'latest_score' => $latest['score'],
                    'best_score' => $bestScore,
                    'is_quiz' => $latest['is_quiz'],
                    'passing_score' => $latest['passing_score'],
                    'passed' => $hasPassed,
                    'can_retake' => $latest['can_retake'],
                    'allow_retake' => $latest['allow_retake'],
                    'max_retakes' => $latest['max_retakes'],
                    'total_attempts' => $totalAttempts,
                    'all_attempts' => $group->values(),
                ];
            })
            ->values()
            ->toArray();

        return [
            'submissions' => $submissions,
            'user' => $user,
        ];
    }
}
