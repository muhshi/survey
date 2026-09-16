<div x-data="{
    tab: '{{ $data['total_belum'] > 0 ? 'belum' : 'sudah' }}',
    searchBelum: '',
    searchSudah: '',
    copiedEmails: false,
    copiedBroadcast: false,
    copiedUrl: false,
    copy(text, type) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            if (type === 'emails') { this.copiedEmails = true; setTimeout(() => this.copiedEmails = false, 2500); }
            if (type === 'broadcast') { this.copiedBroadcast = true; setTimeout(() => this.copiedBroadcast = false, 2500); }
            if (type === 'url') { this.copiedUrl = true; setTimeout(() => this.copiedUrl = false, 2500); }
        });
    }
}" class="space-y-6">

    {{-- Top Overview & Metrics --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-900/60">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 dark:bg-primary-950/50 dark:text-primary-300">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        Target: {{ $data['target_scope'] }}
                    </span>

                    @if($data['is_quiz'])
                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                            Mode Kuis (Passing: {{ $data['passing_score'] }}%)
                        </span>
                    @endif
                </div>
                <h3 class="mt-1 text-base font-bold text-gray-900 dark:text-white">
                    {{ $record->title }}
                </h3>
            </div>

            {{-- Quick Link & Copy --}}
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="copy('{{ $data['public_url'] }}', 'url')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    <svg class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                    </svg>
                    <span x-text="copiedUrl ? 'Link Disalin!' : 'Salin Link Survei'"></span>
                </button>
            </div>
        </div>

        {{-- 3 Key Stat Cards --}}
        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
            {{-- Target / Total --}}
            <div class="rounded-lg border border-gray-200/80 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-800/90">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Target Peserta</div>
                <div class="mt-1 flex items-baseline justify-between">
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white">
                        {{ $data['has_target'] ? $data['total_target'] : $data['total_sudah'] }}
                    </div>
                    <span class="text-xs text-gray-400">orang</span>
                </div>
            </div>

            {{-- Sudah Mengisi --}}
            <div class="rounded-lg border border-emerald-200/80 bg-emerald-50/40 p-3 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20">
                <div class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Sudah Mengisi</div>
                <div class="mt-1 flex items-baseline justify-between">
                    <div class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400">
                        {{ $data['total_sudah'] }}
                    </div>
                    @if($data['percentage'] !== null)
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                            {{ $data['percentage'] }}%
                        </span>
                    @else
                        <span class="text-xs text-emerald-600/70">respon</span>
                    @endif
                </div>
            </div>

            {{-- Belum Mengisi --}}
            <div class="rounded-lg border {{ $data['total_belum'] > 0 ? 'border-amber-300/80 bg-amber-50/50 dark:border-amber-900/60 dark:bg-amber-950/20' : 'border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-800/90' }} p-3 shadow-sm">
                <div class="text-xs font-medium {{ $data['total_belum'] > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-gray-500 dark:text-gray-400' }}">
                    Belum Mengisi
                </div>
                <div class="mt-1 flex items-baseline justify-between">
                    <div class="text-2xl font-extrabold {{ $data['total_belum'] > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $data['total_belum'] }}
                    </div>
                    @if($data['has_target'] && $data['total_target'] > 0)
                        <span class="inline-flex items-center rounded-full {{ $data['total_belum'] > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }} px-2 py-0.5 text-xs font-semibold">
                            {{ round(($data['total_belum'] / max(1, $data['total_target'])) * 100) }}% sisa
                        </span>
                    @else
                        <span class="text-xs text-gray-400">orang</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        @if($data['has_target'] && $data['percentage'] !== null)
            <div class="mt-3">
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div
                        class="h-full rounded-full transition-all duration-500 {{ $data['percentage'] >= 100 ? 'bg-emerald-500' : 'bg-primary-600' }}"
                        style="width: {{ min(100, $data['percentage']) }}%"
                    ></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Quick Action Buttons for Reminders --}}
    @if($data['total_belum'] > 0)
        <div class="flex flex-wrap items-center gap-2 rounded-lg border border-amber-200 bg-amber-50/70 p-3 dark:border-amber-900/40 dark:bg-amber-950/20">
            <span class="text-xs font-semibold text-amber-900 dark:text-amber-200 flex items-center gap-1">
                <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                Aksi Cepat Pengingat:
            </span>

            @if(!empty($data['all_belum_emails']))
                <button
                    type="button"
                    @click="copy(@js($data['all_belum_emails']), 'emails')"
                    class="inline-flex items-center gap-1.5 rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-sm border border-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 transition"
                >
                    <svg class="h-3.5 w-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    <span x-text="copiedEmails ? 'Email Tersalin!' : 'Salin Semua Email Belum ({{ $data['total_belum'] }})'"></span>
                </button>
            @endif

            <button
                type="button"
                @click="copy(@js($data['reminder_text']), 'broadcast')"
                class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 transition"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <span x-text="copiedBroadcast ? 'Pesan WhatsApp Tersalin!' : 'Salin Format Teks WhatsApp'"></span>
            </button>
        </div>
    @endif

    {{-- Tabs Header --}}
    <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button
                type="button"
                @click="tab = 'belum'"
                :class="tab === 'belum' ? 'border-amber-500 text-amber-600 dark:text-amber-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'"
                class="flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition"
            >
                <span>Belum Mengisi</span>
                <span :class="tab === 'belum' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'" class="rounded-full px-2 py-0.5 text-xs font-semibold">
                    {{ $data['total_belum'] }}
                </span>
            </button>

            <button
                type="button"
                @click="tab = 'sudah'"
                :class="tab === 'sudah' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'"
                class="flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition"
            >
                <span>Sudah Mengisi</span>
                <span :class="tab === 'sudah' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'" class="rounded-full px-2 py-0.5 text-xs font-semibold">
                    {{ $data['total_sudah'] }}
                </span>
            </button>

            <button
                type="button"
                @click="tab = 'broadcast'"
                :class="tab === 'broadcast' ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'"
                class="flex items-center gap-2 whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition"
            >
                <span>Template Broadcast</span>
                <span class="rounded-full bg-primary-100 text-primary-800 dark:bg-primary-900/60 dark:text-primary-200 px-2 py-0.5 text-xs font-semibold">
                    WA / Grup
                </span>
            </button>
        </nav>
    </div>

    {{-- TAB CONTENT: BELUM MENGISI --}}
    <div x-show="tab === 'belum'" class="space-y-3">
        @if(!$data['has_target'])
            <div class="rounded-lg border border-blue-200 bg-blue-50/70 p-4 text-xs text-blue-900 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-200">
                <div class="font-bold mb-1">ℹ️ Survei Terbuka / Publik Tanpa Batasan Kelompok</div>
                Survei ini bersifat umum dan tidak dibatasi ke kelompok responden tertentu, sehingga daftar peserta yang "belum mengisi" tidak dapat ditentukan secara otomatis. Jika ingin melacak target responden tertentu, tautkan <strong>Kelompok Survei (Group)</strong> pada survei ini.
            </div>
        @elseif(empty($data['belum']))
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 py-12 text-center dark:border-gray-700">
                <div class="rounded-full bg-emerald-50 p-3 dark:bg-emerald-950/50 text-emerald-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <h4 class="mt-3 text-sm font-bold text-gray-900 dark:text-white">Semua Peserta Sudah Mengisi!</h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Seluruh {{ $data['total_target'] }} peserta target telah menyelesaikan pengisian survei/kuis ini.</p>
            </div>
        @else
            {{-- Search Bar --}}
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input
                    type="text"
                    x-model="searchBelum"
                    placeholder="Cari nama, email, unit kerja, atau jabatan..."
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-4 text-xs text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
            </div>

            {{-- Table --}}
            <div class="max-h-96 overflow-y-auto overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                    <thead class="sticky top-0 bg-gray-100 text-[11px] uppercase tracking-wider text-gray-600 dark:bg-gray-800 dark:text-gray-400 z-10">
                        <tr>
                            <th class="py-2.5 px-3 w-10 text-center">#</th>
                            <th class="py-2.5 px-3">Peserta</th>
                            <th class="py-2.5 px-3">Unit / Jabatan</th>
                            <th class="py-2.5 px-3">Kontak</th>
                            <th class="py-2.5 px-3 text-right">Aksi Pengingat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @foreach($data['belum'] as $index => $item)
                            <tr
                                x-show="!searchBelum || ('{{ strtolower($item['name']) }}'.includes(searchBelum.toLowerCase()) || '{{ strtolower($item['email']) }}'.includes(searchBelum.toLowerCase()) || '{{ strtolower($item['unit_kerja']) }}'.includes(searchBelum.toLowerCase()) || '{{ strtolower($item['jabatan']) }}'.includes(searchBelum.toLowerCase()))"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition"
                            >
                                <td class="py-2.5 px-3 text-center text-gray-400 font-mono text-[11px]">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $item['name'] }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                        {{ $item['email'] }}
                                    </div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="font-medium text-gray-800 dark:text-gray-200">
                                        {{ $item['unit_kerja'] }}
                                    </div>
                                    @if($item['jabatan'] !== '-')
                                        <div class="text-[11px] text-gray-400">
                                            {{ $item['jabatan'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3">
                                    @if(!empty($item['nomor_hp']))
                                        <span class="font-mono text-xs text-gray-700 dark:text-gray-300">
                                            {{ $item['nomor_hp'] }}
                                        </span>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    @if(!empty($item['wa_link']))
                                        <a
                                            href="{{ $item['wa_link'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-emerald-500 transition"
                                            title="Kirim pesan pengingat langsung via WhatsApp"
                                        >
                                            <svg class="h-3 w-3 fill-current" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                            </svg>
                                            Ingatkan WA
                                        </a>
                                    @elseif(!empty($item['email']))
                                        <a
                                            href="mailto:{{ $item['email'] }}?subject={{ rawurlencode('[PENGINGAT] ' . $record->title) }}&body={{ rawurlencode("Halo {$item['name']},\n\nMohon untuk segera mengisi survei/kuis \"{$record->title}\" melalui link: {$data['public_url']}\n\nTerima kasih.") }}"
                                            class="inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white px-2 py-1 text-[11px] font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition"
                                        >
                                            Email
                                        </a>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic">Tidak ada kontak</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- TAB CONTENT: SUDAH MENGISI --}}
    <div x-show="tab === 'sudah'" class="space-y-3">
        @if(empty($data['sudah']))
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 py-12 text-center dark:border-gray-700">
                <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-800 text-gray-400">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <h4 class="mt-3 text-sm font-bold text-gray-900 dark:text-white">Belum Ada Respon</h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Belum ada peserta yang mengirimkan jawaban pada survei/kuis ini.</p>
            </div>
        @else
            {{-- Search Bar --}}
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input
                    type="text"
                    x-model="searchSudah"
                    placeholder="Cari nama responden, email, unit kerja..."
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-4 text-xs text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
            </div>

            {{-- Table --}}
            <div class="max-h-96 overflow-y-auto overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                    <thead class="sticky top-0 bg-gray-100 text-[11px] uppercase tracking-wider text-gray-600 dark:bg-gray-800 dark:text-gray-400 z-10">
                        <tr>
                            <th class="py-2.5 px-3 w-10 text-center">#</th>
                            <th class="py-2.5 px-3">Responden</th>
                            <th class="py-2.5 px-3">Unit / Jabatan</th>
                            <th class="py-2.5 px-3">Waktu Submit</th>
                            <th class="py-2.5 px-3 text-center">Percobaan</th>
                            @if($data['is_quiz'])
                                <th class="py-2.5 px-3 text-right">Skor / Status</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @foreach($data['sudah'] as $index => $item)
                            <tr
                                x-show="!searchSudah || ('{{ strtolower($item['name']) }}'.includes(searchSudah.toLowerCase()) || '{{ strtolower($item['email']) }}'.includes(searchSudah.toLowerCase()) || '{{ strtolower($item['unit_kerja']) }}'.includes(searchSudah.toLowerCase()))"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition"
                            >
                                <td class="py-2.5 px-3 text-center text-gray-400 font-mono text-[11px]">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="font-semibold text-gray-900 dark:text-white flex items-center gap-1.5">
                                        {{ $item['name'] }}
                                        @if(!$item['is_registered'])
                                            <span class="rounded bg-gray-100 px-1 py-0.5 text-[9px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Tamu</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                        {{ $item['email'] }}
                                    </div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="text-gray-800 dark:text-gray-200">
                                        {{ $item['unit_kerja'] }}
                                    </div>
                                    @if($item['jabatan'] !== '-')
                                        <div class="text-[11px] text-gray-400">
                                            {{ $item['jabatan'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-gray-600 dark:text-gray-400">
                                    {{ $item['latest_submitted_at'] }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $item['attempts'] }}x
                                    </span>
                                </td>
                                @if($data['is_quiz'])
                                    <td class="py-2.5 px-3 text-right">
                                        @if($item['best_score'] !== null)
                                            <div class="font-bold {{ $item['passed'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                {{ $item['best_score'] }}%
                                            </div>
                                            <span class="inline-flex items-center rounded px-1.5 py-0.2 text-[10px] font-semibold {{ $item['passed'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300' }}">
                                                {{ $item['passed'] ? 'Lulus' : 'Belum Lulus' }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- TAB CONTENT: TEMPLATE BROADCAST --}}
    <div x-show="tab === 'broadcast'" class="space-y-4">
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-3">
                <div class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                    </svg>
                    Format Pesan WhatsApp Siap Broadcast (Tersedia Daftar Nama)
                </div>
                <button
                    type="button"
                    @click="copy(@js($data['reminder_text']), 'broadcast')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 transition"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                    </svg>
                    <span x-text="copiedBroadcast ? 'Tersalin ke Clipboard!' : 'Salin Pesan WhatsApp'"></span>
                </button>
            </div>

            <textarea
                readonly
                rows="10"
                class="w-full rounded-md border border-gray-300 bg-white p-3 font-mono text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:outline-none"
            >{{ $data['reminder_text'] }}</textarea>
            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                💡 Tip: Anda dapat langsung menyalin teks di atas dan mengirimkannya ke WhatsApp Grup mitra / pegawai agar yang bersangkutan segera diingatkan.
            </p>
        </div>
    </div>
</div>
