<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Header Summary --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 p-5 text-white shadow-lg">
                <div class="text-3xl font-black">{{ count($submissions) }}</div>
                <div class="mt-1 text-sm font-semibold opacity-80">Total Survey Diikuti</div>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-lg">
                <div class="text-3xl font-black">
                    {{ collect($submissions)->filter(fn($s) => $s['is_quiz'] && $s['passed'])->count() }}
                </div>
                <div class="mt-1 text-sm font-semibold opacity-80">Kuis Lulus</div>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-5 text-white shadow-lg">
                <div class="text-3xl font-black">
                    {{ collect($submissions)->filter(fn($s) => $s['is_quiz'] && !$s['passed'] && $s['passed'] !== null)->count() }}
                </div>
                <div class="mt-1 text-sm font-semibold opacity-80">Kuis Belum Lulus</div>
            </div>
        </div>

        {{-- Survey List --}}
        @if(empty($submissions))
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <x-heroicon-o-clipboard-document-list class="h-8 w-8 text-gray-400" />
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Belum Ada Survey</h3>
                <p class="text-sm text-gray-400">Anda belum mengisi survey apapun. Silakan kunjungi halaman survei.</p>
                <a href="{{ route('survey.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-sky-700 transition">
                    <x-heroicon-o-arrow-right class="h-4 w-4" />
                    Lihat Daftar Survei
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($submissions as $item)
                    @php
                        $survey = $item['survey'];
                        $isQuiz = $item['is_quiz'];
                        $passed = $item['passed'];
                        $bestScore = $item['best_score'];
                        $latestScore = $item['latest_score'];
                        $passingScore = $item['passing_score'];
                        $totalAttempts = $item['total_attempts'];
                        $canRetake = $item['can_retake'];
                        $allAttempts = $item['all_attempts'];
                        
                        // Status styling
                        if ($isQuiz && $passed !== null) {
                            $statusClass = $passed ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
                            $statusText = $passed ? '✅ Lulus' : '❌ Belum Lulus';
                            $cardBorder = $passed ? 'border-emerald-200 dark:border-emerald-800' : 'border-red-200 dark:border-red-800';
                        } else {
                            $statusClass = 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300';
                            $statusText = '✓ Sudah Diisi';
                            $cardBorder = 'border-gray-200 dark:border-gray-700';
                        }
                    @endphp

                    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm transition hover:shadow-md dark:bg-gray-800 {{ $cardBorder }}">
                        {{-- Card Header --}}
                        <div class="flex flex-col items-start justify-between gap-4 p-5 sm:flex-row sm:items-center">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                        {{ $survey?->kategori?->name ?? 'Umum' }}
                                    </span>
                                    @if($isQuiz)
                                        <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-bold text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">🎯 Kuis</span>
                                    @endif
                                </div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white truncate">
                                    {{ $survey?->title ?? 'Survei Dihapus' }}
                                </h3>
                                <p class="mt-1 text-xs text-gray-400">
                                    Terakhir diisi: {{ $item['latest_submitted_at']?->format('d M Y, H:i') ?? '-' }}
                                    @if($totalAttempts > 1)
                                        · <span class="font-semibold text-sky-600">{{ $totalAttempts }}x percobaan</span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-shrink-0 items-center gap-3">
                                {{-- Status Badge --}}
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>

                                {{-- Score Badge (Quiz only) --}}
                                @if($isQuiz && $bestScore !== null)
                                    <div class="text-right">
                                        <div class="text-xl font-black {{ $passed ? 'text-emerald-600' : 'text-red-500' }}">
                                            {{ number_format($bestScore, 1) }}%
                                        </div>
                                        <div class="text-xs text-gray-400">skor terbaik</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Progress bar for quiz --}}
                        @if($isQuiz && $passingScore > 0 && $bestScore !== null)
                            <div class="px-5 pb-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-400">Kemajuan menuju kelulusan</span>
                                    <span class="text-xs font-semibold text-gray-500">Target: {{ number_format($passingScore, 0) }}%</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                    @php
                                        $progress = min(100, ($bestScore / $passingScore) * 100);
                                        $barColor = $passed ? 'bg-emerald-500' : 'bg-red-400';
                                    @endphp
                                    <div class="h-full rounded-full transition-all {{ $barColor }}" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        @endif

                        {{-- All attempts (collapsible, shown if > 1) --}}
                        @if($isQuiz && $totalAttempts > 1)
                            <details class="group">
                                <summary class="cursor-pointer select-none px-5 py-3 text-xs font-semibold text-sky-600 hover:text-sky-700 dark:text-sky-400 list-none flex items-center gap-1">
                                    <x-heroicon-o-chevron-right class="h-3 w-3 transition group-open:rotate-90" />
                                    Lihat semua percobaan ({{ $totalAttempts }}x)
                                </summary>
                                <div class="border-t border-gray-100 dark:border-gray-700">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                                            <tr>
                                                <th class="px-5 py-2 text-left font-semibold text-gray-500 dark:text-gray-400">#</th>
                                                <th class="px-5 py-2 text-left font-semibold text-gray-500 dark:text-gray-400">Waktu Submit</th>
                                                <th class="px-5 py-2 text-right font-semibold text-gray-500 dark:text-gray-400">Skor</th>
                                                <th class="px-5 py-2 text-center font-semibold text-gray-500 dark:text-gray-400">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($allAttempts as $attempt)
                                                <tr class="border-t border-gray-100 dark:border-gray-700/50">
                                                    <td class="px-5 py-2 text-gray-400">{{ $loop->iteration }}</td>
                                                    <td class="px-5 py-2 text-gray-600 dark:text-gray-300">
                                                        {{ $attempt['submitted_at']?->format('d M Y H:i') ?? '-' }}
                                                    </td>
                                                    <td class="px-5 py-2 text-right font-bold {{ ($attempt['passed'] ?? false) ? 'text-emerald-600' : 'text-red-500' }}">
                                                        {{ $attempt['score'] !== null ? number_format($attempt['score'], 1) . '%' : '-' }}
                                                    </td>
                                                    <td class="px-5 py-2 text-center">
                                                        @if($attempt['passed'] === true)
                                                            <span class="text-emerald-600 font-bold">Lulus</span>
                                                        @elseif($attempt['passed'] === false)
                                                            <span class="text-red-500">Gagal</span>
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        @endif

                        {{-- Footer Actions --}}
                        <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3 dark:border-gray-700">
                            <span class="text-xs text-gray-400">
                                @if($isQuiz && $item['allow_retake'])
                                    Maks. {{ $item['max_retakes'] }}x percobaan
                                @elseif($survey)
                                    Mode: {{ $survey->mode?->getLabel() ?? '-' }}
                                @endif
                            </span>
                            <div class="flex items-center gap-2">
                                @if($canRetake && $survey)
                                    <a href="{{ route('survey.show', $survey) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition">
                                        <x-heroicon-o-arrow-path class="h-3.5 w-3.5" />
                                        Ulangi Kuis
                                    </a>
                                @elseif(!$isQuiz && $survey && $survey->mode?->value === 'editable')
                                    <a href="{{ route('survey.show', $survey) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-sky-700 transition">
                                        <x-heroicon-o-pencil class="h-3.5 w-3.5" />
                                        Edit Jawaban
                                    </a>
                                @elseif($survey)
                                    <a href="{{ route('survey.show', $survey) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200 transition dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                        <x-heroicon-o-eye class="h-3.5 w-3.5" />
                                        Lihat Survey
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-filament-panels::page>
