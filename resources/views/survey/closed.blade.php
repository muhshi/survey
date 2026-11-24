@extends('layouts.survey')

@section('title', 'Form Tidak Tersedia')

@section('styles')
<style>
    .closed-container {
        text-align: center;
        padding: 4rem 2rem;
        max-width: 500px;
        margin: 2rem auto;
    }
    .closed-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        background: var(--surface-3);
        border: 1px solid var(--border);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .closed-icon svg {
        width: 36px;
        height: 36px;
        color: var(--text-3);
    }
    .closed-container h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 0.75rem;
    }
    .closed-container p {
        font-size: 1rem;
        color: var(--text-2);
        margin-bottom: 2rem;
        line-height: 1.6;
    }
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        background: linear-gradient(135deg, var(--primary), var(--primary-600));
        color: white;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.3);
    }
    .btn-back:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(2, 132, 199, 0.4);
        color: white;
    }
    [data-theme="dark"] .btn-back { color: #04121f; }
    [data-theme="dark"] .btn-back:hover { color: #04121f; }
</style>
@endsection

@section('content')
<div class="closed-container">
    <div class="closed-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>
    <h1>{{ $title ?? 'Form Tidak Tersedia' }}</h1>
    <p>{{ $message ?? 'Maaf, form yang Anda cari sudah ditutup atau tidak lagi tersedia untuk diisi.' }}</p>
    <a href="{{ route('survey.index') }}" class="btn-back">
        ← Lihat Form Lainnya
    </a>
</div>
@endsection
