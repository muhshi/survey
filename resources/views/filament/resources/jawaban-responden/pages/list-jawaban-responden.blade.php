<x-filament-panels::page>
    <div class="space-y-6">

        {{-- 1. TOP TOOLBAR: FILTER PER SURVEY & TABS --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                
                {{-- Left: Filter Per Survei --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
                    <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 shrink-0">
                        <x-heroicon-o-document-chart-bar class="w-6 h-6" />
                        <span class="font-bold text-sm text-gray-900 dark:text-white uppercase tracking-wider">Survei:</span>
                    </div>
                    <div class="flex-1 max-w-xl">
                        <select 
                            wire:model.live="selectedSurveyId" 
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white text-sm font-semibold rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 transition shadow-sm"
                        >
                            @foreach($this->surveyList as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->title }} ({{ number_format($s->jawaban_respondens_count) }} respon) {{ $s->is_active ? '● Aktif' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Right: Modern Segmented Tabs (Grafik vs Tabel) --}}
                <div class="flex items-center bg-gray-100 dark:bg-gray-800/80 p-1.5 rounded-xl border border-gray-200/80 dark:border-gray-700 shrink-0">
                    <button 
                        wire:click="setTab('grafik')" 
                        type="button"
                        class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $currentTab === 'grafik' ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                    >
                        <x-heroicon-m-chart-pie class="w-4 h-4" />
                        <span>Grafik Ringkasan</span>
                        @if($this->currentSurvey)
                            <span class="ml-1 text-xs px-2 py-0.5 rounded-full {{ $currentTab === 'grafik' ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                {{ count($this->questionStats['questions'] ?? []) }}
                            </span>
                        @endif
                    </button>

                    <button 
                        wire:click="setTab('tabel')" 
                        type="button"
                        class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $currentTab === 'tabel' ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                    >
                        <x-heroicon-m-table-cells class="w-4 h-4" />
                        <span>Tabel Response</span>
                        @if($this->currentSurvey)
                            <span class="ml-1 text-xs px-2 py-0.5 rounded-full {{ $currentTab === 'tabel' ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                {{ number_format($this->questionStats['total_responses'] ?? 0) }}
                            </span>
                        @endif
                    </button>
                </div>

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
                {{-- Quick Metric Banner --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-5 text-white shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-100">Total Tanggapan</p>
                            <h3 class="text-3xl font-extrabold mt-1">{{ number_format($totalResponses) }}</h3>
                            <p class="text-xs text-indigo-200 mt-1">Responden telah mengisi</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center">
                            <x-heroicon-o-user-group class="w-7 h-7 text-white" />
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status Survei</p>
                            <div class="mt-2">
                                @if($currentSurvey->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Aktif Menerima Respon
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                        Ditutup
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <x-heroicon-o-check-circle class="w-7 h-7" />
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kategori & Mode</p>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $currentSurvey->kategori?->name ?? 'Umum' }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">Mode: {{ $currentSurvey->mode->value }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-950/50 flex items-center justify-center text-sky-600 dark:text-sky-400">
                            <x-heroicon-o-tag class="w-7 h-7" />
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Butir Pertanyaan</p>
                            <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ count($questions) }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pertanyaan dalam skema</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <x-heroicon-o-clipboard-document-list class="w-7 h-7" />
                        </div>
                    </div>
                </div>

                {{-- Question Cards (Google Forms Style) --}}
                <div class="space-y-6 mt-4">
                    @forelse($questions as $index => $q)
                        @php
                            $qNum = is_numeric($index) ? ($index + 1) : $loop->iteration;
                            $answeredCount = $q['total_answered'] ?? 0;
                            $answeredPct = $totalResponses > 0 ? round(($answeredCount / $totalResponses) * 100, 1) : 0;
                            $counts = $q['counts'] ?? [];
                            $isChart = $q['is_chart'] ?? false;
                            $chartType = $q['chart_type'] ?? 'bar';
                        @endphp

                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm transition hover:shadow-md">
                            
                            {{-- Card Header --}}
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-gray-100 dark:border-gray-800 gap-2">
                                <div>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 text-xs font-extrabold flex items-center justify-center shrink-0">
                                            {{ $qNum }}
                                        </span>
                                        <span>{{ $q['title'] }}</span>
                                    </h4>
                                    <span class="text-xs font-mono text-gray-400 ml-8">Field key: {{ $q['key'] }}</span>
                                </div>
                                <div class="sm:text-right shrink-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                                        {{ number_format($answeredCount) }} tanggapan ({{ $answeredPct }}%)
                                    </span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div class="pt-6">
                                @if($answeredCount === 0)
                                    <p class="text-sm text-gray-400 italic py-4 text-center">Belum ada jawaban untuk pertanyaan ini.</p>
                                @elseif($isChart && $chartType === 'donut')
                                    {{-- A. DONUT CHART (Untuk pertanyaan dengan opsi 2 - 6) --}}
                                    @php
                                        $palette = [
                                            '#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6'
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

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                        {{-- SVG Vector Donut Chart --}}
                                        <div class="md:col-span-5 flex items-center justify-center p-4">
                                            <div class="relative w-48 h-48">
                                                <svg viewBox="0 0 42 42" class="w-full h-full transform -rotate-90">
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
                                                            class="transition-all duration-500 hover:opacity-85"
                                                        ></circle>
                                                    @endforeach
                                                </svg>
                                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                                    <span class="text-2xl font-black text-gray-900 dark:text-white">{{ number_format($answeredCount) }}</span>
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase">Jawaban</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Google Form Legend / Breakdown List --}}
                                        <div class="md:col-span-7 space-y-2.5">
                                            @foreach($chartSlices as $slice)
                                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50/80 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800/80 transition hover:bg-gray-100/70">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-sm" style="background-color: {{ $slice['color'] }};"></span>
                                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $slice['label'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-3 shrink-0 ml-4">
                                                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($slice['count']) }}</span>
                                                        <span class="text-xs font-extrabold px-2.5 py-1 rounded-lg bg-white dark:bg-gray-900 shadow-sm text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 min-w-[55px] text-center">
                                                            {{ $slice['percentage'] }}%
                                                        </span>
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

                                    <div class="space-y-3">
                                        @foreach($topItems as $val => $cnt)
                                            @php
                                                $pct = $answeredCount > 0 ? round(($cnt / $answeredCount) * 100, 1) : 0;
                                                $barWidth = $maxCount > 0 ? round(($cnt / $maxCount) * 100, 1) : 0;
                                            @endphp
                                            <div>
                                                <div class="flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                                    <span class="truncate pr-4 text-sm">{{ $val }}</span>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <span class="text-gray-900 dark:text-white font-extrabold text-sm">{{ number_format($cnt) }}</span>
                                                        <span class="text-gray-400 font-medium">({{ $pct }}%)</span>
                                                    </div>
                                                </div>
                                                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-3.5 overflow-hidden p-0.5">
                                                    <div 
                                                        class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-full rounded-full transition-all duration-700 shadow-sm" 
                                                        style="width: {{ max($barWidth, 3) }}%;"
                                                    ></div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @if(count($counts) > 15)
                                            <p class="text-xs text-gray-400 italic text-right mt-2">+ {{ count($counts) - 15 }} opsi lainnya...</p>
                                        @endif
                                    </div>

                                @else
                                    {{-- C. TEXT RESPONSES (Nama, NIK, Alamat, OPD) --}}
                                    @php
                                        $uniqueVals = count($counts);
                                        $topRecurring = array_filter(array_slice($counts, 0, 6, true), fn($c) => $c > 1);
                                    @endphp

                                    {{-- Jika ada isian teks yang sering berulang (seperti OPD) --}}
                                    @if(!empty($topRecurring))
                                        <div class="mb-4">
                                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Jawaban Paling Sering Muncul:</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($topRecurring as $v => $c)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                                        <span>{{ $v }}</span>
                                                        <span class="px-1.5 py-0.5 rounded-md bg-indigo-600 text-white font-bold text-[10px]">{{ $c }}x</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Scrollable List of Sample Responses --}}
                                    <div>
                                        <div class="flex items-center justify-between text-xs text-gray-400 mb-2 font-semibold">
                                            <span>Sampel Isian Responden ({{ min(count($q['recent_text']), 25) }} ditampilkan):</span>
                                            <span>{{ $uniqueVals }} nilai berbeda</span>
                                        </div>
                                        <div class="max-h-56 overflow-y-auto space-y-1.5 pr-2 custom-scrollbar">
                                            @foreach(array_slice($q['recent_text'], 0, 25) as $txt)
                                                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs font-medium text-gray-800 dark:text-gray-200 truncate">
                                                    {{ $txt }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                @endif
                            </div>

                        </div>
                    @empty
                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-12 text-center">
                            <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Tidak ada pertanyaan ditemukan</h3>
                            <p class="text-sm text-gray-500 mt-1">Skema survei ini belum memiliki butir pertanyaan yang terdata.</p>
                        </div>
                    @endforelse
                </div>

            @else
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-12 text-center">
                    <x-heroicon-o-inbox class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Pilih Survei Terlebih Dahulu</h3>
                    <p class="text-sm text-gray-500 mt-1">Silakan pilih survei pada dropdown di atas untuk melihat ringkasan grafik tanggapan.</p>
                </div>
            @endif

        {{-- 3. TAB CONTENT: TABEL RESPONSE NATIVE FILAMENT --}}
        @else
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm">
                {{ $this->table }}
            </div>
        @endif

    </div>
</x-filament-panels::page>
