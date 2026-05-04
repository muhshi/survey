@extends('layouts.survey')

@section('title', 'Daftar Survei Aktif')
@section('meta_description', 'Lihat dan isi survei yang tersedia dari BPS Kabupaten Demak')

@section('styles')
<style>
    .hero {
        text-align: center;
        padding: 3rem 0 2rem;
    }
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        background: rgba(217, 119, 6, 0.1);
        border: 1px solid rgba(217, 119, 6, 0.2);
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #b45309;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 1rem;
    }
    .hero h1 {
        font-size: clamp(2rem, 5vw, 3rem);
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.15;
        margin-bottom: 0.75rem;
    }
    .hero p {
        font-size: 1.1rem;
        color: var(--text-secondary);
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Filter Bar */
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        margin: 2rem 0;
    }
    .filter-btn {
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-btn:hover, .filter-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 2px 8px rgba(217, 119, 6, 0.25);
    }

    /* Survey Grid */
    .survey-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }

    /* Survey Card */
    .survey-card {
        background: var(--surface);
        backdrop-filter: blur(10px);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        transition: all 0.3s cubic-bezier(.4,0,.2,1);
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }
    .survey-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-light), var(--primary), var(--primary-dark));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .survey-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: rgba(217, 119, 6, 0.15);
    }
    .survey-card:hover::before {
        opacity: 1;
    }
    .card-category {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }
    .card-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }
    .card-desc {
        font-size: 0.875rem;
        color: var(--text-secondary);
        line-height: 1.55;
        margin-bottom: 1rem;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1.25rem;
    }
    .card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }
    .card-date {
        font-size: 0.78rem;
        color: var(--text-muted);
    }
    .btn-fill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 22px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }
    .btn-fill:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(217, 119, 6, 0.3);
    }
    .btn-fill svg { width: 16px; height: 16px; }

    /* Access Badge */
    .access-icon {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .access-icon svg { width: 12px; height: 12px; }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }
    .empty-state .icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .empty-state .icon svg { width: 28px; height: 28px; color: var(--text-muted); }

    @media (max-width: 768px) {
        .survey-grid { grid-template-columns: 1fr; }
        .hero { padding: 2rem 0 1rem; }
    }
</style>
@endsection

@section('content')
    <!-- Hero -->
    <section class="hero">
        <span class="hero-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Platform Survei BPS
        </span>
        <h1>Survei Aktif</h1>
        <p>Ikuti survei yang tersedia dan berkontribusi dalam pengumpulan data statistik untuk pembangunan daerah.</p>
    </section>

    <!-- Category Filter -->
    @if($categories->isNotEmpty())
    <div class="filter-bar" id="filterBar">
        <button class="filter-btn active" onclick="filterSurveys('all', this)">Semua</button>
        @foreach($categories as $category)
            <button class="filter-btn" onclick="filterSurveys('{{ $category->slug }}', this)">{{ $category->name }}</button>
        @endforeach
    </div>
    @endif

    <!-- Survey Grid -->
    @if($surveys->isEmpty())
        <div class="empty-state">
            <div class="icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 style="font-family: 'Outfit'; font-size: 1.25rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">Belum Ada Survei</h3>
            <p>Saat ini belum ada survei yang aktif. Silakan cek kembali nanti.</p>
        </div>
    @else
        <div class="survey-grid">
            @foreach($surveys as $survey)
                <article class="survey-card" data-category="{{ $survey->kategori?->slug ?? 'uncategorized' }}">
                    <div class="card-category">{{ $survey->kategori?->name ?? 'Umum' }}</div>
                    <h2 class="card-title">{{ $survey->title }}</h2>
                    <p class="card-desc">{{ $survey->description ?: 'Silakan isi survei ini untuk berkontribusi dalam pengumpulan data.' }}</p>
                    <div class="card-meta">
                        @switch($survey->mode->value)
                            @case('single')
                                <span class="badge badge-blue">Sekali Isi</span>
                                @break
                            @case('editable')
                                <span class="badge badge-green">Bisa Diedit</span>
                                @break
                            @case('multi')
                                <span class="badge badge-purple">Multi-Submit</span>
                                @break
                        @endswitch

                        @if($survey->access_level === 'public')
                            <span class="badge badge-slate access-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                                Terbuka
                            </span>
                        @else
                            <span class="badge badge-amber access-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Perlu Login
                            </span>
                        @endif
                    </div>
                    <div class="card-footer">
                        <span class="card-date">
                            @if($survey->ends_at)
                                Hingga {{ $survey->ends_at->format('d M Y') }}
                            @else
                                Tanpa batas waktu
                            @endif
                        </span>
                        <a href="{{ route('survey.show', $survey) }}" class="btn-fill">
                            Isi Survei
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection

@section('scripts')
<script>
    function filterSurveys(category, btn) {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Filter cards
        document.querySelectorAll('.survey-card').forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = '';
                card.style.animation = 'fadeIn 0.3s ease';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
