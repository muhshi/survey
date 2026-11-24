@extends('layouts.survey')

@section('title', 'Daftar Form Aktif')
@section('meta_description', 'Lihat dan isi form rekrutmen, kuis, evaluasi, dan pendataan dari BPS Kabupaten Demak')

@section('styles')
<style>
    .list-hero {
        padding: 42px 0 26px;
        max-width: 680px;
    }
    .list-hero .eyebrow { margin-bottom: 16px; }
    .list-hero h1 {
        font-size: clamp(1.9rem, 4vw, 2.6rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.1;
        text-wrap: balance;
    }
    .list-hero p {
        margin-top: 12px;
        font-size: 1rem;
        line-height: 1.65;
        color: var(--text-2);
    }

    /* Toolbar */
    .list-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 14px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
    }
    .search-field {
        position: relative;
        flex: 1;
        min-width: 240px;
    }
    .search-field svg {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        width: 17px;
        height: 17px;
        color: var(--text-3);
        pointer-events: none;
    }
    .search-field input {
        width: 100%;
        padding: 12px 16px 12px 42px;
        border-radius: 11px;
        border: 1px solid var(--border);
        background: var(--surface-2);
        color: var(--text);
        font-family: inherit;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }
    .search-field input::placeholder { color: var(--text-3); }
    .search-field input:focus {
        border-color: var(--primary);
        background: var(--surface);
        box-shadow: 0 0 0 3px var(--primary-50);
    }

    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .filter-btn {
        padding: 8px 15px;
        border-radius: 9px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid transparent;
        background: transparent;
        color: var(--text-2);
        cursor: pointer;
        transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
    }
    .filter-btn:hover { background: var(--surface-3); color: var(--text); }
    .filter-btn.active {
        background: var(--primary-50);
        border-color: var(--primary-100);
        color: var(--primary);
    }

    .list-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin: 24px 0 16px;
        font-size: 0.8rem;
        color: var(--text-3);
    }
    .list-info b { color: var(--text); font-weight: 700; }

    /* Grid */
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
    .card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }
    .card-category {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: var(--primary);
    }
    .card-title {
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
    .card-desc {
        font-size: 0.84rem;
        line-height: 1.6;
        color: var(--text-2);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }
    .card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 16px;
    }
    .card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }
    .card-date {
        font-size: 0.75rem;
        color: var(--text-3);
    }
    .btn-fill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--primary);
        transition: gap 0.18s ease;
    }
    .btn-fill:hover { gap: 8px; }
    .btn-fill svg { width: 14px; height: 14px; }

    .empty-state {
        text-align: center;
        padding: 64px 24px;
        border: 1px dashed var(--border-strong);
        border-radius: var(--radius-lg);
        background: var(--surface);
    }
    .empty-state .icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 16px;
        background: var(--surface-3);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-3);
    }
    .empty-state .icon svg { width: 26px; height: 26px; }
    .empty-state h3 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .empty-state p {
        font-size: 0.875rem;
        color: var(--text-2);
        line-height: 1.6;
    }

    @media (max-width: 980px) {
        .survey-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 680px) {
        .list-hero { padding: 30px 0 20px; }
        .survey-grid { grid-template-columns: 1fr; }
        .list-toolbar { padding: 12px; }
        .filter-bar { width: 100%; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 2px; }
        .filter-btn { white-space: nowrap; }
    }
</style>
@endsection

