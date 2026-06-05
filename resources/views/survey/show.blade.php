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
    
    /* Hide SurveyJS Watermark */
    .sv_watermark, .sv-logo, .sd-root-modern__watermark {
        display: none !important;
        visibility: hidden !important;
    }

    /* === QUIZ RESULT MODAL === */
    #quiz-result-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(6px);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    #quiz-result-modal.show {
        display: flex;
        animation: modalFadeIn 0.3s ease;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .quiz-result-card {
        background: #fff;
        border-radius: 24px;
        padding: 40px 36px;
        max-width: 460px;
        width: 100%;
        text-align: center;
        box-shadow: 0 25px 60px rgba(0,0,0,0.25);
        position: relative;
    }
    .quiz-result-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2.5rem;
    }
    .quiz-result-icon.passed {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    }
    .quiz-result-icon.failed {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
    }
    .quiz-result-score {
        font-size: 3.5rem;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 4px;
    }
    .quiz-result-score.passed { color: #059669; }
    .quiz-result-score.failed { color: #dc2626; }
    .quiz-result-label {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .quiz-result-label.passed { color: #065f46; }
    .quiz-result-label.failed { color: #991b1b; }
    .quiz-result-passing {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-bottom: 28px;
    }
    .quiz-result-actions {
        display: flex;
        gap: 12px;
        flex-direction: column;
    }
    .btn-retake {
        display: block;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px 24px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-retake:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99,102,241,0.35);
        color: white;
    }
    .btn-back-list {
        display: block;
        background: #f1f5f9;
        color: #475569;
        border: none;
        border-radius: 12px;
        padding: 14px 24px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-back-list:hover {
        background: #e2e8f0;
        color: #334155;
    }

    /* === MOBILE DROPDOWN POPUP FIX === */
    /* Force SurveyJS popup ABOVE the sticky navbar (z-index: 100) */
    .sv-popup {
        z-index: 10000 !important;
    }

    /* Hide navbar when dropdown popup is open (class toggled by JS) */
    body.sv-popup-open .navbar {
        display: none !important;
    }

    /* On mobile: make desktop-style dropdown popup fullscreen so it's not clipped */
    @media (max-width: 768px) {
        /* The popup overlay background */
        .sv-popup {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100% !important;
            height: 100% !important;
            z-index: 10000 !important;
            background: rgba(0, 0, 0, 0.5) !important;
        }
        /* The popup container (the white box with content) */
        .sv-popup__container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100% !important;
            height: 100% !important;
            max-height: 100vh !important;
            max-width: 100vw !important;
            border-radius: 0 !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }
        .sv-popup__body-content {
            max-height: 100vh !important;
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .sv-popup__scrolling-content {
            flex: 1 !important;
            overflow-y: auto !important;
        }
        /* Make the search/filter input prominent and always visible */
        .sv-list__filter {
            padding: 12px 12px !important;
            background: #fff !important;
            border-bottom: 2px solid #e2e8f0 !important;
            flex-shrink: 0 !important;
            display: flex !important;
        }
        .sv-list__filter input,
        .sv-list__input {
            font-size: 16px !important; /* Prevents iOS auto-zoom */
            padding: 12px 16px !important;
            border: 2px solid #6366f1 !important;
            border-radius: 10px !important;
            background: #f8fafc !important;
            width: 100% !important;
            -webkit-appearance: none !important;
            appearance: none !important;
        }
        .sv-list__filter input:focus,
        .sv-list__input:focus {
            outline: none !important;
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2) !important;
        }
    }

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

<!-- Quiz Result Modal (outside wrapper for correct z-index) -->
<div id="quiz-result-modal" role="dialog" aria-modal="true" aria-labelledby="quiz-result-title">
    <div class="quiz-result-card">
        <div class="quiz-result-icon" id="quiz-result-icon">🎉</div>
        <div class="quiz-result-score" id="quiz-result-score">0%</div>
        <div class="quiz-result-label" id="quiz-result-label">Selamat, Anda Lulus!</div>
        <div class="quiz-result-passing" id="quiz-result-passing">Nilai standar kelulusan: 0%</div>
        <div class="quiz-result-actions">
            <a id="btn-retake" href="{{ route('survey.show', $survey) }}" class="btn-retake" style="display:none;">
                🔄 Ulangi Kuis
            </a>
            <a href="{{ route('survey.index') }}" class="btn-back-list">
                ← Kembali ke Daftar Survei
            </a>
        </div>
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

            // Force SurveyJS to use desktop-style dropdown popup (with search input)
            // instead of the mobile overlay mode which hides the search field
            if (typeof Survey.IsTouch !== 'undefined') Survey.IsTouch = false;
            if (typeof Survey.IsMobile !== 'undefined') Survey.IsMobile = false;
            if (Survey.settings && Survey.settings.environment) {
                Survey.settings.environment.isMobile = false;
            }

            // Locale
            if (typeof Survey.localization !== 'undefined') {
                Survey.localization.currentLocale = "id";
            } else if (typeof Survey.surveyLocalization !== 'undefined') {
                Survey.surveyLocalization.currentLocale = "id";
            }

            const model = new Survey.Model(rawData);
            model.locale = "id";

            // Force search enabled on all dropdown questions
            model.getAllQuestions().forEach(q => {
                if (q.getType() === 'dropdown' || q.getType() === 'tagbox') {
                    q.searchEnabled = true;
                }
            });

            @if($survey->is_quiz)
                // Randomize questions order within pages
                model.questionsOrder = "random";
                // Randomize choices order for each question
                model.getAllQuestions().forEach(q => {
                    if (q.choicesOrder !== undefined) {
                        q.choicesOrder = "random";
                    }
                });
            @endif
            
            // USE DEFAULT V2 THEME
            model.applyTheme({
                "themeName": "defaultV2",
                "colorPalette": "light",
                "isPanelless": false
            });

            @if(isset($existingSubmission) && $existingSubmission)
                model.data = {!! json_encode($existingSubmission->payload ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
            @elseif(Auth::check())
                model.data = {
                    "nama_lengkap": "{{ Auth::user()->name }}",
                    "email_peserta": "{{ Auth::user()->email }}"
                };
            @endif

            @if($survey->mode === App\Enums\SurveyMode::Multi)
                model.completedHtml = `
                    <div style="text-align: center; padding: 40px;">
                        <h3 style="color: #1e1b4b; font-weight: bold; margin-bottom: 20px;">Berhasil Disimpan!</h3>
                        <p style="color: #64748b; margin-bottom: 30px;">Jawaban responden telah berhasil dikirim ke server.</p>
                        <button onclick="window.location.reload()" class="sd-btn sd-btn--action" style="background-color: #10b981 !important;">Isi Kuesioner Baru</button>
                    </div>
                `;
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
                    loading.classList.add('hidden');
                    if (data.success) {
                        // Show quiz result modal if this is a quiz
                        if (data.quiz) {
                            showQuizResult(data.quiz);
                        }
                        // Otherwise completedHtml from SurveyJS handles the display
                    } else {
                        fatal(data.message || "Proses simpan gagal.");
                    }
                })
                .catch(err => fatal("Koneksi gagal tersambung."));
            });

            function showQuizResult(quiz) {
                const modal   = document.getElementById('quiz-result-modal');
                const icon    = document.getElementById('quiz-result-icon');
                const score   = document.getElementById('quiz-result-score');
                const label   = document.getElementById('quiz-result-label');
                const passing = document.getElementById('quiz-result-passing');
                const btnRetake = document.getElementById('btn-retake');

                const passed = quiz.passed;
                const scoreVal = parseFloat(quiz.score).toFixed(1);
                const passingVal = parseFloat(quiz.passing_score).toFixed(0);

                icon.textContent   = passed ? '🎉' : '😔';
                icon.className     = 'quiz-result-icon ' + (passed ? 'passed' : 'failed');
                score.textContent  = scoreVal + '%';
                score.className    = 'quiz-result-score ' + (passed ? 'passed' : 'failed');
                label.className    = 'quiz-result-label ' + (passed ? 'passed' : 'failed');

                if (passed) {
                    label.textContent = '🎊 Selamat, Anda Lulus!';
                } else {
                    label.textContent = 'Belum Lulus — Coba Lagi!';
                }

                if (quiz.passing_score > 0) {
                    passing.textContent = 'Nilai standar kelulusan: ' + passingVal + '%';
                } else {
                    passing.textContent = 'Jawaban Anda telah berhasil disimpan.';
                }

                if (quiz.can_retake) {
                    btnRetake.style.display = 'block';
                    // Adjust button text: if already passed, offer to improve score
                    if (passed) {
                        btnRetake.innerHTML = '🚀 Coba Lagi (Tingkatkan Nilai)';
                        btnRetake.style.background = 'linear-gradient(135deg, #0ea5e9, #0284c7)';
                    } else {
                        btnRetake.innerHTML = '🔄 Ulangi Kuis';
                        btnRetake.style.background = 'linear-gradient(135deg, #6366f1, #4f46e5)';
                    }
                } else {
                    btnRetake.style.display = 'none';
                }

                // Hide the survey container
                document.getElementById('surveyElement').style.display = 'none';
                modal.classList.add('show');
            }

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

        // === MOBILE POPUP FIX: HIDE NAVBAR + UNLOCK KEYBOARD ===
        
        function unlockPopupInputs(root) {
            if (!root || !root.querySelectorAll) return;
            var inputs = root.querySelectorAll('input[type="text"], input:not([type])');
            inputs.forEach(function(input) {
                input.removeAttribute('readonly');
                if (input.getAttribute('inputmode') === 'none') {
                    input.setAttribute('inputmode', 'text');
                }
            });
        }

        function onPopupOpened(popupNode) {
            // Hide navbar so popup search field is fully visible
            document.body.classList.add('sv-popup-open');
            // Unlock inputs and auto-focus the search field
            unlockPopupInputs(popupNode);
            setTimeout(function() {
                unlockPopupInputs(popupNode);
                var searchInput = popupNode.querySelector('.sv-list__filter input, .sv-list__input, input[type="text"]');
                if (searchInput) {
                    searchInput.removeAttribute('readonly');
                    searchInput.setAttribute('inputmode', 'text');
                    searchInput.focus();
                }
            }, 150);
        }

        function checkPopupsClosed() {
            // If no visible popup exists, restore navbar
            var visiblePopup = document.querySelector('.sv-popup[style*="visibility: visible"], .sv-popup[style*="display: block"], .sv-popup:not([style*="display: none"]):not([style*="visibility: hidden"])');
            if (!visiblePopup) {
                document.body.classList.remove('sv-popup-open');
            }
        }

        // Watch for popup creation/removal and attribute changes
        var observer = new MutationObserver(function(mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var mutation = mutations[i];

                if (mutation.type === 'childList') {
                    // Popup added
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType !== 1) return;
                        if (node.classList && node.classList.contains('sv-popup')) {
                            onPopupOpened(node);
                        }
                        if (node.querySelector) {
                            var popup = node.querySelector('.sv-popup');
                            if (popup) onPopupOpened(popup);
                        }
                    });
                    // Popup removed
                    mutation.removedNodes.forEach(function(node) {
                        if (node.nodeType !== 1) return;
                        if ((node.classList && node.classList.contains('sv-popup')) || (node.querySelector && node.querySelector('.sv-popup'))) {
                            setTimeout(checkPopupsClosed, 100);
                        }
                    });
                }

                // Style/visibility change on popup (SurveyJS hides via style)
                if (mutation.type === 'attributes') {
                    var target = mutation.target;
                    // Check if popup visibility changed
                    if (target.classList && target.classList.contains('sv-popup')) {
                        var style = target.getAttribute('style') || '';
                        if (style.indexOf('visible') > -1 || style.indexOf('block') > -1) {
                            onPopupOpened(target);
                        } else {
                            setTimeout(checkPopupsClosed, 100);
                        }
                    }
                    // Unlock readonly/inputmode on search inputs
                    if (target.tagName === 'INPUT' && target.closest('.sv-popup')) {
                        if (target.hasAttribute('readonly')) target.removeAttribute('readonly');
                        if (target.getAttribute('inputmode') === 'none') target.setAttribute('inputmode', 'text');
                    }
                }
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['readonly', 'inputmode', 'style', 'class']
        });

        // Fallback: touch to unlock and focus
        document.addEventListener('touchstart', function(e) {
            var input = e.target;
            if (input.tagName === 'INPUT' && input.closest('.sv-popup')) {
                input.removeAttribute('readonly');
                input.setAttribute('inputmode', 'text');
                setTimeout(function() { input.focus(); }, 50);
            }
        }, { passive: true });

        // Fallback: periodic check for popup state
        setInterval(function() {
            var popup = document.querySelector('.sv-popup');
            if (popup) {
                var style = popup.getAttribute('style') || '';
                if (style.indexOf('hidden') === -1 && style.indexOf('none') === -1) {
                    document.body.classList.add('sv-popup-open');
                    unlockPopupInputs(popup);
                } else {
                    document.body.classList.remove('sv-popup-open');
                }
            } else {
                document.body.classList.remove('sv-popup-open');
            }
        }, 500);
    })();
</script>
@endsection
