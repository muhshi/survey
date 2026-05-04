<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <style>
        /* removed broad svg override to prevent breaking filament icons */
        .survey-js-creator-container .sv-svg-icon {
            width: 1.5rem !important;
            height: 1.5rem !important;
        }
        /* Fix untuk right sidebar icons */
        .svc-side-bar__action-icon {
            width: 24px !important;
            height: 24px !important;
        }

        @if($field->isFullScreen())
            /* Sembunyikan Sidebar Filament */
            .fi-sidebar {
                display: none !important;
            }

            /* Hapus Margin Kiri pada Konten Utama */
            .fi-main {
                margin-inline-start: 0 !important;
            }

            /* Sembunyikan Topbar Filament */
            .fi-topbar {
                display: none !important;
            }

            /* Berikan ruang lebih pada header page */
            .fi-header {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }

            /* Sembunyikan breadcrumbs jika dirasa terlalu penuh */
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
        class="survey-js-creator-container bg-white"
        style="min-height: 800px;"
    >
        {{-- Mode Toggle --}}
        <div class="mb-4">
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
            
            <template x-if="loadError">
                <p class="mt-2 text-sm text-danger-600 fi-color-danger bg-danger-50 p-2 rounded-lg" x-text="'⚠ ' + loadErrorDetail"></p>
            </template>
        </div>

        {{-- Visual Builder --}}
        <div class="w-full">
            <div x-show="mode === 'visual'" x-ref="surveyCreator" style="height: {{ $field->isFullScreen() ? 'calc(100vh - 120px)' : 'calc(100vh - 180px)' }}; min-height: 700px; width: 100%;">
                <div class="flex items-center justify-center h-full text-slate-500 bg-white">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-amber-500 border-t-transparent mb-4"></div>
                    <p class="text-sm font-medium">Memuat Perancang Survei...</p>
                </div>
            </div>
            </div>
        </div>

        {{-- JSON Editor --}}
        <div x-show="mode === 'json'" class="p-4 bg-white" x-cloak>
            <div class="mb-3">
                <label class="block text-sm font-medium text-slate-700 mb-1">JSON Schema Kuesioner</label>
                <p class="text-xs text-slate-500 mb-2">Edit JSON di sini. Perubahan akan disinkronkan secara otomatis.</p>
            </div>
            
            <textarea
                x-model="jsonText"
                style="width: 100%; height: 500px; padding: 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; background-color: #f9fafb; font-family: ui-monospace, monospace; font-size: 0.875rem;"
                class="fi-input"
                spellcheck="false"
            ></textarea>
            
            <template x-if="jsonError">
                <p class="mt-2 text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg font-medium border border-red-100" x-text="jsonError"></p>
            </template>

            <div 
                x-show="showSuccess" 
                x-transition:enter="transition ease-out duration-300"
                class="mt-2 text-sm text-emerald-600 bg-emerald-50 px-3 py-2 rounded-lg font-medium border border-emerald-100 flex items-center gap-2"
                x-cloak
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                ✓ JSON berhasil diterapkan
            </div>

            <div class="flex items-center gap-3 mt-3">
                <button
                    type="button"
                    x-on:click="applyJson()"
                    class="fi-btn fi-btn-color-primary fi-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 disabled:pointer-events-none disabled:opacity-70 fi-btn-color-primary bg-amber-500 text-white hover:bg-amber-600 px-4 py-2 rounded-lg flex gap-2"
                >
                    Terapkan JSON
                </button>
                
                <button
                    type="button"
                    x-on:click="jsonText = JSON.stringify(state || {}, null, 2); applyJson(true);"
                    class="text-sm font-medium text-slate-600 hover:text-slate-900 px-3 py-2"
                >
                    Reset dari Data Tersimpan
                </button>
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

                init() {
                    console.log('[SurveyJS] Start init...');
                    
                    try {
                        this.jsonText = (this.state && typeof this.state === 'object')
                            ? JSON.stringify(this.state, null, 2)
                            : '{}';
                    } catch (e) {
                        this.jsonText = '{}';
                    }

                    // Auto-sync watcher
                    this.$watch('jsonText', (val) => {
                        if (this.mode !== 'json' || this.isInternalChange) return;
                        
                        // Debounce sync
                        clearTimeout(this.syncTimer);
                        this.syncTimer = setTimeout(() => {
                            this.applyJson(true);
                        }, 800);
                    });

                    // Wait for library
                    const checkReady = setInterval(() => {
                        if (typeof SurveyCreator !== 'undefined') {
                            clearInterval(checkReady);
                            this.setupCreator();
                        }
                    }, 500);
                },

                setupCreator() {
                    try {
                        const options = {
                            showLogicTab: true,
                            showTranslationTab: true,
                            showThemeTab: true,
                            isAutoSave: true,
                            showJSONEditorTab: false, // Kita handle manual lewat tab atas
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
                        
                        // INI KUNCINYA UNTUK MEMUNCULKAN IKON VERTIKAL DI SEBELAH KANAN
                        if (typeof this.instance.propertyGridNavigationMode !== 'undefined') {
                            this.instance.propertyGridNavigationMode = "buttons";
                        }
                        
                        if (SurveyCreator.localization) {
                            SurveyCreator.localization.currentLocale = "id";
                        }

                        if (this.state) {
                            this.instance.JSON = this.state;
                        }

                        // EVENT: Visual Builder diutak-atik
                        this.instance.onModified.add(() => {
                            if (this.mode === 'visual') {
                                this.isInternalChange = true;
                                const json = this.instance.JSON;
                                this.state = json;
                                this.jsonText = JSON.stringify(json, null, 2);
                                
                                // Reset flag setelah UI update
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
                        this.jsonText = JSON.stringify(this.state || {}, null, 2);
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
                        if (!silent) {
                            this.showSuccess = true;
                            setTimeout(() => this.showSuccess = false, 3000);
                        }
                        
                        setTimeout(() => { this.isInternalChange = false; }, 100);
                    } catch (e) {
                        if (!silent) {
                            this.jsonError = 'Format JSON Salah: ' + e.message;
                            this.showSuccess = false;
                        }
                    }
                }
            }));
        });
    </script>
</x-dynamic-component>
