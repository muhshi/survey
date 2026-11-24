<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <style>
        /* Sembunyikan Banner Lisensi & Watermark SurveyJS */
        .svc-creator__banner, 
        .sv-license-banner,
        .sv_watermark,
        .sd-root-modern__watermark,
        .sv-logo,
        .svc-creator__banner-content {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            overflow: hidden !important;
        }

        /* Standarisasi ukuran ikon SurveyJS */
        .survey-js-creator-container .sv-svg-icon {
            width: 1.5rem !important;
            height: 1.5rem !important;
        }

        .svc-side-bar__action-icon {
            width: 24px !important;
            height: 24px !important;
        }

        /* Proteksi mutlak agar SVG tidak membesar tak terkontrol */
        .survey-js-creator-container svg {
            max-width: 100%;
        }

        .survey-js-creator-container .json-status-icon,
        .survey-js-creator-container .json-status-icon svg {
            width: 1.125rem !important;
            height: 1.125rem !important;
            min-width: 1.125rem !important;
            min-height: 1.125rem !important;
            max-width: 1.125rem !important;
            max-height: 1.125rem !important;
            flex-shrink: 0 !important;
            display: inline-block !important;
        }

        /* Container survey creator */
        .survey-js-creator-container {
            border-radius: 0.75rem;
            background-color: transparent;
        }

        @if($field->isFullScreen())
            /* Maksimalkan area kerja saat fullscreen */
            .fi-sidebar {
                display: none !important;
            }

            .fi-main {
                margin-inline-start: 0 !important;
            }

            .fi-topbar {
                display: none !important;
            }

            .fi-header {
                padding-top: 0.75rem !important;
                padding-bottom: 0.75rem !important;
                margin-bottom: 0.75rem !important;
            }

            .fi-breadcrumbs {
                display: none !important;
            }
        @endif
    </style>

    <div
        x-data="surveyCreator({
            state: $wire.entangle('{{ $getStatePath() }}'),
        })"
        wire:ignore
        class="survey-js-creator-container"
        style="min-height: 750px;"
    >
        {{-- Mode Switcher & Status Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <x-filament::tabs label="Mode Perancangan">
                <x-filament::tabs.item 
                    alpine-active="mode === 'visual'"
                    x-on:click="switchMode('visual')"
                    icon="heroicon-o-pencil-square"
                >
                    Visual Builder
                </x-filament::tabs.item>
                
                <x-filament::tabs.item 
                    alpine-active="mode === 'json'"
                    x-on:click="switchMode('json')"
                    icon="heroicon-o-code-bracket"
                >
                    Edit JSON Schema
                </x-filament::tabs.item>
            </x-filament::tabs>

            {{-- Status Pill --}}
            <div class="flex items-center gap-2 text-xs">
                <template x-if="mode === 'visual'">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium border border-slate-200 dark:border-slate-700 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Visual Builder Aktif
                    </span>
                </template>
                <template x-if="mode === 'json'">
                    <span 
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-medium border shadow-xs"
                        :class="isValidSyntax ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'"
                    >
                        <span class="w-2 h-2 rounded-full" :class="isValidSyntax ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                        <span x-text="syntaxStatusText"></span>
                    </span>
                </template>
            </div>
        </div>

        <template x-if="loadError">
            <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2">
                <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="shrink-0 text-rose-600" style="width: 18px; height: 18px;">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
                <span x-text="'Gagal memuat visual builder: ' + loadErrorDetail"></span>
            </div>
        </template>

        {{-- Visual Builder View --}}
        <div x-show="mode === 'visual'" class="w-full">
            <div 
                x-ref="surveyCreator" 
                class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden"
                style="height: {{ $field->isFullScreen() ? 'calc(100vh - 170px)' : 'calc(100vh - 220px)' }}; min-height: 720px; width: 100%;"
            >
                <div class="flex items-center justify-center h-full text-slate-500 bg-slate-50/50 dark:bg-slate-900/50">
                    <div class="text-center p-6">
                        <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-sky-500 border-t-transparent mb-4"></div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Memuat Visual Survey Designer...</p>
                        <p class="text-xs text-slate-500 mt-1">Menyiapkan kanvas perancangan dan panel pengaturan</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- JSON Editor View --}}
        <div x-show="mode === 'json'" class="w-full" x-cloak>
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden">
                {{-- Editor Top Bar --}}
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 bg-slate-100 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-sky-600 dark:text-sky-400 shrink-0" style="width: 18px; height: 18px; min-width: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                        </svg>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Editor Skema JSON</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-mono hidden sm:inline">survey-schema.json</span>
                    </div>

                    {{-- Toolbar Action Buttons --}}
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            x-on:click="formatJson()"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-600 transition shadow-xs cursor-pointer"
                            title="Rapikan format JSON dengan indentasi 2 spasi"
                        >
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" class="shrink-0 text-slate-500 dark:text-slate-300" style="width: 14px; height: 14px; min-width: 14px;">
                                <path fill-rule="evenodd" d="M2.5 4A1.5 1.5 0 014 2.5h12A1.5 1.5 0 0117.5 4v1.5a.75.75 0 01-1.5 0V4a.5.5 0 00-.5-.5H4a.5.5 0 00-.5.5v12a.5.5 0 00.5.5h12a.5.5 0 00.5-.5v-1.5a.75.75 0 011.5 0V16a1.5 1.5 0 01-1.5 1.5H4A1.5 1.5 0 012.5 16V4z" clip-rule="evenodd" />
                                <path d="M13.28 7.22a.75.75 0 00-1.06 1.06L13.94 10l-1.72 1.72a.75.75 0 101.06 1.06l2.25-2.25a.75.75 0 000-1.06l-2.25-2.25zM6.72 12.78a.75.75 0 001.06-1.06L6.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L4.47 9.47a.75.75 0 000 1.06l2.25 2.25z" />
                            </svg>
                            Rapikan JSON
                        </button>

                        <button
                            type="button"
                            x-on:click="copyJson()"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-600 transition shadow-xs cursor-pointer"
                            title="Salin seluruh isi JSON ke clipboard"
                        >
                            <template x-if="!copied">
                                <span class="inline-flex items-center gap-1">
                                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" class="shrink-0 text-slate-500 dark:text-slate-300" style="width: 14px; height: 14px; min-width: 14px;">
                                        <path d="M7 3.5A1.5 1.5 0 018.5 2h3.879a1.5 1.5 0 011.06.44l3.122 3.12A1.5 1.5 0 0117 6.622V12.5a1.5 1.5 0 01-1.5 1.5h-1v-3.379a3 3 0 00-.879-2.121L10.5 5.379A3 3 0 008.379 4.5H7v-1z" />
                                        <path d="M4.5 6A1.5 1.5 0 003 7.5v9A1.5 1.5 0 004.5 18h7a1.5 1.5 0 001.5-1.5v-5.879a1.5 1.5 0 00-.44-1.06L9.44 6.439A1.5 1.5 0 008.379 6H4.5z" />
                                    </svg>
                                    Salin
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" class="shrink-0 text-emerald-600 dark:text-emerald-400" style="width: 14px; height: 14px; min-width: 14px;">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                    </svg>
                                    Tersalin!
                                </span>
                            </template>
                        </button>

                        <button
                            type="button"
                            x-on:click="resetJson()"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-600 transition shadow-xs cursor-pointer"
                            title="Reset ke skema yang tersimpan di database"
                        >
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" class="shrink-0 text-slate-500 dark:text-slate-300" style="width: 14px; height: 14px; min-width: 14px;">
                                <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.75A.75.75 0 003 12.828v4.482a.75.75 0 001.5 0v-2.227l.312.312a7 7 0 1010.5-8.31.75.75 0 00-1.06 1.06 5.5 5.5 0 011.06 3.279z" clip-rule="evenodd" />
                            </svg>
                            Reset
                        </button>
                    </div>
                </div>

                {{-- Code Editor Textarea --}}
                <div class="relative bg-slate-950">
                    <textarea
                        x-model="jsonText"
                        x-on:input="validateJson()"
                        x-on:keydown="handleTab($event)"
                        style="width: 100%; height: 560px; padding: 1.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-size: 0.85rem; line-height: 1.6; border: none; outline: none; resize: vertical; color: #f1f5f9; background-color: #0b1120;"
                        spellcheck="false"
                        placeholder="{ ... }"
                    ></textarea>
                </div>

                {{-- Alert Banners & Action Bar --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800">
                    {{-- Error Notification Banner --}}
                    <div 
                        x-show="jsonError" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="mb-3 p-3 rounded-lg bg-rose-50 border border-rose-200 dark:bg-rose-950/40 dark:border-rose-900 text-rose-800 dark:text-rose-200 text-xs flex items-start gap-2.5"
                        style="display: flex; align-items: flex-start;"
                        x-cloak
                    >
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="json-status-icon shrink-0 text-rose-600 dark:text-rose-400" style="width: 18px; height: 18px; min-width: 18px; min-height: 18px; flex-shrink: 0; margin-top: 1px;">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">
                            <p class="font-semibold text-rose-900 dark:text-rose-100">Format JSON Tidak Valid</p>
                            <p x-text="jsonError" class="mt-0.5 font-mono opacity-90 break-all"></p>
                        </div>
                    </div>

                    {{-- Success Notification Banner --}}
                    <div 
                        x-show="showSuccess" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="mb-3 p-3 rounded-lg bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-900 text-emerald-800 dark:text-emerald-200 text-xs flex items-center gap-2.5"
                        style="display: flex; align-items: center;"
                        x-cloak
                    >
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="json-status-icon shrink-0 text-emerald-600 dark:text-emerald-400" style="width: 18px; height: 18px; min-width: 18px; min-height: 18px; flex-shrink: 0;">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">JSON schema berhasil diterapkan ke perancang kuesioner.</span>
                    </div>

                    {{-- Actions Row --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <button
                                type="button"
                                x-on:click="applyJson()"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-sky-600 hover:bg-sky-500 active:bg-sky-700 rounded-lg shadow-xs transition focus:outline-none focus:ring-2 focus:ring-sky-500/50 cursor-pointer"
                            >
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" class="shrink-0" style="width: 16px; height: 16px; min-width: 16px;">
                                    <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.75A.75.75 0 003 12.828v4.482a.75.75 0 001.5 0v-2.227l.312.312a7 7 0 1010.5-8.31.75.75 0 00-1.06 1.06 5.5 5.5 0 011.06 3.279z" clip-rule="evenodd" />
                                </svg>
                                Terapkan JSON
                            </button>

                            <button
                                type="button"
                                x-on:click="switchMode('visual')"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-200/70 dark:hover:bg-slate-700 rounded-lg transition cursor-pointer"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0" style="width: 16px; height: 16px; min-width: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Lihat di Visual Builder
                            </button>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <span>Shortcut:</span>
                            <kbd class="px-1.5 py-0.5 text-[11px] bg-slate-200 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded text-slate-700 dark:text-slate-200 font-mono">Tab</kbd>
                            <span>indentasi •</span>
                            <kbd class="px-1.5 py-0.5 text-[11px] bg-slate-200 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded text-slate-700 dark:text-slate-200 font-mono">Ctrl+Enter</kbd>
                            <span>/</span>
                            <kbd class="px-1.5 py-0.5 text-[11px] bg-slate-200 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded text-slate-700 dark:text-slate-200 font-mono">Cmd+S</kbd>
                            <span>terapkan</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('surveyCreator', (config) => ({
                state: config.state,
                instance: null,
                mode: 'visual',
                jsonText: '',
                jsonError: '',
                showSuccess: false,
                loadError: false,
                loadErrorDetail: '',
                isInternalChange: false,
                isValidSyntax: true,
                syntaxStatusText: 'Sintaks Valid',
                copied: false,
                syncTimer: null,

                init() {
                    console.log('[SurveyJS] Start init...');
                    
                    try {
                        this.jsonText = (this.state && typeof this.state === 'object')
                            ? JSON.stringify(this.state, null, 2)
                            : '{}';
                        this.validateJson();
                    } catch (e) {
                        this.jsonText = '{}';
                        this.isValidSyntax = false;
                        this.syntaxStatusText = 'JSON Kosong';
                    }

                    // Auto-sync watcher
                    this.$watch('jsonText', (val) => {
                        if (this.mode !== 'json' || this.isInternalChange) return;
                        
                        this.validateJson();

                        // Debounce sync
                        clearTimeout(this.syncTimer);
                        this.syncTimer = setTimeout(() => {
                            if (this.isValidSyntax) {
                                this.applyJson(true);
                            }
                        }, 800);
                    });

                    // Tunggu SurveyCreator library ready
                    const checkReady = setInterval(() => {
                        if (typeof SurveyCreator !== 'undefined') {
                            clearInterval(checkReady);
                            this.setupCreator();
                        }
                    }, 400);
                },

                validateJson() {
                    if (!this.jsonText || this.jsonText.trim() === '') {
                        this.isValidSyntax = false;
                        this.syntaxStatusText = 'JSON Kosong';
                        return;
                    }
                    try {
                        JSON.parse(this.jsonText);
                        this.isValidSyntax = true;
                        this.syntaxStatusText = 'Sintaks Valid';
                        this.jsonError = '';
                    } catch (e) {
                        this.isValidSyntax = false;
                        this.syntaxStatusText = 'Format Error';
                    }
                },

                handleTab(e) {
                    if (e.key === 'Tab') {
                        e.preventDefault();
                        const target = e.target;
                        const start = target.selectionStart;
                        const end = target.selectionEnd;
                        this.jsonText = this.jsonText.substring(0, start) + '  ' + this.jsonText.substring(end);
                        this.$nextTick(() => {
                            target.selectionStart = target.selectionEnd = start + 2;
                        });
                    } else if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        this.applyJson();
                    } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                        e.preventDefault();
                        this.applyJson();
                    }
                },

                formatJson() {
                    try {
                        const parsed = JSON.parse(this.jsonText);
                        this.jsonText = JSON.stringify(parsed, null, 2);
                        this.isValidSyntax = true;
                        this.syntaxStatusText = 'Sintaks Valid';
                        this.jsonError = '';
                        if (window.FilamentNotification) {
                            new FilamentNotification()
                                .title('JSON Berhasil Dirapikan')
                                .success()
                                .send();
                        }
                    } catch (e) {
                        this.jsonError = 'Tidak dapat merapikan: ' + e.message;
                        if (window.FilamentNotification) {
                            new FilamentNotification()
                                .title('Gagal Merapikan JSON')
                                .body(e.message)
                                .danger()
                                .send();
                        }
                    }
                },

                copyJson() {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(this.jsonText).then(() => {
                            this.copied = true;
                            setTimeout(() => { this.copied = false; }, 2000);
                            if (window.FilamentNotification) {
                                new FilamentNotification()
                                    .title('JSON Disalin ke Clipboard')
                                    .success()
                                    .send();
                            }
                        }).catch(() => {
                            this.fallbackCopy();
                        });
                    } else {
                        this.fallbackCopy();
                    }
                },

                fallbackCopy() {
                    const el = document.createElement('textarea');
                    el.value = this.jsonText;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2000);
                },

                resetJson() {
                    try {
                        this.jsonText = (this.state && typeof this.state === 'object')
                            ? JSON.stringify(this.state, null, 2)
                            : '{}';
                        this.applyJson(true);
                        this.validateJson();
                        if (window.FilamentNotification) {
                            new FilamentNotification()
                                .title('Schema Direset')
                                .body('JSON dikembalikan ke data terakhir tersimpan.')
                                .info()
                                .send();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                setupCreator() {
                    try {
                        const options = {
                            showLogicTab: true,
                            showTranslationTab: true,
                            showThemeTab: true,
                            isAutoSave: true,
                            showJSONEditorTab: false,
                            allowEditSurveyTitle: true,
                            showSidebar: true,
                            showPropertyGrid: true,
                        };

                        const CreatorClass = typeof SurveyCreator.SurveyCreator !== 'undefined' 
                            ? SurveyCreator.SurveyCreator 
                            : SurveyCreator;

                        this.instance = new CreatorClass(options);
                        this.instance.isMobileView = false;
                        this.instance.sidebarLocation = "right";
                        
                        if (typeof this.instance.propertyGridNavigationMode !== 'undefined') {
                            this.instance.propertyGridNavigationMode = "buttons";
                        }
                        
                        if (SurveyCreator.localization) {
                            SurveyCreator.localization.currentLocale = "id";
                        }

                        if (this.state) {
                            this.instance.JSON = this.state;
                        }

                        // Event perubahan di visual builder
                        this.instance.onModified.add(() => {
                            if (this.mode === 'visual') {
                                this.isInternalChange = true;
                                const json = this.instance.JSON;
                                this.state = json;
                                this.jsonText = JSON.stringify(json, null, 2);
                                this.validateJson();
                                setTimeout(() => { this.isInternalChange = false; }, 100);
                            }
                        });

                        this.instance.render(this.$refs.surveyCreator);
                        console.log('[SurveyJS] UI Ready.');
                    } catch (e) {
                        this.loadError = true;
                        this.loadErrorDetail = e.message;
                        this.mode = 'json';
                    }
                },

                switchMode(newMode) {
                    if (newMode === 'json' && this.mode === 'visual') {
                        if (this.instance) {
                            this.jsonText = JSON.stringify(this.instance.JSON || {}, null, 2);
                        } else if (this.state) {
                            this.jsonText = JSON.stringify(this.state, null, 2);
                        }
                        this.validateJson();
                    } else if (newMode === 'visual' && this.mode === 'json') {
                        if (this.isValidSyntax) {
                            this.applyJson(true);
                        }
                    }
                    this.mode = newMode;
                },

                applyJson(silent = false) {
                    try {
                        const parsed = JSON.parse(this.jsonText);
                        this.isInternalChange = true;
                        this.state = parsed;
                        
                        if (this.instance) {
                            this.instance.JSON = parsed;
                        }
                        
                        this.jsonError = '';
                        this.isValidSyntax = true;
                        this.syntaxStatusText = 'Sintaks Valid';

                        if (!silent) {
                            this.showSuccess = true;
                            if (window.FilamentNotification) {
                                new FilamentNotification()
                                    .title('JSON Berhasil Diterapkan')
                                    .body('Perubahan skema kuesioner berhasil disinkronkan ke perancang visual.')
                                    .success()
                                    .send();
                            }
                            setTimeout(() => { this.showSuccess = false; }, 3500);
                        }
                        
                        setTimeout(() => { this.isInternalChange = false; }, 100);
                    } catch (e) {
                        this.isValidSyntax = false;
                        this.syntaxStatusText = 'Format Error';
                        if (!silent) {
                            this.jsonError = 'Format JSON Salah: ' + e.message;
                            this.showSuccess = false;
                            if (window.FilamentNotification) {
                                new FilamentNotification()
                                    .title('Format JSON Salah')
                                    .body(e.message)
                                    .danger()
                                    .send();
                            }
                        }
                    }
                }
            }));
        });
    </script>
</x-dynamic-component>
