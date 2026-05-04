@extends('layouts.survey')

@section('title', 'Platform Survei Digital')
@section('meta_description', 'Platform survei digital BPS Kabupaten Demak — Kumpulkan data, bangun informasi, gerakkan pembangunan.')

@section('styles')
<style>
    /* === HERO === */
    .landing-hero {
        text-align: center;
        padding: 4rem 0 3rem;
        position: relative;
    }
    .landing-hero::before {
        content: '';
        position: absolute;
        top: -64px;
        left: 50%;
        transform: translateX(-50%);
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(251,191,36,0.12) 0%, transparent 70%);
        pointer-events: none;
        z-index: -1;
    }
    .hero-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 18px;
        background: rgba(2, 132, 199, 0.1);
        border: 1px solid rgba(125, 211, 252, 0.2);
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #7dd3fc; /* Sky 300 - better on dark */
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 1.5rem;
    }
    .landing-hero h1 {
        font-size: clamp(2.25rem, 6vw, 3.5rem);
        font-weight: 800;
        line-height: 1.1;
        color: var(--text-primary);
        margin-bottom: 1rem;
    }
    .landing-hero h1 .gradient-text {
        background: linear-gradient(135deg, var(--primary), #ea580c);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .landing-hero .hero-desc {
        font-size: 1.15rem;
        color: var(--text-secondary);
        max-width: 620px;
        margin: 0 auto 2rem;
        line-height: 1.6;
    }
    .hero-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .btn-hero {
        padding: 14px 32px;
        border-radius: 14px;
        font-size: 0.95rem;
        font-weight: 600;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        cursor: pointer;
    }
    .btn-hero-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        box-shadow: 0 4px 16px rgba(217,119,6,0.25);
    }
    .btn-hero-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(217,119,6,0.35);
    }
    .btn-hero-secondary {
        background: white;
        color: #0f172a !important; /* Force dark text for visibility on white bg */
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: var(--shadow-sm);
    }
    .btn-hero-secondary:hover {
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    /* === STATS === */
    .stats-bar {
        display: flex;
        justify-content: center;
        gap: 2rem;
        padding: 2rem 0;
        flex-wrap: wrap;
    }
    .stat-item {
        text-align: center;
    }
    .stat-num {
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
    }
    .stat-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 4px;
    }

    /* === FEATURES === */
    .features-section {
        padding: 2rem 0;
    }
    .features-section h2 {
        text-align: center;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .features-section .section-desc {
        text-align: center;
        color: var(--text-secondary);
        margin-bottom: 2rem;
        font-size: 0.95rem;
    }
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.25rem;
    }
    .feature-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        transition: all 0.25s ease;
    }
    .feature-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }
    .feature-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }
    .feature-icon svg { width: 22px; height: 22px; color: white; }
    .feature-icon.amber { background: linear-gradient(135deg, #fbbf24, #d97706); }
    .feature-icon.blue { background: linear-gradient(135deg, #60a5fa, #2563eb); }
    .feature-icon.green { background: linear-gradient(135deg, #34d399, #059669); }
    .feature-icon.purple { background: linear-gradient(135deg, #a78bfa, #7c3aed); }
    .feature-card h3 {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 0.4rem;
    }
    .feature-card p {
        font-size: 0.85rem;
        color: var(--text-secondary);
        line-height: 1.55;
    }

    /* === RECENT SURVEYS === */
    .recent-section {
        padding: 3rem 0 1rem;
    }
    .recent-section h2 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .see-all {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .see-all:hover { text-decoration: underline; }

    /* Reuse card styles from index */
    .mini-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
    }
    .mini-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    .mini-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }
    .mini-card .mc-category {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--primary);
        margin-bottom: 0.4rem;
    }
    .mini-card .mc-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
    }
    .mini-card .mc-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .mini-card .mc-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .mc-btn {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .mc-btn svg { width: 14px; height: 14px; }

    @media (max-width: 768px) {
        .landing-hero { padding: 2rem 0; }
        .stats-bar { gap: 1.5rem; }
        .mini-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
    <!-- Hero -->
    <section class="landing-hero">
        <span class="hero-label">
            ◆ BPS Kabupaten Demak
        </span>
        <h1>
            Platform <span class="gradient-text">Survei Digital</span><br>
            untuk Pembangunan
        </h1>
        <p class="hero-desc">
            Kumpulkan data, bangun informasi, gerakkan pembangunan. Ikuti survei yang tersedia dan berkontribusi dalam pendataan statistik.
        </p>
        <div class="hero-actions">
            <a href="{{ route('survey.index') }}" class="btn-hero btn-hero-primary">
                Lihat Survei
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            @auth
                <a href="{{ url('/admin') }}" class="btn-hero btn-hero-secondary">
                    Dashboard Admin
                </a>
            @else
                <a href="{{ route('filament.admin.auth.login') }}" class="btn-hero btn-hero-secondary">
                    Masuk ke Sistem
                </a>
            @endauth
        </div>
    </section>

    <!-- Stats -->
    <section class="stats-bar">
        <div class="stat-item">
            <div class="stat-num">{{ $totalSurveys }}</div>
            <div class="stat-label">Survei Aktif</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ $totalResponses }}</div>
            <div class="stat-label">Jawaban Masuk</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ $totalCategories }}</div>
            <div class="stat-label">Kategori</div>
        </div>
    </section>

    <!-- Features -->
    <section class="features-section">
        <h2>Kenapa Platform Ini?</h2>
        <p class="section-desc">Teknologi modern untuk pendataan yang lebih akurat dan efisien.</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <h3>Kuesioner Dinamis</h3>
                <p>Desain survei dengan drag-and-drop: multi-halaman, logika kondisional, dan validasi otomatis.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3>Akses Terkontrol</h3>
                <p>Buka survei untuk umum, batasi dengan login, atau atur berdasarkan role pengguna.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon green">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3>Analitik Real-Time</h3>
                <p>Pantau hasil pendataan secara langsung dengan dashboard dan visualisasi data.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h3>Mobile-Friendly</h3>
                <p>Akses dan isi survei dari perangkat apapun — smartphone, tablet, atau desktop.</p>
            </div>
        </div>
    </section>

    <!-- Recent Surveys -->
    @if($recentSurveys->isNotEmpty())
    <section class="recent-section">
        <div class="section-header">
            <h2>Survei Terbaru</h2>
            <a href="{{ route('survey.index') }}" class="see-all">
                Lihat Semua
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="mini-grid">
            @foreach($recentSurveys as $survey)
                <a href="{{ route('survey.show', $survey) }}" class="mini-card">
                    <div class="mc-category">{{ $survey->kategori?->name ?? 'Umum' }}</div>
                    <div class="mc-title">{{ $survey->title }}</div>
                    <div class="mc-footer">
                        @switch($survey->mode->value)
                            @case('single')
                                <span class="mc-badge badge-blue">Sekali Isi</span>
                                @break
                            @case('editable')
                                <span class="mc-badge badge-green">Bisa Diedit</span>
                                @break
                            @case('multi')
                                <span class="mc-badge badge-purple">Multi-Submit</span>
                                @break
                        @endswitch
                        <span class="mc-btn">
                            Isi Survei
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif
@endsection
