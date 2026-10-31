<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Terima Pengisian - {{ $survey->title }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            padding: 32px 16px;
        }

        .heading-font {
            font-family: 'Outfit', sans-serif;
        }

        .receipt-card {
            max-width: 680px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            position: relative;
        }

        .receipt-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #0284c7, #38bdf8, #6366f1);
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Action Toolbar (Hidden in Print) -->
    <div class="max-w-[680px] mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('survey.show', $survey) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Kuesioner
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-bold shadow-sm transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Main Receipt Document -->
    <div class="receipt-card p-8 sm:p-10">

        <!-- Header BPS -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600 font-extrabold text-xl">
                    📊
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 tracking-tight leading-tight heading-font">
                        BADAN PUSAT STATISTIK
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">Kabupaten Demak • Layanan Survei & Kuis Digital</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    ✓ TERVERIFIKASI
                </span>
                <p class="text-[11px] font-mono text-slate-400 mt-1">{{ $regNumber }}</p>
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-black text-slate-900 heading-font mb-1">
                Tanda Terima Pengisian
            </h2>
            <p class="text-xs text-slate-500">
                Bukti sah bahwa responden telah menyelesaikan pengisian kuesioner digital berikut:
            </p>
        </div>

        <!-- Survey Info Box -->
        <div class="bg-slate-50 border border-slate-200/70 rounded-xl p-5 mb-6">
            <div class="text-xs font-bold text-sky-600 uppercase tracking-wider mb-1">
                {{ $survey->kategori?->nama ?? 'Survei Digital' }}
            </div>
            <h3 class="text-lg font-bold text-slate-900 heading-font leading-snug">
                {{ $survey->title }}
            </h3>
            @if($survey->description)
                <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                    {{ $survey->description }}
                </p>
            @endif
        </div>

        <!-- Grid: Detail Responden & Verifikasi -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            
            <div class="sm:col-span-2 space-y-3.5">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Nama Responden</span>
                    <span class="text-sm font-bold text-slate-800">{{ $respondentName }}</span>
                </div>

                @if($opdOrInstansi)
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Unit Kerja / Instansi</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $opdOrInstansi }}</span>
                </div>
                @endif

                @if($emailOrContact)
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Kontak / Email</span>
                    <span class="text-sm text-slate-700 font-mono">{{ $emailOrContact }}</span>
                </div>
                @endif

                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Waktu Submit</span>
                    <span class="text-sm font-medium text-slate-700">
                        {{ $submission->submitted_at ? $submission->submitted_at->translatedFormat('d F Y - H:i:s') : '-' }} WIB
                    </span>
                </div>

                @if($isQuiz)
                <div class="pt-2 border-t border-slate-100 flex items-center gap-4">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Skor Kuis</span>
                        <span class="text-lg font-black {{ $passed ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ number_format((float) $score, 1) }}%
                        </span>
                    </div>
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Status Kelulusan</span>
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $passed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $passed ? 'LULUS' : 'BELUM LULUS' }}
                        </span>
                    </div>
                </div>
                @endif
            </div>

            <!-- QR Code Section -->
            <div class="flex flex-col items-center justify-center p-4 bg-slate-50 border border-slate-200/60 rounded-xl text-center">
                <img src="{{ $qrCode }}" alt="QR Code Verifikasi" class="w-32 h-32 rounded-lg bg-white p-1 border border-slate-200 shadow-xs mb-2">
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Scan untuk Verifikasi</span>
                <span class="text-[9px] text-slate-400 mt-0.5">Otentikasi Sistem BPS</span>
            </div>

        </div>

        <!-- Footer / Watermark -->
        <div class="pt-6 border-t border-slate-100 text-center text-[11px] text-slate-400">
            <p class="font-medium">Dokumen ini diterbitkan secara elektronik oleh Sistem Manajemen Survei BPS Kabupaten Demak.</p>
            <p class="mt-1 font-mono text-[10px]">ID Pengisian: #{{ $submission->id }} • Hash: {{ substr(md5($submission->id . $submission->created_at), 0, 12) }}</p>
        </div>

    </div>

</body>
</html>
