<div class="resp-wrap" x-data="{
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
}">

    <style>
        .resp-wrap {
            font-family: inherit;
            color: #1e293b;
            line-height: 1.5;
        }
        :is(.dark .resp-wrap) {
            color: #e2e8f0;
        }

        /* Icons */
        .resp-wrap svg {
            display: inline-block;
            vertical-align: middle;
            flex-shrink: 0;
            overflow: visible;
        }
        .resp-icon-xs { width: 12px !important; height: 12px !important; }
        .resp-icon-sm { width: 14px !important; height: 14px !important; }
        .resp-icon-md { width: 16px !important; height: 16px !important; }
        .resp-icon-lg { width: 20px !important; height: 20px !important; }
        .resp-icon-xl { width: 36px !important; height: 36px !important; }

        /* Top Overview Card */
        .resp-header-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        :is(.dark .resp-header-box) {
            background: rgba(15, 23, 42, 0.6);
            border-color: #334155;
        }

        .resp-header-top {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .resp-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 4px;
        }
        :is(.dark .resp-title) {
            color: #f8fafc;
        }

        .resp-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }
        .resp-badge-sky {
            background: #e0f2fe;
            color: #0369a1;
        }
        :is(.dark .resp-badge-sky) {
            background: rgba(3, 105, 161, 0.25);
            color: #38bdf8;
        }
        .resp-badge-amber {
            background: #fef3c7;
            color: #b45309;
        }
        :is(.dark .resp-badge-amber) {
            background: rgba(180, 83, 9, 0.25);
            color: #fbbf24;
        }

        /* Stat Cards Grid */
        .resp-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        .resp-stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
        }
        :is(.dark .resp-stat-card) {
            background: #1e293b;
            border-color: #334155;
        }
        .resp-stat-card-success {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }
        :is(.dark .resp-stat-card-success) {
            background: rgba(20, 83, 45, 0.2);
            border-color: rgba(34, 197, 94, 0.3);
        }
        .resp-stat-card-warning {
            background: #fffbeb;
            border-color: #fde68a;
        }
        :is(.dark .resp-stat-card-warning) {
            background: rgba(120, 53, 15, 0.2);
            border-color: rgba(245, 158, 11, 0.3);
        }

        .resp-stat-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .resp-stat-val-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-top: 4px;
        }
        .resp-stat-number {
            font-size: 24px;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
        }
        :is(.dark .resp-stat-number) {
            color: #f8fafc;
        }
        .resp-stat-card-success .resp-stat-number { color: #15803d; }
        :is(.dark .resp-stat-card-success .resp-stat-number) { color: #4ade80; }
        .resp-stat-card-warning .resp-stat-number { color: #b45309; }
        :is(.dark .resp-stat-card-warning .resp-stat-number) { color: #fbbf24; }

        /* Progress Bar */
        .resp-progress-wrap {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 9999px;
            overflow: hidden;
            margin-top: 4px;
        }
        :is(.dark .resp-progress-wrap) {
            background: #334155;
        }
        .resp-progress-bar {
            height: 100%;
            border-radius: 9999px;
            background: #0284c7;
            transition: width 0.4s ease;
        }
        .resp-progress-bar-complete {
            background: #16a34a;
        }

        /* Action Alert Banner */
        .resp-alert-banner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        :is(.dark .resp-alert-banner) {
            background: rgba(120, 53, 15, 0.25);
            border-color: rgba(245, 158, 11, 0.35);
        }

        /* Buttons */
        .resp-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            line-height: 1.25;
            background: #ffffff;
            color: #334155;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .resp-btn:hover {
            background: #f1f5f9;
        }
        :is(.dark .resp-btn) {
            background: #1e293b;
            border-color: #475569;
            color: #cbd5e1;
        }
        :is(.dark .resp-btn:hover) {
            background: #334155;
        }

        .resp-btn-emerald {
            background: #059669 !important;
            color: #ffffff !important;
            border-color: #047857 !important;
        }
        .resp-btn-emerald:hover {
            background: #047857 !important;
        }

        .resp-btn-wa {
            background: #10b981;
            color: #ffffff;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(16, 185, 129, 0.2);
            transition: background 0.15s ease;
        }
        .resp-btn-wa:hover {
            background: #059669;
            color: #ffffff;
        }

        /* Tabs Navigation */
        .resp-tabs-nav {
            display: flex;
            gap: 16px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 16px;
        }
        :is(.dark .resp-tabs-nav) {
            border-color: #334155;
        }
        .resp-tab-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 4px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .resp-tab-link:hover {
            color: #0f172a;
        }
        :is(.dark .resp-tab-link:hover) {
            color: #f8fafc;
        }
        .resp-tab-link.active-belum {
            color: #d97706;
            border-bottom-color: #d97706;
            font-weight: 700;
        }
        .resp-tab-link.active-sudah {
            color: #059669;
            border-bottom-color: #059669;
            font-weight: 700;
        }
        .resp-tab-link.active-broadcast {
            color: #0284c7;
            border-bottom-color: #0284c7;
            font-weight: 700;
        }

        .resp-pill {
            padding: 2px 7px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            background: #f1f5f9;
            color: #475569;
        }
        :is(.dark .resp-pill) {
            background: #334155;
            color: #cbd5e1;
        }
        .resp-tab-link.active-belum .resp-pill {
            background: #fef3c7;
            color: #92400e;
        }
        .resp-tab-link.active-sudah .resp-pill {
            background: #dcfce7;
            color: #166534;
        }

        /* Search Input */
        .resp-search-wrap {
            position: relative;
            margin-bottom: 12px;
        }
        .resp-search-wrap svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .resp-input {
            width: 100%;
            padding: 8px 12px 8px 34px;
            font-size: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: #0f172a;
            outline: none;
            box-sizing: border-box;
        }
        .resp-input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }
        :is(.dark .resp-input) {
            background: #1e293b;
            border-color: #475569;
            color: #f8fafc;
        }

        /* Table Design */
        .resp-table-scroll {
            max-height: 420px;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        :is(.dark .resp-table-scroll) {
            border-color: #334155;
        }
        .resp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
        }
        .resp-table thead th {
            position: sticky;
            top: 0;
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            z-index: 5;
        }
        :is(.dark .resp-table thead th) {
            background: #0f172a;
            color: #94a3b8;
            border-color: #334155;
        }
        .resp-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        :is(.dark .resp-table tbody td) {
            border-color: rgba(51, 65, 85, 0.5);
        }
        .resp-table tbody tr:hover {
            background: #f8fafc;
        }
        :is(.dark .resp-table tbody tr:hover) {
            background: rgba(255, 255, 255, 0.03);
        }

        .resp-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 13px;
        }
        :is(.dark .resp-name) {
            color: #f8fafc;
        }
        .resp-subtext {
            font-size: 11px;
            color: #64748b;
        }
        :is(.dark .resp-subtext) {
            color: #94a3b8;
        }

        /* Status Pills in Table */
        .resp-score-pass {
            color: #16a34a;
            font-weight: 800;
            font-size: 13px;
        }
        .resp-score-fail {
            color: #e11d48;
            font-weight: 800;
            font-size: 13px;
        }
        .resp-badge-pass {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }
        :is(.dark .resp-badge-pass) {
            background: rgba(22, 163, 74, 0.25);
            color: #4ade80;
        }
        .resp-badge-fail {
            display: inline-block;
            background: #ffe4e6;
            color: #be123c;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }
        :is(.dark .resp-badge-fail) {
            background: rgba(225, 29, 72, 0.25);
            color: #fb7185;
        }

        /* Empty State */
        .resp-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 36px 16px;
            text-align: center;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            margin: 12px 0;
        }
        :is(.dark .resp-empty) {
            border-color: #334155;
        }
    </style>

    {{-- Top Overview Card --}}
    <div class="resp-header-box">
        <div class="resp-header-top">
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="resp-badge resp-badge-sky">
                        <svg class="resp-icon-sm" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        Target: {{ $data['target_scope'] }}
                    </span>

                    @if($data['is_quiz'])
                        <span class="resp-badge resp-badge-amber">
                            Mode Kuis (Passing Score: {{ $data['passing_score'] }}%)
                        </span>
                    @endif
                </div>
                <div class="resp-title">{{ $record->title }}</div>
            </div>

            <div>
                <button
                    type="button"
                    @click="copy('{{ $data['public_url'] }}', 'url')"
                    class="resp-btn"
                    title="Salin tautan langsung survei/kuis"
                >
                    <svg class="resp-icon-sm" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                    </svg>
                    <span x-text="copiedUrl ? 'Tautan Disalin!' : 'Salin Tautan'"></span>
                </button>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="resp-stat-grid">
            {{-- Target Card --}}
            <div class="resp-stat-card">
                <div class="resp-stat-label">Total Target</div>
                <div class="resp-stat-val-row">
                    <div class="resp-stat-number">{{ $data['has_target'] ? $data['total_target'] : $data['total_sudah'] }}</div>
                    <span class="resp-subtext">peserta</span>
                </div>
            </div>

            {{-- Sudah Mengisi Card --}}
            <div class="resp-stat-card resp-stat-card-success">
                <div class="resp-stat-label" style="color: #15803d;">Sudah Mengisi</div>
                <div class="resp-stat-val-row">
                    <div class="resp-stat-number">{{ $data['total_sudah'] }}</div>
                    @if($data['percentage'] !== null)
                        <span class="resp-badge resp-badge-sky" style="background:#dcfce7; color:#15803d;">
                            {{ $data['percentage'] }}%
                        </span>
                    @else
                        <span class="resp-subtext">respon</span>
                    @endif
                </div>
            </div>

            {{-- Belum Mengisi Card --}}
            <div class="resp-stat-card {{ $data['total_belum'] > 0 ? 'resp-stat-card-warning' : '' }}">
                <div class="resp-stat-label" style="{{ $data['total_belum'] > 0 ? 'color: #b45309;' : '' }}">Belum Mengisi</div>
                <div class="resp-stat-val-row">
                    <div class="resp-stat-number">{{ $data['total_belum'] }}</div>
                    @if($data['has_target'] && $data['total_target'] > 0)
                        <span class="resp-badge" style="background:#fef3c7; color:#92400e;">
                            {{ round(($data['total_belum'] / max(1, $data['total_target'])) * 100) }}% sisa
                        </span>
                    @else
                        <span class="resp-subtext">orang</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        @if($data['has_target'] && $data['percentage'] !== null)
            <div>
                <div class="resp-progress-wrap">
                    <div
                        class="resp-progress-bar {{ $data['percentage'] >= 100 ? 'resp-progress-bar-complete' : '' }}"
                        style="width: {{ min(100, $data['percentage']) }}%;"
                    ></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Quick Actions Toolbar --}}
    @if($data['total_belum'] > 0)
        <div class="resp-alert-banner">
            <span style="font-size: 12px; font-weight: 700; color: #92400e; display: inline-flex; align-items: center; gap: 5px;">
                <svg class="resp-icon-md" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                Tindakan Cepat:
            </span>

            @if(!empty($data['all_belum_emails']))
                <button
                    type="button"
                    @click="copy(@js($data['all_belum_emails']), 'emails')"
                    class="resp-btn"
                >
                    <svg class="resp-icon-sm" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    <span x-text="copiedEmails ? 'Email Disalin!' : 'Salin Semua Email Belum ({{ $data['total_belum'] }})'"></span>
                </button>
            @endif

            <button
                type="button"
                @click="copy(@js($data['reminder_text']), 'broadcast')"
                class="resp-btn resp-btn-emerald"
            >
                <svg class="resp-icon-sm" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <span x-text="copiedBroadcast ? 'Teks WhatsApp Disalin!' : 'Salin Pesan Broadcast WA'"></span>
            </button>
        </div>
    @endif

    {{-- Tabs Navigation --}}
    <div class="resp-tabs-nav">
        <button
            type="button"
            @click="tab = 'belum'"
            :class="tab === 'belum' ? 'resp-tab-link active-belum' : 'resp-tab-link'"
        >
            <span>Belum Mengisi</span>
            <span class="resp-pill">{{ $data['total_belum'] }}</span>
        </button>

        <button
            type="button"
            @click="tab = 'sudah'"
            :class="tab === 'sudah' ? 'resp-tab-link active-sudah' : 'resp-tab-link'"
        >
            <span>Sudah Mengisi</span>
            <span class="resp-pill">{{ $data['total_sudah'] }}</span>
        </button>

        <button
            type="button"
            @click="tab = 'broadcast'"
            :class="tab === 'broadcast' ? 'resp-tab-link active-broadcast' : 'resp-tab-link'"
        >
            <span>Format Pesan Broadcast</span>
        </button>
    </div>

    {{-- TAB 1: BELUM MENGISI --}}
    <div x-show="tab === 'belum'">
        @if(!$data['has_target'])
            <div style="background:#eff6ff; border:1px solid #bfdbfe; padding:14px; border-radius:8px; font-size:12px; color:#1e40af; margin-bottom:12px;">
                <strong>ℹ️ Survei Terbuka / Tanpa Kelompok Khusus:</strong><br>
                Survei ini bersifat publik dan tidak dibatasi ke kelompok peserta tertentu. Tautkan <strong>Kelompok Survei (Group)</strong> pada survei ini untuk mengaktifkan pemantauan otomatis peserta yang belum mengisi.
            </div>
        @elseif(empty($data['belum']))
            <div class="resp-empty">
                <div style="color: #16a34a; margin-bottom: 8px;">
                    <svg class="resp-icon-xl" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Semua Peserta Sudah Mengisi!</div>
                <div class="resp-subtext" style="margin-top: 4px;">Seluruh {{ $data['total_target'] }} peserta target telah menyelesaikan pengisian.</div>
            </div>
        @else
            {{-- Search Bar --}}
            <div class="resp-search-wrap">
                <svg class="resp-icon-sm" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    type="text"
                    x-model="searchBelum"
                    placeholder="Cari nama, email, unit kerja..."
                    class="resp-input"
                />
            </div>

            {{-- Table --}}
            <div class="resp-table-scroll">
                <table class="resp-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th>Nama Peserta</th>
                            <th>Unit Kerja / Jabatan</th>
                            <th>Nomor HP</th>
                            <th style="text-align: right; width: 140px;">Aksi Pengingat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['belum'] as $index => $item)
                            <tr x-show="!searchBelum || ('{{ strtolower($item['name']) }}'.includes(searchBelum.toLowerCase()) || '{{ strtolower($item['email']) }}'.includes(searchBelum.toLowerCase()) || '{{ strtolower($item['unit_kerja']) }}'.includes(searchBelum.toLowerCase()))">
                                <td style="text-align: center; color: #94a3b8; font-family: monospace;">
                                    {{ $index + 1 }}
                                </td>
                                <td>
                                    <div class="resp-name">{{ $item['name'] }}</div>
                                    <div class="resp-subtext">{{ $item['email'] }}</div>
                                </td>
                                <td>
                                    <div>{{ $item['unit_kerja'] }}</div>
                                    @if($item['jabatan'] !== '-')
                                        <div class="resp-subtext">{{ $item['jabatan'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($item['nomor_hp']))
                                        <span style="font-family: monospace; font-size: 12px;">{{ $item['nomor_hp'] }}</span>
                                    @else
                                        <span class="resp-subtext" style="font-style: italic;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    @if(!empty($item['wa_link']))
                                        <a
                                            href="{{ $item['wa_link'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="resp-btn-wa"
                                            title="Kirim pesan pengingat langsung ke WhatsApp peserta ini"
                                        >
                                            <svg class="resp-icon-xs" width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                            </svg>
                                            Ingatkan WA
                                        </a>
                                    @elseif(!empty($item['email']))
                                        <a
                                            href="mailto:{{ $item['email'] }}?subject={{ rawurlencode('[PENGINGAT] ' . $record->title) }}&body={{ rawurlencode("Halo {$item['name']},\n\nMohon untuk segera menyelesaikan pengisian kuis/survei \"{$record->title}\" melalui link: {$data['public_url']}\n\nTerima kasih.") }}"
                                            class="resp-btn"
                                            style="padding: 3px 8px; font-size: 11px;"
                                        >
                                            Email
                                        </a>
                                    @else
                                        <span class="resp-subtext" style="font-style: italic;">No Kontak</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- TAB 2: SUDAH MENGISI --}}
    <div x-show="tab === 'sudah'">
        @if(empty($data['sudah']))
            <div class="resp-empty">
                <div style="color: #94a3b8; margin-bottom: 8px;">
                    <svg class="resp-icon-xl" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Belum Ada Jawaban Masuk</div>
                <div class="resp-subtext" style="margin-top: 4px;">Belum ada peserta yang menyelesaikan pengisian kuis/survei ini.</div>
            </div>
        @else
            {{-- Search Bar --}}
            <div class="resp-search-wrap">
                <svg class="resp-icon-sm" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    type="text"
                    x-model="searchSudah"
                    placeholder="Cari nama responden, email, unit..."
                    class="resp-input"
                />
            </div>

            {{-- Table --}}
            <div class="resp-table-scroll">
                <table class="resp-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th>Responden</th>
                            <th>Unit Kerja</th>
                            <th>Waktu Submit Terakhir</th>
                            <th style="text-align: center; width: 90px;">Percobaan</th>
                            @if($data['is_quiz'])
                                <th style="text-align: right; width: 110px;">Skor / Status</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['sudah'] as $index => $item)
                            <tr x-show="!searchSudah || ('{{ strtolower($item['name']) }}'.includes(searchSudah.toLowerCase()) || '{{ strtolower($item['email']) }}'.includes(searchSudah.toLowerCase()) || '{{ strtolower($item['unit_kerja']) }}'.includes(searchSudah.toLowerCase()))">
                                <td style="text-align: center; color: #94a3b8; font-family: monospace;">
                                    {{ $index + 1 }}
                                </td>
                                <td>
                                    <div class="resp-name">
                                        {{ $item['name'] }}
                                        @if(!$item['is_registered'])
                                            <span style="font-size: 10px; background: #f1f5f9; color: #475569; padding: 1px 5px; border-radius: 4px; font-weight: normal;">Tamu</span>
                                        @endif
                                    </div>
                                    <div class="resp-subtext">{{ $item['email'] }}</div>
                                </td>
                                <td>
                                    <div>{{ $item['unit_kerja'] }}</div>
                                    @if($item['jabatan'] !== '-')
                                        <div class="resp-subtext">{{ $item['jabatan'] }}</div>
                                    @endif
                                </td>
                                <td style="color: #475569; font-size: 11px;">
                                    {{ $item['latest_submitted_at'] }}
                                </td>
                                <td style="text-align: center;">
                                    <span class="resp-pill">{{ $item['attempts'] }}x</span>
                                </td>
                                @if($data['is_quiz'])
                                    <td style="text-align: right;">
                                        @if($item['best_score'] !== null)
                                            <div class="{{ $item['passed'] ? 'resp-score-pass' : 'resp-score-fail' }}">
                                                {{ $item['best_score'] }}%
                                            </div>
                                            <span class="{{ $item['passed'] ? 'resp-badge-pass' : 'resp-badge-fail' }}">
                                                {{ $item['passed'] ? 'Lulus' : 'Belum Lulus' }}
                                            </span>
                                        @else
                                            <span class="resp-subtext">-</span>
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

    {{-- TAB 3: TEMPLATE BROADCAST --}}
    <div x-show="tab === 'broadcast'">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-size: 12px; font-weight: 700; color: #0f172a; display: inline-flex; align-items: center; gap: 6px;">
                    <svg class="resp-icon-md" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="color: #10b981;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                    </svg>
                    Format Pesan WhatsApp Siap Broadcast:
                </span>
                <button
                    type="button"
                    @click="copy(@js($data['reminder_text']), 'broadcast')"
                    class="resp-btn resp-btn-emerald"
                >
                    <svg class="resp-icon-sm" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                    </svg>
                    <span x-text="copiedBroadcast ? 'Pesan Tersalin!' : 'Salin Pesan ke Clipboard'"></span>
                </button>
            </div>
            <textarea
                readonly
                rows="10"
                style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; font-family: monospace; font-size: 11px; background: #ffffff; color: #0f172a; box-sizing: border-box; outline: none;"
            >{{ $data['reminder_text'] }}</textarea>
            <div class="resp-subtext" style="margin-top: 8px;">
                💡 <strong>Tip:</strong> Klik tombol "Salin Pesan ke Clipboard", lalu tempel (*paste*) ke grup WhatsApp petugas/pegawai untuk mengingatkan seluruh yang belum mengisi sekaligus.
            </div>
        </div>
    </div>
</div>