@section('content')
    <section class="list-hero">
        <span class="eyebrow">Platform Form BPS</span>
        <h1>Form yang sedang dibuka</h1>
        <p>Pilih form rekrutmen petugas, kuis pelatihan, evaluasi kegiatan, atau pendataan lapangan. Beberapa form memerlukan akun SIPETRA.</p>
    </section>

    <div class="list-toolbar">
        <div class="search-field">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input
                type="text"
                id="surveySearchInput"
                placeholder="Cari judul atau topik form..."
                autocomplete="off"
                oninput="handleSearch(this.value)"
            >
        </div>

        @if($categories->isNotEmpty())
            <div class="filter-bar" id="filterBar">
                <button type="button" class="filter-btn active" onclick="filterSurveys('all', this)">Semua</button>
                @foreach($categories as $category)
                    <button type="button" class="filter-btn" onclick="filterSurveys('{{ $category->slug }}', this)">{{ $category->name }}</button>
                @endforeach
            </div>
        @endif
    </div>

    <div class="list-info">
        <span><b>{{ $surveys->count() }}</b> form tersedia</span>
        <span>Diperbarui {{ now()->translatedFormat('d M Y') }}</span>
    </div>

    @if($surveys->isEmpty())
        <div class="empty-state">
            <div class="icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
            </div>
            <h3>Belum ada form</h3>
            <p>Saat ini belum ada form yang dibuka. Silakan cek kembali nanti.</p>
        </div>
    @else
        <div class="survey-grid" id="surveyGrid">
            @foreach($surveys as $survey)
                <article class="survey-card" data-category="{{ $survey->kategori?->slug ?? 'uncategorized' }}" data-title="{{ strtolower($survey->title) }}" data-desc="{{ strtolower($survey->description ?? '') }}">
                    <div class="card-top">
                        <span class="card-category">{{ $survey->kategori?->nama ?? 'Umum' }}</span>
                        @if($survey->is_quiz)
                            <span class="badge badge-violet">Kuis</span>
                        @endif
                    </div>
                    <h2 class="card-title">{{ $survey->title }}</h2>
                    <p class="card-desc">{{ $survey->description ?: 'Silakan isi form ini sesuai petunjuk yang diberikan.' }}</p>

                    <div class="card-meta">
                        @switch($survey->mode->value)
                            @case('single')
                                <span class="badge badge-blue">Sekali isi</span>
                                @break
                            @case('editable')
                                <span class="badge badge-green">Bisa diedit</span>
                                @break
                            @case('multi')
                                <span class="badge badge-purple">Bisa diisi berulang</span>
                                @break
                        @endswitch

                        @if($survey->access_level === 'public')
                            <span class="badge badge-slate">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.1 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.9M8 3.9V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.1M15 20.5V18a2 2 0 012-2h3.1"/></svg>
                                Terbuka
                            </span>
                        @else
                            <span class="badge badge-amber">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
                                Perlu login
                            </span>
                        @endif

                        @if($survey->ends_at)
                            @php
                                $daysLeft = (int) now()->diffInDays($survey->ends_at, false);
                            @endphp
                            @if($daysLeft >= 0 && $daysLeft <= 3)
                                <span class="badge badge-amber">
                                    ⏳ {{ $daysLeft == 0 ? 'Hari terakhir' : $daysLeft . ' hari lagi' }}
                                </span>
                            @endif
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
                            Isi form
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 7l5 5-5 5"/></svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection

@section('scripts')
<script>
    let currentCategory = 'all';
    let searchQuery = '';

    function applyFilters() {
        const query = searchQuery.trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.survey-card').forEach(card => {
            const cat = card.dataset.category || '';
            const title = card.dataset.title || '';
            const desc = card.dataset.desc || '';

            const matchCat = (currentCategory === 'all' || cat === currentCategory);
            const matchSearch = (!query || title.includes(query) || desc.includes(query));

            if (matchCat && matchSearch) {
                card.style.display = '';
                card.style.animation = 'fadeIn 0.25s ease';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        let noResultMsg = document.getElementById('noSearchResultMsg');
        if (!noResultMsg) {
            noResultMsg = document.createElement('div');
            noResultMsg.id = 'noSearchResultMsg';
            noResultMsg.className = 'empty-state';
            noResultMsg.style.marginTop = '18px';
            noResultMsg.innerHTML = '<h3>Tidak ada hasil</h3><p>Tidak ada form yang cocok dengan pencarian atau filter Anda.</p>';
            const grid = document.getElementById('surveyGrid');
            if (grid) grid.parentNode.insertBefore(noResultMsg, grid.nextSibling);
        }
        noResultMsg.style.display = (visibleCount === 0) ? 'block' : 'none';
    }

    function filterSurveys(category, btn) {
        currentCategory = category;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        applyFilters();
    }

    function handleSearch(val) {
        searchQuery = val;
        applyFilters();
    }
</script>
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
