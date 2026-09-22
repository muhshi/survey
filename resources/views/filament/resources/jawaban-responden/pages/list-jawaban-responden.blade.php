<x-filament-panels::page>
    <div class="srv-wrap">

        <style>
            .srv-wrap {
                font-family: inherit;
                color: #1e293b;
                line-height: 1.5;
            }
            :is(.dark .srv-wrap) {
                color: #f1f5f9;
            }

            /* Global SVG constraints - prevents oversized icons */
            .srv-wrap svg {
                display: inline-block !important;
                vertical-align: middle !important;
                flex-shrink: 0 !important;
            }

            /* Top Toolbar (Filter + Tabs) */
            .srv-toolbar {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 16px 20px;
                margin-bottom: 20px;
                display: flex;
                flex-direction: column;
                gap: 16px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            }
            @media (min-width: 900px) {
                .srv-toolbar {
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                }
            }
            :is(.dark .srv-toolbar) {
                background: #0f172a;
                border-color: #1e293b;
            }

            /* Survey Filter Group */
            .srv-filter-group {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
                min-width: 0;
            }
            .srv-filter-label {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 12px;
                font-weight: 800;
                color: #0284c7;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                flex-shrink: 0;
            }
            :is(.dark .srv-filter-label) {
                color: #38bdf8;
            }
            .srv-select {
                width: 100%;
                max-width: 500px;
                padding: 9px 14px;
                font-size: 13px;
                font-weight: 600;
                border-radius: 10px;
                border: 1px solid #cbd5e1;
                background: #f8fafc;
                color: #0f172a;
                outline: none;
                transition: border-color 0.15s, box-shadow 0.15s;
                cursor: pointer;
            }
            .srv-select:focus {
                border-color: #0284c7;
                box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
            }
            :is(.dark .srv-select) {
                background: #1e293b;
                border-color: #334155;
                color: #f8fafc;
            }

            /* Segmented Tabs */
            .srv-tabs {
                display: inline-flex;
                align-items: center;
                background: #f1f5f9;
                padding: 4px;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                gap: 4px;
                flex-shrink: 0;
            }
            :is(.dark .srv-tabs) {
                background: #1e293b;
                border-color: #334155;
            }
            .srv-tab-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 700;
                border: none;
                cursor: pointer;
                transition: all 0.15s ease;
                background: transparent;
                color: #64748b;
            }
            .srv-tab-btn:hover {
                color: #0f172a;
            }
            :is(.dark .srv-tab-btn) {
                color: #94a3b8;
            }
            :is(.dark .srv-tab-btn:hover) {
                color: #f8fafc;
            }
            .srv-tab-btn.active {
                background: #ffffff;
                color: #0284c7;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            }
            :is(.dark .srv-tab-btn.active) {
                background: #0f172a;
                color: #38bdf8;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
            }
            .srv-tab-badge {
                font-size: 11px;
                font-weight: 800;
                padding: 2px 7px;
                border-radius: 12px;
                background: #e2e8f0;
                color: #475569;
            }
            .srv-tab-btn.active .srv-tab-badge {
                background: #e0f2fe;
                color: #0369a1;
            }
            :is(.dark .srv-tab-badge) {
                background: #334155;
                color: #cbd5e1;
            }
            :is(.dark .srv-tab-btn.active .srv-tab-badge) {
                background: rgba(3, 105, 161, 0.3);
                color: #38bdf8;
            }

            /* Stat Cards Grid */
            .srv-stat-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 14px;
                margin-bottom: 22px;
            }
            .srv-stat-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 18px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            }
            :is(.dark .srv-stat-card) {
                background: #0f172a;
                border-color: #1e293b;
            }
            .srv-stat-primary {
                background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
                color: #ffffff;
                border: none;
            }
            .srv-stat-label {
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #64748b;
                margin: 0 0 4px 0;
            }
            .srv-stat-primary .srv-stat-label {
                color: #e0f2fe;
            }
            :is(.dark .srv-stat-label) {
                color: #94a3b8;
            }
            .srv-stat-val {
                font-size: 26px;
                font-weight: 800;
                color: #0f172a;
                line-height: 1.1;
                margin: 0;
            }
            .srv-stat-primary .srv-stat-val {
                color: #ffffff;
            }
            :is(.dark .srv-stat-val) {
                color: #f8fafc;
            }
            .srv-stat-sub {
                font-size: 12px;
                color: #94a3b8;
                margin: 4px 0 0 0;
            }
            .srv-stat-primary .srv-stat-sub {
                color: #bae6fd;
            }
            .srv-icon-box {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .srv-icon-box-primary {
                background: rgba(255, 255, 255, 0.2);
                color: #ffffff;
            }
            .srv-icon-box-emerald {
                background: #ecfdf5;
                color: #059669;
            }
            :is(.dark .srv-icon-box-emerald) {
                background: rgba(5, 150, 105, 0.2);
                color: #34d399;
            }
            .srv-icon-box-sky {
                background: #f0f9ff;
                color: #0284c7;
            }
            :is(.dark .srv-icon-box-sky) {
                background: rgba(2, 132, 199, 0.2);
                color: #38bdf8;
            }
            .srv-icon-box-purple {
                background: #faf5ff;
                color: #7c3aed;
            }
            :is(.dark .srv-icon-box-purple) {
                background: rgba(124, 58, 237, 0.2);
                color: #a78bfa;
            }

            /* Question Cards (Google Forms Style) */
            .srv-question-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 22px 24px;
                margin-bottom: 20px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            }
            :is(.dark .srv-question-card) {
                background: #0f172a;
                border-color: #1e293b;
            }
            .srv-q-header {
                display: flex;
                flex-direction: column;
                gap: 10px;
                padding-bottom: 16px;
                border-bottom: 1px solid #f1f5f9;
            }
            @media (min-width: 640px) {
                .srv-q-header {
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                }
            }
            :is(.dark .srv-q-header) {
                border-color: #1e293b;
            }
            .srv-q-title-wrap {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                flex: 1;
                min-width: 0;
            }
            .srv-q-num {
                width: 28px;
                height: 28px;
                border-radius: 8px;
                background: #e0f2fe;
                color: #0369a1;
                font-size: 13px;
                font-weight: 800;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            :is(.dark .srv-q-num) {
                background: rgba(3, 105, 161, 0.3);
                color: #38bdf8;
            }
            .srv-q-title {
                font-size: 15px;
                font-weight: 700;
                color: #0f172a;
                margin: 0;
                line-height: 1.4;
            }
            :is(.dark .srv-q-title) {
                color: #f8fafc;
            }
            .srv-q-key {
                font-size: 11px;
                font-family: monospace;
                color: #94a3b8;
                margin-top: 2px;
            }
            .srv-badge-resp {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-size: 12px;
                font-weight: 700;
                background: #f0f9ff;
                color: #0369a1;
                border: 1px solid #bae6fd;
                padding: 4px 12px;
                border-radius: 20px;
                white-space: nowrap;
                flex-shrink: 0;
            }
            :is(.dark .srv-badge-resp) {
                background: rgba(3, 105, 161, 0.2);
                border-color: rgba(3, 105, 161, 0.4);
                color: #38bdf8;
            }

            /* Donut Chart Layout */
            .srv-donut-layout {
                display: flex;
                flex-direction: column;
                gap: 24px;
                padding-top: 18px;
            }
            @media (min-width: 768px) {
                .srv-donut-layout {
                    flex-direction: row;
                    align-items: center;
                }
            }
            .srv-donut-chart-box {
                width: 180px;
                height: 180px;
                position: relative;
                flex-shrink: 0;
                margin: 0 auto;
            }
            .srv-donut-center-text {
                position: absolute;
                inset: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                pointer-events: none;
            }
            .srv-donut-center-num {
                font-size: 24px;
                font-weight: 800;
                color: #0f172a;
                line-height: 1;
            }
            :is(.dark .srv-donut-center-num) {
                color: #f8fafc;
            }
            .srv-donut-center-lbl {
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                color: #94a3b8;
                margin-top: 3px;
            }
            .srv-legend-list {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 8px;
                min-width: 0;
            }
            .srv-legend-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 9px 14px;
                border-radius: 10px;
                background: #f8fafc;
                border: 1px solid #f1f5f9;
                gap: 12px;
            }
            :is(.dark .srv-legend-item) {
                background: #1e293b;
                border-color: #334155;
            }
            .srv-legend-left {
                display: flex;
                align-items: center;
                gap: 10px;
                min-width: 0;
                flex: 1;
            }
            .srv-color-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                flex-shrink: 0;
            }
            .srv-legend-text {
                font-size: 13px;
                font-weight: 600;
                color: #334155;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            :is(.dark .srv-legend-text) {
                color: #cbd5e1;
            }
            .srv-legend-right {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-shrink: 0;
            }
            .srv-legend-count {
                font-size: 13px;
                font-weight: 700;
                color: #0f172a;
            }
            :is(.dark .srv-legend-count) {
                color: #f8fafc;
            }
            .srv-legend-pct {
                font-size: 11px;
                font-weight: 800;
                padding: 3px 8px;
                border-radius: 6px;
                background: #ffffff;
                color: #475569;
                border: 1px solid #cbd5e1;
                min-width: 52px;
                text-align: center;
            }
            :is(.dark .srv-legend-pct) {
                background: #0f172a;
                border-color: #475569;
                color: #cbd5e1;
            }

            /* Horizontal Bar Chart */
            .srv-bar-layout {
                display: flex;
                flex-direction: column;
                gap: 12px;
                padding-top: 14px;
            }
            .srv-bar-row {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            .srv-bar-meta {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 13px;
                font-weight: 600;
                color: #334155;
            }
            :is(.dark .srv-bar-meta) {
                color: #cbd5e1;
            }
            .srv-bar-track {
                width: 100%;
                height: 12px;
                background: #f1f5f9;
                border-radius: 20px;
                overflow: hidden;
            }
            :is(.dark .srv-bar-track) {
                background: #1e293b;
            }
            .srv-bar-fill {
                height: 100%;
                border-radius: 20px;
                background: linear-gradient(90deg, #0284c7 0%, #38bdf8 100%);
                transition: width 0.4s ease;
            }

            /* Text Responses */
            .srv-text-layout {
                padding-top: 12px;
            }
            .srv-pill-wrap {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 14px;
            }
            .srv-pill-tag {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 5px 12px;
                border-radius: 10px;
                background: #f1f5f9;
                font-size: 12px;
                font-weight: 600;
                color: #1e293b;
                border: 1px solid #e2e8f0;
            }
            :is(.dark .srv-pill-tag) {
                background: #1e293b;
                border-color: #334155;
                color: #f1f5f9;
            }
            .srv-pill-cnt {
                font-size: 10px;
                font-weight: 800;
                background: #0284c7;
                color: #ffffff;
                padding: 1px 6px;
                border-radius: 5px;
            }
            .srv-sample-list {
                max-height: 220px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 6px;
                padding-right: 6px;
            }
            .srv-sample-item {
                padding: 9px 14px;
                border-radius: 8px;
                background: #f8fafc;
                border: 1px solid #f1f5f9;
                font-size: 12px;
                color: #334155;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            :is(.dark .srv-sample-item) {
                background: rgba(30, 41, 59, 0.5);
                border-color: #334155;
                color: #cbd5e1;
            }

            /* Empty Box */
            .srv-empty-box {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 48px 24px;
                text-align: center;
            }
            :is(.dark .srv-empty-box) {
                background: #0f172a;
                border-color: #1e293b;
            }
            .srv-table-container {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 16px;
            }
            :is(.dark .srv-table-container) {
                background: #0f172a;
                border-color: #1e293b;
            }
        </style>

        {{-- 1. TOP TOOLBAR: FILTER PER SURVEY & TABS --}}
        <div class="srv-toolbar">
            
            {{-- Left: Filter Per Survei --}}
            <div class="srv-filter-group">
                <div class="srv-filter-label">
                    <x-filament::icon icon="heroicon-o-document-chart-bar" style="width: 18px; height: 18px;" />
                    <span>Survei:</span>
                </div>
                <select 
                    wire:model.live="selectedSurveyId" 
                    class="srv-select"
                >
                    @foreach($this->surveyList as $s)
                        <option value="{{ $s->id }}">
                            {{ $s->title }} ({{ number_format($s->jawaban_respondens_count) }} respon) {{ $s->is_active ? '● Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Right: Modern Segmented Tabs (Grafik vs Tabel) --}}
            <div class="srv-tabs">
                <button 
                    wire:click="setTab('grafik')" 
                    type="button"
                    class="srv-tab-btn {{ $currentTab === 'grafik' ? 'active' : '' }}"
                >
                    <x-filament::icon icon="heroicon-m-chart-pie" style="width: 16px; height: 16px;" />
                    <span>Grafik Ringkasan</span>
                    @if($this->currentSurvey)
                        <span class="srv-tab-badge">
                            {{ count($this->questionStats['questions'] ?? []) }}
                        </span>
                    @endif
                </button>

                <button 
                    wire:click="setTab('tabel')" 
                    type="button"
                    class="srv-tab-btn {{ $currentTab === 'tabel' ? 'active' : '' }}"
                >
                    <x-filament::icon icon="heroicon-m-table-cells" style="width: 16px; height: 16px;" />
                    <span>Tabel Response</span>
                    @if($this->currentSurvey)
                        <span class="srv-tab-badge">
                            {{ number_format($this->questionStats['total_responses'] ?? 0) }}
                        </span>
                    @endif
                </button>
            </div>

        </div>

        {{-- 2. TAB CONTENT: GRAFIK RINGKASAN (GOOGLE FORM STYLE) --}}
        @if($currentTab === 'grafik')
            @php
                $statsData = $this->questionStats;
                $currentSurvey = $this->currentSurvey;
                $totalResponses = $statsData['total_responses'] ?? 0;
                $questions = $statsData['questions'] ?? [];
            @endphp

            @if($currentSurvey)
                {{-- Quick Metric Grid --}}
                <div class="srv-stat-grid">
                    
                    {{-- 1. Total Responden --}}
                    <div class="srv-stat-card srv-stat-primary">
                        <div>
                            <p class="srv-stat-label">Total Tanggapan</p>
                            <h3 class="srv-stat-val">{{ number_format($totalResponses) }}</h3>
                            <p class="srv-stat-sub">Responden telah mengisi</p>
                        </div>
                        <div class="srv-icon-box srv-icon-box-primary">
                            <x-filament::icon icon="heroicon-o-user-group" style="width: 24px; height: 24px;" />
                        </div>
                    </div>

                    {{-- 2. Status Survei --}}
                    <div class="srv-stat-card">
                        <div>
                            <p class="srv-stat-label">Status Survei</p>
                            <div style="margin-top: 6px;">
                                @if($currentSurvey->is_active)
                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; background: #ecfdf5; color: #059669;" class="dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981;"></span>
                                        Aktif Menerima Respon
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; background: #f1f5f9; color: #64748b;" class="dark:bg-gray-800 dark:text-gray-400">
                                        Ditutup
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="srv-icon-box srv-icon-box-emerald">
                            <x-filament::icon icon="heroicon-o-check-circle" style="width: 24px; height: 24px;" />
                        </div>
                    </div>

                    {{-- 3. Kategori & Mode --}}
                    <div class="srv-stat-card">
                        <div>
                            <p class="srv-stat-label">Kategori & Mode</p>
                            <h4 style="font-size: 16px; font-weight: 700; margin: 4px 0 2px 0;" class="text-gray-900 dark:text-white">
                                {{ $currentSurvey->kategori?->name ?? 'Umum' }}
                            </h4>
                            <p class="srv-stat-sub" style="text-transform: capitalize;">Mode: {{ $currentSurvey->mode->value }}</p>
                        </div>
                        <div class="srv-icon-box srv-icon-box-sky">
                            <x-filament::icon icon="heroicon-o-tag" style="width: 24px; height: 24px;" />
                        </div>
                    </div>

                    {{-- 4. Total Pertanyaan --}}
                    <div class="srv-stat-card">
                        <div>
                            <p class="srv-stat-label">Butir Pertanyaan</p>
                            <h3 class="srv-stat-val">{{ count($questions) }}</h3>
                            <p class="srv-stat-sub">Pertanyaan dalam skema</p>
                        </div>
                        <div class="srv-icon-box srv-icon-box-purple">
                            <x-filament::icon icon="heroicon-o-clipboard-document-list" style="width: 24px; height: 24px;" />
                        </div>
                    </div>

                </div>

                {{-- Question Cards (Google Forms Style) --}}
                <div>
                    @forelse($questions as $index => $q)
                        @php
                            $qNum = is_numeric($index) ? ($index + 1) : $loop->iteration;
                            $answeredCount = $q['total_answered'] ?? 0;
                            $answeredPct = $totalResponses > 0 ? round(($answeredCount / $totalResponses) * 100, 1) : 0;
                            $counts = $q['counts'] ?? [];
                            $isChart = $q['is_chart'] ?? false;
                            $chartType = $q['chart_type'] ?? 'bar';
                        @endphp

                        <div class="srv-question-card">
                            
                            {{-- Card Header --}}
                            <div class="srv-q-header">
                                <div class="srv-q-title-wrap">
                                    <span class="srv-q-num">{{ $qNum }}</span>
                                    <div>
                                        <h4 class="srv-q-title">{{ $q['title'] }}</h4>
                                        <div class="srv-q-key">Key: {{ $q['key'] }}</div>
                                    </div>
                                </div>
                                <div>
                                    <span class="srv-badge-resp">
                                        {{ number_format($answeredCount) }} tanggapan ({{ $answeredPct }}%)
                                    </span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div>
                                @if($answeredCount === 0)
                                    <p style="font-size: 13px; color: #94a3b8; font-style: italic; padding: 16px 0; text-align: center;">
                                        Belum ada tanggapan untuk pertanyaan ini.
                                    </p>
                                @elseif($isChart && $chartType === 'donut')
                                    {{-- A. DONUT CHART (Untuk pertanyaan dengan opsi 2 - 6) --}}
                                    @php
                                        $palette = [
                                            '#0284c7', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'
                                        ];
                                        $cumulativePct = 0;
                                        $chartSlices = [];
                                        $colorIndex = 0;
                                        foreach ($counts as $val => $cnt) {
                                            $pct = $answeredCount > 0 ? round(($cnt / $answeredCount) * 100, 1) : 0;
                                            $chartSlices[] = [
                                                'label' => $val,
                                                'count' => $cnt,
                                                'percentage' => $pct,
                                                'color' => $palette[$colorIndex % count($palette)],
                                                'dasharray' => "{$pct} " . (100 - $pct),
                                                'dashoffset' => 25 - $cumulativePct,
                                            ];
                                            $cumulativePct += $pct;
                                            $colorIndex++;
                                        }
                                    @endphp

                                    <div class="srv-donut-layout">
                                        {{-- SVG Vector Donut Chart --}}
                                        <div class="srv-donut-chart-box">
                                            <svg viewBox="0 0 42 42" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                                                <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#f1f5f9" stroke-width="6" class="dark:stroke-gray-800"></circle>
                                                @foreach($chartSlices as $slice)
                                                    <circle 
                                                        cx="21" 
                                                        cy="21" 
                                                        r="15.91549430918954" 
                                                        fill="transparent" 
                                                        stroke="{{ $slice['color'] }}" 
                                                        stroke-width="6" 
                                                        stroke-dasharray="{{ $slice['dasharray'] }}" 
                                                        stroke-dashoffset="{{ $slice['dashoffset'] }}"
                                                        style="transition: all 0.4s ease;"
                                                    ></circle>
                                                @endforeach
                                            </svg>
                                            <div class="srv-donut-center-text">
                                                <span class="srv-donut-center-num">{{ number_format($answeredCount) }}</span>
                                                <span class="srv-donut-center-lbl">Jawaban</span>
                                            </div>
                                        </div>

                                        {{-- Google Form Legend List --}}
                                        <div class="srv-legend-list">
                                            @foreach($chartSlices as $slice)
                                                <div class="srv-legend-item">
                                                    <div class="srv-legend-left">
                                                        <span class="srv-color-dot" style="background-color: {{ $slice['color'] }};"></span>
                                                        <span class="srv-legend-text" title="{{ $slice['label'] }}">{{ $slice['label'] }}</span>
                                                    </div>
                                                    <div class="srv-legend-right">
                                                        <span class="srv-legend-count">{{ number_format($slice['count']) }}</span>
                                                        <span class="srv-legend-pct">{{ $slice['percentage'] }}%</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                @elseif($isChart && $chartType === 'bar')
                                    {{-- B. HORIZONTAL BAR CHART (Untuk pertanyaan dengan banyak opsi seperti Kecamatan) --}}
                                    @php
                                        $topItems = array_slice($counts, 0, 15, true);
                                        $maxCount = max($counts);
                                    @endphp

                                    <div class="srv-bar-layout">
                                        @foreach($topItems as $val => $cnt)
                                            @php
                                                $pct = $answeredCount > 0 ? round(($cnt / $answeredCount) * 100, 1) : 0;
                                                $barWidth = $maxCount > 0 ? round(($cnt / $maxCount) * 100, 1) : 0;
                                            @endphp
                                            <div class="srv-bar-row">
                                                <div class="srv-bar-meta">
                                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 12px;">
                                                        {{ $val }}
                                                    </span>
                                                    <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                                        <strong style="color: #0284c7;" class="dark:text-sky-400">{{ number_format($cnt) }}</strong>
                                                        <span style="font-size: 11px; color: #94a3b8;">({{ $pct }}%)</span>
                                                    </div>
                                                </div>
                                                <div class="srv-bar-track">
                                                    <div class="srv-bar-fill" style="width: {{ max($barWidth, 3) }}%;"></div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @if(count($counts) > 15)
                                            <p style="font-size: 11px; color: #94a3b8; font-style: italic; text-align: right; margin-top: 6px;">
                                                + {{ count($counts) - 15 }} opsi lainnya...
                                            </p>
                                        @endif
                                    </div>

                                @else
                                    {{-- C. TEXT RESPONSES (Nama, NIK, Alamat, OPD) --}}
                                    @php
                                        $uniqueVals = count($counts);
                                        $topRecurring = array_filter(array_slice($counts, 0, 8, true), fn($c) => $c > 1);
                                    @endphp

                                    <div class="srv-text-layout">
                                        {{-- Jawaban Paling Sering Muncul --}}
                                        @if(!empty($topRecurring))
                                            <div style="margin-bottom: 14px;">
                                                <p style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin: 0 0 6px 0;">
                                                    Paling Sering Terisi:
                                                </p>
                                                <div class="srv-pill-wrap">
                                                    @foreach($topRecurring as $v => $c)
                                                        <span class="srv-pill-tag">
                                                            <span>{{ $v }}</span>
                                                            <span class="srv-pill-cnt">{{ $c }}x</span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Daftar Sampel Respon --}}
                                        <div>
                                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: #94a3b8; font-weight: 600; margin-bottom: 6px;">
                                                <span>Sampel Tanggapan ({{ min(count($q['recent_text']), 25) }} data):</span>
                                                <span>{{ $uniqueVals }} nilai unik</span>
                                            </div>
                                            <div class="srv-sample-list">
                                                @foreach(array_slice($q['recent_text'], 0, 25) as $txt)
                                                    <div class="srv-sample-item" title="{{ $txt }}">
                                                        {{ $txt }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                @endif
                            </div>

                        </div>
                    @empty
                        <div class="srv-empty-box">
                            <x-filament::icon icon="heroicon-o-document-magnifying-glass" style="width: 40px; height: 40px; color: #94a3b8; margin: 0 auto 12px auto;" />
                            <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0;" class="dark:text-white">
                                Tidak ada pertanyaan ditemukan
                            </h3>
                            <p style="font-size: 13px; color: #64748b; margin-top: 4px;">
                                Skema survei ini belum memiliki butir pertanyaan yang terdata.
                            </p>
                        </div>
                    @endforelse
                </div>

            @else
                <div class="srv-empty-box">
                    <x-filament::icon icon="heroicon-o-inbox" style="width: 40px; height: 40px; color: #94a3b8; margin: 0 auto 12px auto;" />
                    <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0;" class="dark:text-white">
                        Pilih Survei Terlebih Dahulu
                    </h3>
                    <p style="font-size: 13px; color: #64748b; margin-top: 4px;">
                        Silakan pilih survei pada dropdown di atas untuk melihat ringkasan grafik tanggapan.
                    </p>
                </div>
            @endif

        {{-- 3. TAB CONTENT: TABEL RESPONSE NATIVE FILAMENT --}}
        @else
            <div class="srv-table-container">
                {{ $this->table }}
            </div>
        @endif

    </div>
</x-filament-panels::page>
