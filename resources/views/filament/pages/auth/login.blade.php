<x-filament-panels::page.simple>
    <div class="space-y-6">
        {{-- Flash Session Alerts --}}
        @if (session('error'))
            <div class="p-4 text-sm text-red-800 bg-red-50 dark:bg-red-950/40 dark:text-red-300 rounded-xl border border-red-200 dark:border-red-900 flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <span class="font-semibold block">Gagal Masuk</span>
                    <span class="text-xs sm:text-sm">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="p-4 text-sm text-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-900 flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <span class="text-xs sm:text-sm">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        {{-- Heroic Single SSO Button --}}
        <div>
            <a href="{{ route('sipetra.login') }}"
               id="sipetra-sso-btn"
               onclick="if(this.getAttribute('data-submitting')) return false; this.setAttribute('data-submitting', '1'); this.style.pointerEvents='none'; this.style.opacity='0.85'; document.getElementById('sipetra-sso-text').innerText='Menghubungkan ke SIPETRA...'; document.getElementById('sipetra-sso-spinner').classList.remove('hidden'); document.getElementById('sipetra-sso-arrow').classList.add('hidden');"
               class="group relative flex items-center justify-between w-full px-5 py-4 rounded-xl text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200 transform active:scale-[0.99]"
               style="background-color: #005596;">
               
                <div class="flex items-center gap-3.5">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white p-1.5 shadow-sm shrink-0">
                        <img src="{{ asset('images/logo_bps.png') }}" class="w-full h-full object-contain" alt="Logo BPS">
                    </span>
                    <span id="sipetra-sso-text" class="text-base font-bold tracking-wide">Masuk dengan SIPETRA SSO</span>
                </div>

                <div class="flex items-center shrink-0">
                    {{-- Spinner (hidden by default) --}}
                    <svg id="sipetra-sso-spinner" class="hidden w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>

                    {{-- Arrow --}}
                    <svg id="sipetra-sso-arrow" class="w-5 h-5 text-white/90 group-hover:text-white group-hover:translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">
                Akses portal survei terintegrasi menggunakan akun resmi BPS Kabupaten Demak
            </p>
        </div>

        {{-- Accordion Fallback: Opsi Darurat / Pengembang --}}
        <div x-data="{ show: @entangle('showFallback') }" class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-800">
            <div class="flex justify-center">
                <button 
                    type="button" 
                    @click="show = !show"
                    class="text-xs font-medium text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors flex items-center gap-1.5 focus:outline-none cursor-pointer"
                >
                    <span>Opsi Darurat / Pengembang</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': show }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <div x-show="show" x-cloak class="mt-6 space-y-4">
                <div class="p-3 text-xs text-amber-700 bg-amber-50 dark:bg-amber-950/30 dark:text-amber-400 rounded-lg border border-amber-200 dark:border-amber-800/50 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Akses darurat menggunakan akun email dan kata sandi lokal pengembang / administrator.</span>
                </div>

                {{ $this->content }}
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
