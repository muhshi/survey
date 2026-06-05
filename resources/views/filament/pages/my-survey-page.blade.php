<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        {{-- Header Summary Cards --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div style="background: linear-gradient(135deg, #0ea5e9, #4f46e5); border-radius: 16px; padding: 20px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; font-weight: 900; line-height: 1;">{{ count($submissions) }}</div>
                <div style="margin-top: 6px; font-size: 0.85rem; font-weight: 600; opacity: 0.8;">Total Survey Diikuti</div>
            </div>
            <div style="background: linear-gradient(135deg, #10b981, #0d9488); border-radius: 16px; padding: 20px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; font-weight: 900; line-height: 1;">
                    {{ collect($submissions)->filter(fn($s) => $s['is_quiz'] && $s['passed'])->count() }}
                </div>
                <div style="margin-top: 6px; font-size: 0.85rem; font-weight: 600; opacity: 0.8;">Kuis Lulus</div>
            </div>
            <div style="background: linear-gradient(135deg, #f59e0b, #ea580c); border-radius: 16px; padding: 20px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; font-weight: 900; line-height: 1;">
                    {{ collect($submissions)->filter(fn($s) => $s['is_quiz'] && !$s['passed'] && $s['passed'] !== null)->count() }}
                </div>
                <div style="margin-top: 6px; font-size: 0.85rem; font-weight: 600; opacity: 0.8;">Kuis Belum Lulus</div>
            </div>
        </div>

        {{-- Survey List --}}
        @if(empty($submissions))
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 48px 24px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="width: 64px; height: 64px; margin: 0 auto 16px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg style="width: 32px; height: 32px; color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #374151; margin-bottom: 8px;">Belum Ada Survey</h3>
                <p style="font-size: 0.875rem; color: #94a3b8;">Anda belum mengisi survey apapun. Silakan kunjungi halaman survei.</p>
                <a href="{{ route('survey.index') }}" style="display: inline-flex; align-items: center; gap: 8px; margin-top: 16px; background: #0ea5e9; color: white; padding: 10px 20px; border-radius: 12px; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                    → Lihat Daftar Survei
                </a>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 16px;">
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

                        $borderColor = '#e5e7eb';
                        if ($isQuiz && $passed !== null) {
                            $borderColor = $passed ? '#a7f3d0' : '#fecaca';
                        }
                        $statusBg = '#f0f9ff'; $statusColor = '#0369a1'; $statusText = '✓ Sudah Diisi';
                        if ($isQuiz && $passed !== null) {
                            if ($passed) { $statusBg = '#d1fae5'; $statusColor = '#065f46'; $statusText = '✅ Lulus'; }
                            else { $statusBg = '#fee2e2'; $statusColor = '#991b1b'; $statusText = '❌ Belum Lulus'; }
                        }
                    @endphp

                    <div style="background: white; border: 1px solid {{ $borderColor }}; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: box-shadow 0.2s;">
                        {{-- Card Header --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px; gap: 16px; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8;">
                                        {{ $survey?->kategori?->name ?? 'Umum' }}
                                    </span>
                                    @if($isQuiz)
                                        <span style="background: #f3e8ff; color: #7c3aed; font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 999px;">🎯 Kuis</span>
                                    @endif
                                </div>
                                <h3 style="font-size: 1rem; font-weight: 700; color: #111827; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $survey?->title ?? 'Survei Dihapus' }}
                                </h3>
                                <p style="margin-top: 4px; font-size: 0.75rem; color: #94a3b8;">
                                    Terakhir diisi: {{ $item['latest_submitted_at']?->format('d M Y, H:i') ?? '-' }}
                                    @if($totalAttempts > 1)
                                        · <span style="font-weight: 600; color: #0ea5e9;">{{ $totalAttempts }}x percobaan</span>
                                    @endif
                                </p>
                            </div>

                            <div style="display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
                                {{-- Status Badge --}}
                                <span style="background: {{ $statusBg }}; color: {{ $statusColor }}; padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                                    {{ $statusText }}
                                </span>

                                {{-- Score (Quiz only) --}}
                                @if($isQuiz && $bestScore !== null)
                                    <div style="text-align: right;">
                                        <div style="font-size: 1.25rem; font-weight: 900; color: {{ $passed ? '#059669' : '#dc2626' }};">
                                            {{ number_format($bestScore, 1) }}%
                                        </div>
                                        <div style="font-size: 0.7rem; color: #94a3b8;">skor terbaik</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Progress bar for quiz --}}
                        @if($isQuiz && $passingScore > 0 && $bestScore !== null)
                            <div style="padding: 0 20px 8px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <span style="font-size: 0.7rem; color: #94a3b8;">Kemajuan menuju kelulusan</span>
                                    <span style="font-size: 0.7rem; font-weight: 600; color: #6b7280;">Target: {{ number_format($passingScore, 0) }}%</span>
                                </div>
                                @php
                                    $progress = min(100, ($bestScore / $passingScore) * 100);
                                    $barColor = $passed ? '#10b981' : '#f87171';
                                @endphp
                                <div style="height: 8px; width: 100%; background: #f1f5f9; border-radius: 999px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ $progress }}%; background: {{ $barColor }}; border-radius: 999px; transition: width 0.5s;"></div>
                                </div>
                            </div>
                        @endif

                        {{-- All attempts (collapsible) --}}
                        @if($isQuiz && $totalAttempts > 1)
                            <details style="border-top: 1px solid #f1f5f9;">
                                <summary style="cursor: pointer; padding: 10px 20px; font-size: 0.75rem; font-weight: 600; color: #0ea5e9; list-style: none; display: flex; align-items: center; gap: 4px;">
                                    ▸ Lihat semua percobaan ({{ $totalAttempts }}x)
                                </summary>
                                <div style="border-top: 1px solid #f1f5f9;">
                                    <table style="width: 100%; font-size: 0.75rem; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background: #f9fafb;">
                                                <th style="padding: 8px 20px; text-align: left; font-weight: 600; color: #6b7280;">#</th>
                                                <th style="padding: 8px 20px; text-align: left; font-weight: 600; color: #6b7280;">Waktu Submit</th>
                                                <th style="padding: 8px 20px; text-align: right; font-weight: 600; color: #6b7280;">Skor</th>
                                                <th style="padding: 8px 20px; text-align: center; font-weight: 600; color: #6b7280;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($allAttempts as $attempt)
                                                <tr style="border-top: 1px solid #f1f5f9;">
                                                    <td style="padding: 8px 20px; color: #94a3b8;">{{ $loop->iteration }}</td>
                                                    <td style="padding: 8px 20px; color: #4b5563;">
                                                        {{ $attempt['submitted_at']?->format('d M Y H:i') ?? '-' }}
                                                    </td>
                                                    <td style="padding: 8px 20px; text-align: right; font-weight: 700; color: {{ ($attempt['passed'] ?? false) ? '#059669' : '#dc2626' }};">
                                                        {{ $attempt['score'] !== null ? number_format($attempt['score'], 1) . '%' : '-' }}
                                                    </td>
                                                    <td style="padding: 8px 20px; text-align: center;">
                                                        @if($attempt['passed'] === true)
                                                            <span style="color: #059669; font-weight: 700;">Lulus</span>
                                                        @elseif($attempt['passed'] === false)
                                                            <span style="color: #dc2626;">Gagal</span>
                                                        @else
                                                            <span style="color: #94a3b8;">-</span>
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
                        <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding: 12px 20px;">
                            <span style="font-size: 0.75rem; color: #94a3b8;">
                                @if($isQuiz && $item['allow_retake'])
                                    Maks. {{ $item['max_retakes'] }}x percobaan
                                @elseif($survey)
                                    Mode: {{ $survey->mode?->getLabel() ?? '-' }}
                                @endif
                            </span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                @if($canRetake && $survey)
                                    @if($passed)
                                        <a href="{{ route('survey.show', $survey) }}"
                                           style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; padding: 6px 14px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                            🚀 Coba Lagi (Tingkatkan Nilai)
                                        </a>
                                    @else
                                        <a href="{{ route('survey.show', $survey) }}"
                                           style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; padding: 6px 14px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                            🔄 Ulangi Kuis
                                        </a>
                                    @endif
                                @elseif(!$isQuiz && $survey && $survey->mode?->value === 'editable')
                                    <a href="{{ route('survey.show', $survey) }}"
                                       style="display: inline-flex; align-items: center; gap: 6px; background: #0ea5e9; color: white; padding: 6px 14px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none;">
                                        ✏️ Edit Jawaban
                                    </a>
                                @elseif($survey)
                                    <a href="{{ route('survey.show', $survey) }}"
                                       style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; color: #475569; padding: 6px 14px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; text-decoration: none;">
                                        👁️ Lihat Survey
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <style>
        /* Responsive grid for mobile */
        @media (max-width: 640px) {
            div[style*="grid-template-columns: repeat(3"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</x-filament-panels::page>
