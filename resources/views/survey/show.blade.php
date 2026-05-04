@extends('layouts.survey')

@section('title', $survey->title)

@section('head')
<style>
    /* CLEAN LIGHT THEME - FINAL ISOLATION */
    #survey-runner-wrapper {
        background-color: #6366f1; /* Vibrant Indigo */
        background-image: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        min-height: 100vh;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
        padding: 40px 20px;
        z-index: 10;
        color: #1e1b4b; /* Dark Indigo Text */
    }

    .survey-centered-container {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
    }

    /* HEADER */
    .simple-header {
        text-align: center;
        color: white;
        margin-bottom: 24px; /* Reduced from 40px */
    }
    .simple-header h2 {
        font-size: 1.125rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .simple-header p {
        font-size: 0.8125rem;
        opacity: 0.9;
        margin-top: 4px;
    }

    /* WHITE CARDS */
    .white-card {
        background: #ffffff !important;
        border-radius: 16px !important; /* Matches others */
        padding: 32px !important; /* Smaller padding */
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
        margin-bottom: 16px !important; /* Matches others */
        color: #1e1b4b !important;
    }

    .title-card h1 {
        font-size: 1.875rem;
        font-weight: 800;
        color: #1e1b4b;
        margin: 0 0 16px 0;
        text-transform: capitalize;
    }
    .badge-category {
        background: #dcfce7;
        color: #166534;
        font-size: 0.7rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    .meta-text {
        font-size: 0.8125rem;
        color: #94a3b8;
        font-weight: 500;
    }

    /* SURVEYJS DEFAULT V2 CUSTOMIZATION */
    .sd-root-modern, .sd-root-modern--full-container, .sd-container-modern {
        background: transparent !important;
    }
    
    /* SINGLE COHESIVE CARD DESIGN (UNIFIED) */
    #surveyElement {
        background-color: #ffffff !important;
        border-radius: 16px !important;
        padding: 32px !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05) !important;
        margin-top: 8px;
    }

    /* Remove individual card styles to eliminate gaps */
    .sd-element, .sd-question, .sd-panel, .sd-page__title, .sd-page__description {
        background-color: transparent !important;
        border-radius: 0 !important;
        padding: 12px 0 !important; /* Thinner padding */
        margin-bottom: 0 !important; /* No gaps */
        box-shadow: none !important;
        border: none !important;
        color: #1e1b4b !important;
        display: block !important;
    }

    /* Subtle divider between questions */
    .sd-element:not(:last-child) {
        border-bottom: 1px solid #f1f5f9 !important;
        padding-bottom: 24px !important;
        margin-bottom: 24px !important;
    }

    /* Section Titles (Page Titles) inside the card */
    .sd-page__title {
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        color: #1e1b4b !important;
        padding-top: 0 !important;
        margin-bottom: 8px !important;
    }
    .sd-page__description {
        font-size: 0.9375rem !important;
        color: #64748b !important;
        padding-top: 0 !important;
        margin-bottom: 24px !important;
    }

    /* Number circles */
    .sd-question__number {
        background-color: #3b82f6 !important;
        color: #fff !important;
        min-width: 28px !important;
        height: 28px !important;
        font-size: 0.875rem !important;
    }

    /* Text contrast for invisible elements */
    .sd-question__title, .sd-title { color: #1e1b4b !important; }
    .sd-description { color: #64748b !important; }
    
    /* Progress bar */
    .sd-progress {
        background-color: rgba(255, 255, 255, 0.2) !important;
        height: 12px !important;
        border-radius: 6px !important;
        margin-bottom: 32px !important;
    }
    .sd-progress__bar {
        background-color: #10b981 !important; /* Success Green */
        border-radius: 6px !important;
    }

    /* Footer / Progress text */
    .sd-body__progress-text {
        color: white !important;
        font-weight: 700 !important;
        opacity: 0.9 !important;
        text-align: right !important;
    }

    /* Inputs refined */
    .sd-input, .sd-dropdown {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 14px 18px !important;
        font-size: 1rem !important;
        color: #1e293b !important;
    }
    .sd-input::placeholder { color: #94a3b8; }
    
    /* Navigation */
    .sd-navigation {
        display: flex !important;
        padding: 20px 0 !important;
    }
    .sd-btn {
        border-radius: 10px !important;
        padding: 12px 28px !important;
        font-weight: 700 !important;
        font-family: inherit !important;
        transition: all 0.2s !important;
    }
    .sd-btn--action {
        background-color: #2563eb !important;
        color: #fff !important;
    }
    .sd-btn--action:hover {
        background-color: #1d4ed8 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    /* Fixed header to avoid scrolling */
    .navbar { box-shadow: none !important; }

</style>
@endsection

@section('content')
<div id="survey-runner-wrapper">
    <div class="survey-centered-container">
        
        <!-- Simple Header -->
        <div class="simple-header">
            <h2>📋 Kuesioner Digital BPS</h2>
            <p>Badan Pusat Statistik Kabupaten Demak</p>
        </div>

        <!-- Title Card -->
        <div class="white-card title-card">
            <h1>{{ $survey->title }}</h1>
            <hr class="border-slate-100 my-6">
            <div class="flex items-center justify-between">
                <span class="badge-category">{{ $survey->kategori?->nama ?? 'Umum' }}</span>
                <div class="meta-text flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Diterbitkan: {{ $survey->created_at?->format('d M Y') ?? date('d M Y') }}</span>
                </div>
            </div>
            @if($survey->description)
                <div class="mt-6 text-slate-500 text-[0.9375rem] leading-relaxed">
                    {{ $survey->description }}
                </div>
            @endif
        </div>

        <!-- Status Area -->
        <div id="status-card">
            <div id="loading-box" class="py-10 text-center text-white">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-white/20 border-t-white mb-4"></div>
                <p class="font-bold opacity-80">Menyiapkan kuesioner...</p>
            </div>

            <div id="error-box" class="hidden white-card text-center py-12">
                <h2 class="text-red-600 font-bold text-xl mb-4">Akses Gagal</h2>
                <p id="error-msg" class="text-slate-500 text-sm mb-6 font-mono"></p>
                <button onclick="window.location.reload()" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 transition">Coba Lagi</button>
            </div>
        </div>

        <!-- SurveyJS Area -->
        <div id="surveyElement"></div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/survey-core@1.12.14/survey.core.min.js"></script>
<script src="https://unpkg.com/survey-js-ui@1.12.14/survey-js-ui.min.js"></script>
<link href="https://unpkg.com/survey-core@1.12.14/defaultV2.min.css" type="text/css" rel="stylesheet">
<script src="https://unpkg.com/survey-core@1.12.14/i18n/indonesian.js"></script>

<script>
    (function() {
        const loading = document.getElementById('loading-box');
        const errorBox = document.getElementById('error-box');
        const errorMsg = document.getElementById('error-msg');

        function fatal(txt) {
            loading.classList.add('hidden');
            errorBox.classList.remove('hidden');
            errorMsg.innerText = txt;
        }

        window.onerror = function(msg, url, line) {
            fatal("JS Error: " + msg + " at " + line);
            return false;
        };

        try {
            let rawData = {!! json_encode($survey->schema ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
            
            if (!rawData || !rawData.pages || rawData.pages.length === 0) {
                return fatal("Data pertanyaan kuesioner tidak ditemukan.");
            }

            if (typeof Survey === 'undefined') return fatal("Modul integrasi gagal dimuat.");

            // Locale
            if (typeof Survey.localization !== 'undefined') {
                Survey.localization.currentLocale = "id";
            } else if (typeof Survey.surveyLocalization !== 'undefined') {
                Survey.surveyLocalization.currentLocale = "id";
            }

            const model = new Survey.Model(rawData);
            model.locale = "id";
            
            // USE DEFAULT V2 THEME
            model.applyTheme({
                "themeName": "defaultV2",
                "colorPalette": "light",
                "isPanelless": false
            });

            @if(isset($existingSubmission) && $existingSubmission)
                model.data = {!! json_encode($existingSubmission->payload ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
            @endif

            model.completeText = "Kirim Jawaban";
            model.pageNextText = "Lanjut";
            model.pagePrevText = "Kembali";

            model.onComplete.add(function(sender) {
                loading.classList.remove('hidden');
                loading.querySelector('p').innerText = 'Mengirim data...';
                
                fetch("{{ route('survey.submit', $survey) }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" },
                    body: JSON.stringify({ payload: sender.data })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        loading.classList.add('hidden');
                    } else {
                        fatal(data.message || "Proses simpan gagal.");
                    }
                })
                .catch(err => fatal("Koneksi gagal tersambung."));
            });

            const container = document.getElementById("surveyElement");
            if (container) {
                model.render(container);
                loading.classList.add('hidden');
            } else {
                fatal("Kontainer tampilan tidak tersedia.");
            }

        } catch (e) {
            fatal("Masalah Sistem: " + e.message);
        }
    })();
</script>
@endsection
