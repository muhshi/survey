@extends('layouts.survey')

@section('title', 'Platform Form Digital')
@section('meta_description', 'Platform form digital BPS Kabupaten Demak — rekrutmen petugas, kuis pelatihan, evaluasi kegiatan, dan pendataan lapangan.')

@section('styles')
<style>
    /* ============ LANDING PAGE ============ */
    .lp-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        align-items: center;
        gap: 56px;
        padding: 64px 0 48px;
        position: relative;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 14px 7px 10px;
        border-radius: 999px;
        background: var(--surface);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        font-size: 0.76rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: var(--text-2);
        text-transform: uppercase;
    }
    .eyebrow::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 0 3px var(--success-50);
    }

    .lp-hero h1 {
        margin-top: 22px;
        font-size: clamp(2.3rem, 4.6vw, 3.55rem);
        font-weight: 800;
        line-height: 1.06;
        letter-spacing: -0.032em;
        text-wrap: balance;
    }
    .lp-hero h1 .highlight {
        color: var(--primary);
        position: relative;
        white-space: nowrap;
    }
    .lp-hero h1 .highlight::after {
        content: '';
        position: absolute;
        left: 2px;
        right: 2px;
        bottom: 0.06em;
        height: 0.18em;
        border-radius: 3px;
        background: linear-gradient(90deg, var(--accent) 0%, rgba(245, 158, 11, 0.25) 100%);
        z-index: -1;
    }

    .lp-hero .lede {
        margin-top: 18px;
        max-width: 52ch;
        font-size: 1.06rem;
        line-height: 1.68;
        color: var(--text-2);
    }

    .lp-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
    }
    .lp-hero__actions .btn { padding: 13px 24px; font-size: 0.95rem; }

    .lp-points {
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 8px 22px;
        margin-top: 26px;
    }
    .lp-points li {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 0.83rem;
        font-weight: 500;
        color: var(--text-2);
    }
    .lp-points svg { width: 15px; height: 15px; color: var(--success); flex-shrink: 0; }

    /* --- Hero visual --- */
    .lp-hero__visual {
        position: relative;
        padding: 8px 0 20px;
    }
    .lp-hero__visual::before {
        content: '';
        position: absolute;
        inset: -8% -6% 0 -6%;
        background:
            radial-gradient(circle at 78% 18%, var(--primary-50) 0%, transparent 62%),
            radial-gradient(circle at 18% 90%, var(--accent-50) 0%, transparent 58%);
        filter: blur(2px);
        z-index: 0;
    }

    .preview-card {
        position: relative;
        z-index: 1;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 22px;
        box-shadow: var(--shadow-lg);
    }
    .preview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    .preview-head .ph-title {
        font-family: var(--font-display);
        font-size: 0.98rem;
        font-weight: 700;
    }
    .preview-head .ph-title span {
        display: block;
        font-family: var(--font-sans);
        font-size: 0.72rem;
        font-weight: 500;
        color: var(--text-3);
        margin-top: 2px;
        letter-spacing: 0.02em;
    }
    .ph-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        background: var(--success-50);
        color: var(--success);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }
    .ph-pill::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .preview-question {
        padding: 18px 2px 4px;
    }
    .preview-question .pq-label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--text-3);
    }
    .preview-question .pq-text {
        margin-top: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.5;
        color: var(--text);
    }
    .pq-options { display: grid; gap: 8px; margin-top: 14px; }
    .pq-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 11px;
        background: var(--surface-2);
        font-size: 0.84rem;
        color: var(--text-2);
    }
    .pq-option .dot {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 1.5px solid var(--border-strong);
        flex-shrink: 0;
    }
    .pq-option.is-selected {
        border-color: var(--primary);
        background: var(--primary-50);
        color: var(--text);
        font-weight: 600;
    }
    .pq-option.is-selected .dot {
        border-color: var(--primary);
        background: var(--primary);
        box-shadow: inset 0 0 0 3px var(--surface);
    }
    .preview-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--border);
    }
    .preview-foot .pf-note {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        color: var(--text-3);
    }
    .preview-foot .pf-note svg { width: 13px; height: 13px; color: var(--success); }
    .preview-foot .pf-btn {
        padding: 8px 16px;
        border-radius: 9px;
        background: var(--primary);
        color: #fff;
        font-size: 0.76rem;
        font-weight: 700;
    }
    [data-theme="dark"] .preview-foot .pf-btn { color: #04121f; }

    .float-chip {
        position: absolute;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 14px;
        border-radius: 12px;
        background: var(--surface);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-md);
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--text-2);
    }
    .float-chip svg { width: 15px; height: 15px; }
    .float-chip.fc-top { top: -6px; right: -10px; color: var(--primary); }
    .float-chip.fc-bottom { bottom: 2px; left: -14px; color: var(--accent-600); }

    /* --- Stats --- */
    .lp-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }
    .stat {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 22px 26px;
    }
    .stat + .stat { border-left: 1px solid var(--border); }
    .stat-icon {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-50);
        color: var(--primary);
    }
    .stat-icon svg { width: 20px; height: 20px; }
    .stat:nth-child(2) .stat-icon { background: var(--accent-50); color: var(--accent-600); }
    .stat:nth-child(3) .stat-icon { background: var(--success-50); color: var(--success); }
    .stat-num {
        font-family: var(--font-display);
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.02em;
    }
    .stat-label {
        margin-top: 4px;
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--text-3);
    }

    /* --- Sections --- */
    .lp-section { padding-top: 72px; }
    .section-head { max-width: 640px; margin-bottom: 30px; }
    .section-head .kicker {
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--primary);
    }
    .section-head h2 {
        margin-top: 10px;
        font-size: clamp(1.65rem, 3vw, 2.15rem);
        font-weight: 800;
        letter-spacing: -0.025em;
        line-height: 1.15;
        text-wrap: balance;
    }
    .section-head p {
        margin-top: 10px;
        font-size: 0.98rem;
        line-height: 1.65;
        color: var(--text-2);
    }

    /* --- Features --- */
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }
    .feature-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 26px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }
    .feature-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--border-strong);
    }
    .feature-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        background: var(--primary-50);
        color: var(--primary);
    }
    .feature-icon svg { width: 21px; height: 21px; }
    .feature-card:nth-child(2) .feature-icon { background: var(--accent-50); color: var(--accent-600); }
    .feature-card:nth-child(3) .feature-icon { background: var(--success-50); color: var(--success); }
    .feature-card:nth-child(4) .feature-icon { background: #f1ecff; color: #7c3aed; }
    [data-theme="dark"] .feature-card:nth-child(4) .feature-icon { background: #241a3d; color: #c4a7ff; }
    .feature-card h3 {
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        margin-bottom: 7px;
    }
    .feature-card p {
        font-size: 0.875rem;
        line-height: 1.62;
        color: var(--text-2);
    }

    /* --- Steps --- */
    .steps {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        counter-reset: step;
    }
    .step {
        position: relative;
        padding: 24px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        background: linear-gradient(180deg, var(--surface) 0%, var(--surface-2) 100%);
        box-shadow: var(--shadow-sm);
    }
    .step-num {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: var(--primary);
        color: #fff;
        font-family: var(--font-display);
        font-size: 0.95rem;
        font-weight: 800;
        margin-bottom: 16px;
    }
    [data-theme="dark"] .step-num { color: #04121f; }
    .step h3 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .step p {
        font-size: 0.85rem;
        line-height: 1.6;
        color: var(--text-2);
    }

    /* --- Recent surveys --- */
    .recent-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }
    .link-arrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--primary);
    }
    .link-arrow svg { width: 15px; height: 15px; transition: transform 0.18s ease; }
    .link-arrow:hover svg { transform: translateX(3px); }

    .survey-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }
    .survey-card {
        display: flex;
        flex-direction: column;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 24px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }
    .survey-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--border-strong);
    }
    .sc-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
    }
    .sc-category {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: var(--primary);
    }
    .survey-card h3 {
        font-size: 1.06rem;
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: -0.01em;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .survey-card .sc-desc {
        font-size: 0.84rem;
        line-height: 1.6;
        color: var(--text-2);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }
    .sc-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }
    .sc-date {
        font-size: 0.75rem;
        color: var(--text-3);
    }
    .sc-cta {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--primary);
    }
    .sc-cta svg { width: 14px; height: 14px; }

    .empty-panel {
        padding: 44px 24px;
        text-align: center;
        border: 1px dashed var(--border-strong);
        border-radius: var(--radius-lg);
        background: var(--surface);
        color: var(--text-2);
        font-size: 0.9rem;
    }

    /* --- CTA --- */
    .lp-cta {
        margin-top: 72px;
        border-radius: var(--radius-xl);
        padding: 44px 40px;
        background:
            radial-gradient(circle at 88% 12%, rgba(255, 255, 255, 0.16) 0%, transparent 46%),
            linear-gradient(120deg, #0a4f8c 0%, #0b6bbd 52%, #0a5a9e 100%);
        color: #eaf4ff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 28px;
        flex-wrap: wrap;
        box-shadow: 0 30px 70px -34px rgba(10, 79, 140, 0.7);
    }
    .lp-cta h2 {
        color: #fff;
        font-size: clamp(1.5rem, 2.6vw, 1.95rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.15;
    }
    .lp-cta p {
        margin-top: 8px;
        font-size: 0.94rem;
        line-height: 1.6;
        color: rgba(234, 244, 255, 0.82);
        max-width: 52ch;
    }
    .lp-cta .btn-light {
        background: #fff;
        color: #08497f;
        padding: 13px 26px;
        font-size: 0.92rem;
        box-shadow: 0 12px 28px -12px rgba(0, 0, 0, 0.5);
    }
    .lp-cta .btn-light:hover { transform: translateY(-1px); }

    @media (max-width: 980px) {
        .lp-hero {
            grid-template-columns: 1fr;
            gap: 44px;
            padding-top: 44px;
        }
        .lp-hero__visual { max-width: 520px; }
        .feature-grid, .steps, .survey-grid { grid-template-columns: 1fr 1fr; }
        .survey-grid .survey-card:last-child { display: none; }
    }

    @media (max-width: 680px) {
        .lp-hero { padding: 32px 0 36px; gap: 32px; }
        .lp-hero h1 { font-size: clamp(2rem, 9vw, 2.6rem); }
        .lp-hero .lede { font-size: 0.98rem; }
        .lp-hero__actions .btn { width: 100%; }
        .lp-points { gap: 8px 16px; }
        .lp-stats { grid-template-columns: 1fr; }
        .stat { padding: 18px 22px; }
        .stat + .stat { border-left: 0; border-top: 1px solid var(--border); }
        .feature-grid, .steps, .survey-grid { grid-template-columns: 1fr; }
        .survey-grid .survey-card:last-child { display: flex; }
        .lp-section { padding-top: 56px; }
        .lp-cta { margin-top: 56px; padding: 32px 24px; }
        .lp-cta .btn-light { width: 100%; }
        .float-chip.fc-top { right: 0; }
        .float-chip.fc-bottom { left: 0; }
        .preview-card { padding: 18px; }
    }
</style>
@endsection

@section('content')
    <!-- Hero -->
    <section class="lp-hero">
        <div class="lp-hero__copy">
            <span class="eyebrow">Platform Resmi BPS Kabupaten Demak</span>
            <h1>Dari biodata sampai evaluasi, <span class="highlight">semuanya di sini.</span></h1>
            <p class="lede">
                Isi formulir rekrutmen petugas, ikuti kuis pelatihan, sampaikan evaluasi kegiatan, atau bantu pendataan lapangan.
                Dipakai pegawai, mitra, dan calon mitra BPS Kabupaten Demak.
            </p>
            <div class="lp-hero__actions">
                <a href="{{ route('survey.index') }}" class="btn btn-primary">
                    Lihat Form Tersedia
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 7l5 5-5 5M18 12H5"/></svg>
                </a>
                @auth
                    <a href="{{ url('/admin') }}" class="btn btn-outline">Buka Dashboard</a>
                @else
                    <a href="{{ route('filament.admin.auth.login') }}" class="btn btn-outline">Masuk ke Sistem</a>
                @endauth
            </div>
            <ul class="lp-points">
                <li>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    Terintegrasi SSO SIPETRA
                </li>
                <li>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    Bukti pengisian digital
                </li>
                <li>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    Draf tersimpan otomatis
                </li>
            </ul>
        </div>

        <div class="lp-hero__visual" aria-hidden="true">
            <div class="float-chip fc-top">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0114 0"/></svg>
                Login SSO SIPETRA
            </div>
            <div class="preview-card">
                <div class="preview-head">
                    <div class="ph-title">
                        Pratinjau Formulir
                        <span>Contoh tampilan saat mengisi form</span>
                    </div>
                    <span class="ph-pill">Aktif</span>
                </div>
                <div class="preview-question">
                    <div class="pq-label">Pertanyaan 1</div>
                    <div class="pq-text">Seberapa mudah Anda menggunakan form digital ini?</div>
                    <div class="pq-options">
                        <div class="pq-option is-selected"><span class="dot"></span> Sangat mudah</div>
                        <div class="pq-option"><span class="dot"></span> Cukup mudah</div>
                        <div class="pq-option"><span class="dot"></span> Perlu penyempurnaan</div>
                    </div>
                </div>
                <div class="preview-foot">
                    <span class="pf-note">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        Jawaban tersimpan otomatis
                    </span>
                    <span class="pf-btn">Lanjut</span>
                </div>
            </div>
            <div class="float-chip fc-bottom">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H7a2 2 0 00-2 2v16a2 2 0 002 2h10a2 2 0 002-2V7z"/><path d="M14 2v5h5M9 13h6M9 17h4"/></svg>
                Bukti pengisian
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="lp-stats" aria-label="Ringkasan platform">
        <div class="stat">
            <span class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
            </span>
            <div>
                <div class="stat-num">{{ number_format($totalSurveys) }}</div>
                <div class="stat-label">Form aktif</div>
            </div>
        </div>
        <div class="stat">
            <span class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>
            </span>
            <div>
                <div class="stat-num">{{ number_format($totalResponses) }}</div>
                <div class="stat-label">Jawaban masuk</div>
            </div>
        </div>
        <div class="stat">
            <span class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
            </span>
            <div>
                <div class="stat-num">{{ number_format($totalCategories) }}</div>
                <div class="stat-label">Kategori form</div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="lp-section">
        <div class="section-head">
            <div class="kicker">Kenapa platform ini</div>
            <h2>Satu sistem untuk berbagai kebutuhan kerja.</h2>
            <p>Mulai dari pendaftaran calon petugas, kuis pelatihan, evaluasi kegiatan, hingga pendataan lapangan.</p>
        </div>
        <div class="feature-grid">
            <article class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path d="M18.4 2.6a2 2 0 012.8 2.8L11.8 15H9v-2.8z"/></svg>
                </div>
                <h3>Formulir dinamis</h3>
                <p>Susun form dengan logika kondisional, validasi otomatis, dan berbagai tipe pertanyaan.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
                </div>
                <h3>Akses terkontrol</h3>
                <p>Buka form untuk umum, batasi dengan login, atau atur peserta melalui kelompok.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10M10 20V4M16 20v-7"/><path d="M3 20h18"/></svg>
                </div>
                <h3>Rekap dan analitik</h3>
                <p>Pantau jawaban yang masuk, lihat rekap nilai kuis, dan unduh hasilnya dalam satu tempat.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="2" width="10" height="20" rx="2.5"/><path d="M11 18h2"/></svg>
                </div>
                <h3>Nyaman di semua perangkat</h3>
                <p>Isi form dari ponsel, tablet, atau komputer. Tampilan menyesuaikan ukuran layar secara otomatis.</p>
            </article>
        </div>
    </section>

    <!-- Steps -->
    <section class="lp-section">
        <div class="section-head">
            <div class="kicker">Cara mengisi</div>
            <h2>Tiga langkah, selesai.</h2>
        </div>
        <div class="steps">
            <div class="step">
                <span class="step-num">1</span>
                <h3>Pilih form</h3>
                <p>Buka daftar form dan pilih yang ditujukan untuk Anda. Beberapa form memerlukan login SIPETRA.</p>
            </div>
            <div class="step">
                <span class="step-num">2</span>
                <h3>Isi formulir</h3>
                <p>Jawab pertanyaan per halaman. Draf jawaban tersimpan otomatis di perangkat Anda.</p>
            </div>
            <div class="step">
                <span class="step-num">3</span>
                <h3>Dapatkan bukti</h3>
                <p>Setelah terkirim, unduh tanda terima pengisian atau lihat hasil kuis Anda.</p>
            </div>
        </div>
    </section>

    <!-- Recent Surveys -->
    @if($recentSurveys->isNotEmpty())
        <section class="lp-section">
            <div class="recent-head">
                <div class="section-head" style="margin-bottom: 0;">
                    <div class="kicker">Baru dibuka</div>
                    <h2>Form terbaru</h2>
                </div>
                <a href="{{ route('survey.index') }}" class="link-arrow">
                    Lihat semua form
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 7l5 5-5 5"/></svg>
                </a>
            </div>
            <div class="survey-grid">
                @foreach($recentSurveys as $survey)
                    <a href="{{ route('survey.show', $survey) }}" class="survey-card">
                        <div class="sc-top">
                            <span class="sc-category">{{ $survey->kategori?->nama ?? 'Umum' }}</span>
                            @if($survey->is_quiz)
                                <span class="badge badge-violet">Kuis</span>
                            @endif
                        </div>
                        <h3>{{ $survey->title }}</h3>
                        <p class="sc-desc">{{ $survey->description ?: 'Silakan isi form ini sesuai petunjuk yang diberikan.' }}</p>
                        <div class="sc-meta">
                            <span class="sc-date">
                                @if($survey->ends_at)
                                    Hingga {{ $survey->ends_at->format('d M Y') }}
                                @else
                                    Tanpa batas waktu
                                @endif
                            </span>
                            <span class="sc-cta">
                                Isi form
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 7l5 5-5 5"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- CTA -->
    <section class="lp-cta">
        <div>
            <h2>Butuh mengisi form? Mulai dari sini.</h2>
            <p>Pilih form rekrutmen petugas, kuis pelatihan, evaluasi kegiatan, atau pendataan yang tersedia untuk Anda.</p>
        </div>
        <a href="{{ route('survey.index') }}" class="btn btn-light">
            Lihat form tersedia
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 7l5 5-5 5"/></svg>
        </a>
    </section>
@endsection
