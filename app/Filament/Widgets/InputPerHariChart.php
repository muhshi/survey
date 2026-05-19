<?php

namespace App\Filament\Widgets;

use App\Models\JawabanResponden;
use App\Models\Survey;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InputPerHariChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Jumlah Inputan per Hari';

    protected function getFilters(): ?array
    {
        $surveys = Survey::pluck('title', 'id')->toArray();

        return ['' => 'Semua Survei'] + $surveys;
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $query = JawabanResponden::query()
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subDays(14)->startOfDay());

        if (! empty($activeFilter)) {
            $query->where('survey_id', $activeFilter);
        }

        $data = $query->select(
            DB::raw('DATE(submitted_at) as date'),
            DB::raw('count(*) as count')
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $counts = [];

        $start = now()->subDays(14);
        $end = now();

        $dateRange = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateRange[$date->format('Y-m-d')] = 0;
        }

        foreach ($data as $row) {
            if (isset($dateRange[$row->date])) {
                $dateRange[$row->date] = $row->count;
            }
        }

        foreach ($dateRange as $date => $count) {
            $labels[] = Carbon::parse($date)->translatedFormat('d M');
            $counts[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Inputan',
                    'data' => $counts,
                    'fill' => 'start',
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
