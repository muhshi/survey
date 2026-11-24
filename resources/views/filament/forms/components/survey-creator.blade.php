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

        /* Container survey creator */
        .survey-js-creator-container {
            border-radius: 0.75rem;
            background-color: transparent;
            width: 100%;
        }

        /* ==============================================================
           Filament Survey Creator (FSC) Scoped Styling
           Menjamin tampilan konsisten & rapi tanpa dependensi class utility
           ============================================================== */
        .fsc-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: 100%;
        }

        .fsc-top-bar {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 0.75rem !important;
            margin-bottom: 0.25rem !important;
            width: 100% !important;
        }

        .fsc-card {
            border-radius: 0.75rem !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            width: 100% !important;
        }

        :is(.dark) .fsc-card {
            border-color: #1e293b !important;
            background-color: #0f172a !important;
        }

        .fsc-editor-toolbar {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 0.75rem !important;
            padding: 0.625rem 1rem !important;
            background-color: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        :is(.dark) .fsc-editor-toolbar {
            background-color: #1e293b !important;
            border-bottom-color: #334155 !important;
        }

        .fsc-editor-title {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            color: #1e293b !important;
        }

        :is(.dark) .fsc-editor-title {
            color: #f1f5f9 !important;
        }

        .fsc-editor-subtitle {
            font-size: 0.75rem !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            color: #64748b !important;
            margin-left: 0.25rem !important;
            font-weight: normal !important;
        }

        :is(.dark) .fsc-editor-subtitle {
            color: #94a3b8 !important;
        }

        .fsc-toolbar-actions {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
        }

        .fsc-editor-body {
            position: relative !important;
            background-color: #090d16 !important;
            width: 100% !important;
        }

        .fsc-textarea {
            display: block !important;
            width: 100% !important;
            height: 540px !important;
            padding: 1.25rem !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace !important;
            font-size: 0.84rem !important;
            line-height: 1.6 !important;
            border: none !important;
            outline: none !important;
            resize: vertical !important;
            color: #f8fafc !important;
            background-color: #090d16 !important;
            box-sizing: border-box !important;
        }

        .fsc-editor-footer {
            padding: 0.875rem 1rem !important;
            background-color: #f8fafc !important;
            border-top: 1px solid #e2e8f0 !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        :is(.dark) .fsc-editor-footer {
            background-color: #1e293b !important;
            border-top-color: #334155 !important;
        }

        .fsc-footer-actions {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 0.75rem !important;
            width: 100% !important;
        }

        .fsc-footer-left {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.625rem !important;
        }

        .fsc-shortcut-hint {
            font-size: 0.75rem !important;
            color: #64748b !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.375rem !important;
        }

        :is(.dark) .fsc-shortcut-hint {
            color: #94a3b8 !important;
        }

        .fsc-kbd {
            display: inline-block !important;
            padding: 0.125rem 0.375rem !important;
            font-size: 0.6875rem !important;
            font-family: ui-monospace, monospace !important;
            font-weight: 600 !important;
            color: #334155 !important;
            background-color: #e2e8f0 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.25rem !important;
            line-height: 1.2 !important;
        }

        :is(.dark) .fsc-kbd {
            color: #e2e8f0 !important;
            background-color: #334155 !important;
            border-color: #475569 !important;
        }

        .fsc-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            margin-bottom: 0.75rem;
            width: 100%;
            box-sizing: border-box;
        }

        .fsc-alert[style*="display: none"],
        [x-cloak] {
            display: none !important;
        }

        .fsc-alert-success {
            background-color: #ecfdf5 !important;
            border: 1px solid #a7f3d0 !important;
            color: #065f46 !important;
        }

        :is(.dark) .fsc-alert-success {
            background-color: rgba(6, 95, 70, 0.2) !important;
            border-color: #065f46 !important;
            color: #a7f3d0 !important;
        }

        .fsc-alert-error {
            background-color: #fff1f2 !important;
            border: 1px solid #fecdd3 !important;
            color: #9f1239 !important;
        }

        :is(.dark) .fsc-alert-error {
            background-color: rgba(159, 18, 57, 0.2) !important;
            border-color: #9f1239 !important;
            color: #fecdd3 !important;
        }

        .fsc-visual-wrapper {
            border-radius: 0.75rem !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #ffffff !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            width: 100% !important;
        }

        :is(.dark) .fsc-visual-wrapper {
            border-color: #1e293b !important;
            background-color: #0f172a !important;
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
        <div class="fsc-top-bar">
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

            {{-- Status Badges --}}
            <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <div x-show="mode === 'visual'">
                    <x-filament::badge color="gray" size="sm" icon="heroicon-m-pencil-square">
                        Visual Builder Aktif
                    </x-filament::badge>
                </div>
                <div x-show="mode === 'json' && isValidSyntax" x-cloak>
                    <x-filament::badge color="success" size="sm" icon="heroicon-m-check-circle">
                        Sintaks JSON Valid
                    </x-filament::badge>
                </div>
                <div x-show="mode === 'json' && !isValidSyntax" x-cloak>
                    <x-filament::badge color="danger" size="sm" icon="heroicon-m-exclamation-triangle">
                        Format JSON Salah
                    </x-filament::badge>
                </div>
            </div>
        </div>

        <template x-if="loadError">
            <div class="fsc-alert fsc-alert-error" style="margin-top: 0.5rem;">
                <x-filament::icon
                    icon="heroicon-m-exclamation-triangle"
                    style="width: 1.25rem; height: 1.25rem; flex-shrink: 0; color: #e11d48;"
                />
                <span x-text="'Gagal memuat visual builder: ' + loadErrorDetail" style="font-weight: 500;"></span>
            </div>
        </template>

        {{-- Visual Builder View --}}
        <div x-show="mode === 'visual'" class="w-full">
            <div 
                x-ref="surveyCreator" 
                class="fsc-visual-wrapper"
                style="height: {{ $field->isFullScreen() ? 'calc(100vh - 170px)' : 'calc(100vh - 220px)' }}; min-height: 720px; width: 100%;"
            >
                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #64748b; background-color: rgba(248, 250, 252, 0.5);">
                    <div style="text-align: center; padding: 1.5rem;">
                        <x-filament::loading-indicator class="h-10 w-10 text-primary-600 mb-3" style="width: 2.5rem; height: 2.5rem; margin: 0 auto 0.75rem auto; color: #0284c7;" />
                        <p style="font-size: 0.875rem; font-weight: 600; color: #334155; margin-bottom: 0.25rem;">Memuat Visual Survey Designer...</p>
                        <p style="font-size: 0.75rem; color: #64748b;">Menyiapkan kanvas perancangan dan panel pengaturan</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- JSON Editor View --}}
        <div x-show="mode === 'json'" class="w-full" x-cloak>
            <div class="fsc-card">
                {{-- Editor Top Bar --}}
                <div class="fsc-editor-toolbar">
                    <div class="fsc-editor-title">
                        <x-filament::icon
                            icon="heroicon-m-document-text"
                            style="width: 1.25rem; height: 1.25rem; color: #0284c7;"
                        />
                        <span>Editor Skema JSON</span>
                        <span class="fsc-editor-subtitle">survey-schema.json</span>
                    </div>

                    {{-- Toolbar Action Buttons (Menggunakan Native Filament Button) --}}
                    <div class="fsc-toolbar-actions">
                        <x-filament::button
                            type="button"
                            color="gray"
                            size="xs"
                            icon="heroicon-m-code-bracket"
                            x-on:click="formatJson()"
                            tooltip="Rapikan format JSON dengan indentasi 2 spasi"
                        >
                            Rapikan JSON
                        </x-filament::button>

                        <x-filament::button
                            type="button"
                            color="gray"
                            size="xs"
                            icon="heroicon-m-clipboard-document"
                            x-on:click="copyJson()"
                            tooltip="Salin seluruh isi JSON ke clipboard"
                        >
                            <span x-text="copied ? 'Tersalin!' : 'Salin JSON'">Salin JSON</span>
                        </x-filament::button>

                        <x-filament::button
                            type="button"
                            color="gray"
                            size="xs"
                            icon="heroicon-m-arrow-path"
                            x-on:click="resetJson()"
                            tooltip="Reset ke skema yang tersimpan di database"
                        >
                            Reset
                        </x-filament::button>
                    </div>
                </div>

                {{-- Code Editor Textarea --}}
                <div class="fsc-editor-body">
                    <textarea
                        x-model="jsonText"
                        x-on:input="validateJson()"
                        x-on:keydown="handleTab($event)"
                        class="fsc-textarea"
                        spellcheck="false"
                        placeholder="{ ... }"
                    ></textarea>
                </div>

                {{-- Alert Banners & Action Bar --}}
                <div class="fsc-editor-footer">
                    {{-- Error Notification Banner --}}
                    <div 
                        x-show="Boolean(jsonError && jsonError.trim())" 
                        class="fsc-alert fsc-alert-error"
                        x-cloak
                    >
                        <x-filament::icon
                            icon="heroicon-m-exclamation-triangle"
                            style="width: 1.25rem; height: 1.25rem; flex-shrink: 0; color: #e11d48; margin-top: 1px;"
                        />
                        <div style="flex: 1;">
                            <strong style="display: block; font-weight: 600;">Format JSON Tidak Valid</strong>
                            <span x-text="jsonError" style="font-family: ui-monospace, monospace; font-size: 0.75rem; word-break: break-all;"></span>
                        </div>
                    </div>

                    {{-- Success Notification Banner --}}
                    <div 
                        x-show="showSuccess && !(jsonError && jsonError.trim())" 
                        class="fsc-alert fsc-alert-success"
                        x-cloak
                    >
                        <x-filament::icon
                            icon="heroicon-m-check-circle"
                            style="width: 1.25rem; height: 1.25rem; flex-shrink: 0; color: #059669;"
                        />
                        <span style="font-weight: 500;">JSON schema berhasil diterapkan ke perancang kuesioner.</span>
                    </div>

                    {{-- Actions Row (Menggunakan Native Filament Button) --}}
                    <div class="fsc-footer-actions">
                        <div class="fsc-footer-left">
                            <x-filament::button
                                type="button"
                                color="primary"
                                size="sm"
                                icon="heroicon-m-check"
                                x-on:click="applyJson()"
                            >
                                Terapkan JSON
                            </x-filament::button>

                            <x-filament::button
                                type="button"
                                color="gray"
                                size="sm"
                                icon="heroicon-m-eye"
                                x-on:click="switchMode('visual')"
                            >
                                Lihat di Visual Builder
                            </x-filament::button>
                        </div>

                        <div class="fsc-shortcut-hint">
                            <span>Shortcut:</span>
                            <kbd class="fsc-kbd">Tab</kbd>
                            <span>indentasi •</span>
                            <kbd class="fsc-kbd">Ctrl+Enter</kbd>
                            <span>/</span>
                            <kbd class="fsc-kbd">Cmd+S</kbd>
                            <span>terapkan</span>
                        </div>
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
