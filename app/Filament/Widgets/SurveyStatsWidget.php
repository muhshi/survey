<?php

namespace App\Filament\Widgets;

use App\Models\JawabanResponden;
use App\Models\Survey;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SurveyStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $activeSurveys = Survey::where('is_active', true)->count();
        $totalSurveys = Survey::count();
        $totalResponses = JawabanResponden::count();
        $todayResponses = JawabanResponden::whereDate('submitted_at', today())->count();

        return [
            Stat::make('Survei Aktif', $activeSurveys.' / '.$totalSurveys)
                ->description('Aktif dari total survei')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-sky-50 dark:hover:bg-sky-900/10 transition-colors duration-300',
                ]),
            Stat::make('Total Jawaban', number_format($totalResponses))
                ->description('Seluruh jawaban masuk')
                ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-900/10 transition-colors duration-300',
                ]),
            Stat::make('Jawaban Hari Ini', number_format($todayResponses))
                ->description('Submit hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-colors duration-300',
                ]),
        ];
    }
}
