<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Form') - BPS Kabupaten Demak</title>
    <meta name="description" content="@yield('meta_description', 'Platform form digital BPS Kabupaten Demak untuk pegawai, mitra, dan calon mitra')">

    <!-- Hindari kedipan tema: terapkan tema tersimpan sebelum halaman digambar -->
    <script>
        (function () {
            var theme = 'light';
            try {
                var saved = localStorage.getItem('bps-theme');
                if (saved === 'dark' || saved === 'light') {
                    theme = saved;
                }
            } catch (e) {}
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    @yield('head')

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-display: 'Outfit', 'Inter', sans-serif;

            --bg: #f4f7fb;
            --bg-soft: #eaf1f9;
            --surface: #ffffff;
            --surface-2: #f8fafd;
            --surface-3: #eef4fb;
            --nav-bg: rgba(255, 255, 255, 0.86);

            --border: #e3eaf3;
            --border-strong: #cbd8e8;

            --text: #0f1d33;
            --text-2: #53627c;
            --text-3: #8593a9;

            --primary: #0b6bbd;
            --primary-600: #0a5a9e;
            --primary-700: #08497f;
            --primary-50: #e9f2fc;
            --primary-100: #d5e8f9;

            --accent: #f59e0b;
            --accent-600: #d97706;
            --accent-50: #fff6e5;

            --success: #0f9f6e;
            --success-50: #e6f8f0;
            --danger: #dc2626;
            --danger-50: #fdecec;

            --shadow-sm: 0 1px 2px rgba(15, 29, 51, 0.06);
            --shadow-md: 0 10px 30px -18px rgba(15, 29, 51, 0.28);
            --shadow-lg: 0 28px 70px -32px rgba(15, 29, 51, 0.35);

            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;

            color-scheme: light;
        }

        [data-theme="dark"] {
            --bg: #080f1c;
            --bg-soft: #0b1526;
            --surface: #0f1a2b;
            --surface-2: #142136;
            --surface-3: #182741;
            --nav-bg: rgba(8, 15, 28, 0.86);

            --border: #1e2c44;
            --border-strong: #2b3d5c;

            --text: #edf3ff;
            --text-2: #a7b7d0;
            --text-3: #7c8ea9;

            --primary: #4da8e6;
            --primary-600: #7cc3f2;
            --primary-700: #a6d8f9;
            --primary-50: #0e2438;
            --primary-100: #13314c;

            --accent: #fbbf24;
            --accent-600: #fcd34d;
            --accent-50: #2a2008;

            --success: #34d399;
            --success-50: #0c2b23;
            --danger: #f87171;
            --danger-50: #2b1214;

            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.35);
            --shadow-md: 0 12px 32px -18px rgba(0, 0, 0, 0.65);
            --shadow-lg: 0 34px 80px -36px rgba(0, 0, 0, 0.8);

            color-scheme: dark;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg);
            background-image:
                radial-gradient(circle at 12% -10%, var(--primary-50) 0%, transparent 42%),
                radial-gradient(circle at 92% 0%, var(--accent-50) 0%, transparent 34%);
            background-attachment: fixed;
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        h1, h2, h3, h4, h5, h6 { font-family: var(--font-display); color: var(--text); }

        a { text-decoration: none; color: inherit; }
        img, svg { display: block; max-width: 100%; }
        button { font-family: inherit; }

        :focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        .skip-link {
            position: absolute;
            left: -999px;
            top: 12px;
            z-index: 200;
            padding: 10px 16px;
            background: var(--surface);
            border: 1px solid var(--border-strong);
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .skip-link:focus { left: 16px; }

        /* === NAVBAR === */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--nav-bg);
            backdrop-filter: blur(18px) saturate(1.6);
            -webkit-backdrop-filter: blur(18px) saturate(1.6);
            border-bottom: 1px solid var(--border);
        }
        .navbar-inner {
            max-width: 1180px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            height: 68px;
            padding: 0 24px;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }
        .brand-mark {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #0b6bbd 0%, #0a4f8c 55%, #083b6b 100%);
            color: #fff;
            box-shadow: 0 6px 16px -8px rgba(11, 107, 189, 0.9);
        }
        .brand-mark svg { width: 21px; height: 21px; }
        .brand-text { display: flex; flex-direction: column; line-height: 1.1; min-width: 0; }
        .brand-text strong {
            font-family: var(--font-display);
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--text);
            white-space: nowrap;
        }
        .brand-text small {
            font-size: 0.68rem;
            font-weight: 500;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: var(--text-3);
            margin-top: 3px;
            white-space: nowrap;
        }
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .navbar-link {
            padding: 9px 14px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-2);
            transition: background-color 0.18s ease, color 0.18s ease;
        }
        .navbar-link:hover { background: var(--surface-3); color: var(--text); }

        .theme-toggle {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-2);
            cursor: pointer;
            transition: color 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
        }
        .theme-toggle:hover { color: var(--primary); border-color: var(--border-strong); }
        .theme-toggle svg { width: 18px; height: 18px; }
        .theme-toggle .icon-sun { display: none; }
        .theme-toggle .icon-moon { display: block; }
        [data-theme="dark"] .theme-toggle .icon-sun { display: block; }
        [data-theme="dark"] .theme-toggle .icon-moon { display: none; }

        /* === BUTTONS === */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            border-radius: var(--radius-sm);
            padding: 11px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.2;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
            white-space: nowrap;
        }
        .btn svg { width: 16px; height: 16px; flex-shrink: 0; }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-600) 100%);
            color: #fff;
            box-shadow: 0 8px 20px -10px rgba(11, 107, 189, 0.85);
        }
        [data-theme="dark"] .btn-primary { color: #04121f; }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 26px -12px rgba(11, 107, 189, 0.95);
        }
        .btn-outline {
            background: var(--surface);
            color: var(--text);
            border-color: var(--border-strong);
            box-shadow: var(--shadow-sm);
        }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        .btn-nav {
            padding: 9px 18px;
            font-size: 0.86rem;
            border-radius: 10px;
        }

        /* === MAIN === */
        .main-content {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 24px 72px;
        }

        /* === FOOTER === */
        .site-footer {
            border-top: 1px solid var(--border);
            background: var(--surface);
        }
        .footer-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 44px 24px 28px;
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr;
            gap: 36px;
        }
        .footer-about p {
            margin-top: 14px;
            font-size: 0.875rem;
            line-height: 1.65;
            color: var(--text-2);
            max-width: 36ch;
        }
        .footer-col h4 {
            font-family: var(--font-sans);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-3);
            margin-bottom: 14px;
        }
        .footer-col ul { list-style: none; display: grid; gap: 10px; }
        .footer-col a, .footer-col span {
            font-size: 0.875rem;
            color: var(--text-2);
            line-height: 1.5;
        }
        .footer-col a:hover { color: var(--primary); }
        .footer-bottom {
            max-width: 1180px;
            margin: 0 auto;
            padding: 18px 24px 26px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 0.78rem;
            color: var(--text-3);
        }

        /* === BADGES === */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            background: var(--surface-3);
            color: var(--text-2);
            border: 1px solid transparent;
        }
        .badge svg { width: 12px; height: 12px; }
        .badge-amber { background: var(--accent-50); color: var(--accent-600); }
        .badge-green { background: var(--success-50); color: var(--success); }
        .badge-blue { background: var(--primary-50); color: var(--primary); }
        .badge-purple { background: #f1ecff; color: #6d3bd1; }
        .badge-violet { background: #f3ebff; color: #7c3aed; }
        .badge-slate { background: var(--surface-3); color: var(--text-2); }
        [data-theme="dark"] .badge-purple { background: #241a3d; color: #c4a7ff; }
        [data-theme="dark"] .badge-violet { background: #241a3d; color: #c4a7ff; }

        @media (max-width: 860px) {
            .footer-inner { grid-template-columns: 1fr 1fr; }
            .footer-about { grid-column: 1 / -1; }
        }

        @media (max-width: 680px) {
            .navbar-inner { height: 62px; padding: 0 16px; }
            .brand-text small { display: none; }
            .navbar-link { display: none; }
            .main-content { padding: 0 16px 56px; }
            .footer-inner { padding: 36px 18px 22px; gap: 28px; }
            .footer-bottom { padding: 16px 18px 24px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.001ms !important; transition-duration: 0.001ms !important; }
            html { scroll-behavior: auto; }
        }
    </style>
    @yield('styles')
</head>
<body class="@yield('body_class')">
    <a href="#main" class="skip-link">Lewati ke konten</a>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-inner">
            <a href="{{ route('home') }}" class="navbar-brand" aria-label="Beranda Form Digital BPS Kabupaten Demak">
                <span class="brand-mark" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>
                    </svg>
                </span>
                <span class="brand-text">
                    <strong>Form Digital</strong>
                    <small>BPS Kabupaten Demak</small>
                </span>
            </a>
            <div class="navbar-actions">
                <a href="{{ route('survey.index') }}" class="navbar-link">Daftar Form</a>
                <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Ganti mode terang atau gelap" title="Ganti mode terang / gelap">
                    <svg class="icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                    <svg class="icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                    </svg>
                </button>
                @auth
                    <a href="{{ url('/admin') }}" class="btn btn-primary btn-nav">Dashboard</a>
                @else
                    <a href="{{ route('filament.admin.auth.login') }}" class="btn btn-primary btn-nav">Masuk</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="main-content" id="main">
        @yield('content')
    </main>

    <!-- Footer -->
    @unless(View::hasSection('hide_footer'))
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-about">
                <a href="{{ route('home') }}" class="navbar-brand">
                    <span class="brand-mark" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>
                        </svg>
                    </span>
                    <span class="brand-text">
                        <strong>Form Digital</strong>
                        <small>BPS Kabupaten Demak</small>
                    </span>
                </a>
                <p>Platform form resmi Badan Pusat Statistik Kabupaten Demak. Dipakai pegawai, mitra, dan calon mitra untuk rekrutmen petugas, kuis pelatihan, evaluasi kegiatan, dan pendataan lapangan.</p>
            </div>
            <div class="footer-col">
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="{{ route('survey.index') }}">Daftar Form</a></li>
                    @auth
                        <li><a href="{{ url('/admin') }}">Dashboard Admin</a></li>
                    @else
                        <li><a href="{{ route('filament.admin.auth.login') }}">Masuk ke Sistem</a></li>
                    @endauth
                </ul>
            </div>
            <div class="footer-col">
                <h4>Badan Pusat Statistik</h4>
                <ul>
                    <li><span>Kabupaten Demak</span></li>
                    <li><span>Jawa Tengah, Indonesia</span></li>
                    <li><a href="https://demakkab.bps.go.id" target="_blank" rel="noopener">demakkab.bps.go.id</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} Badan Pusat Statistik Kabupaten Demak</span>
            <span>Sistem Form Digital &bull; Didukung SurveyJS</span>
        </div>
    </footer>
    @endunless

    <script>
        (function () {
            var root = document.documentElement;
            var toggle = document.getElementById('theme-toggle');

            function currentTheme() {
                return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            }

            function syncLabel() {
                if (!toggle) return;
                var isDark = currentTheme() === 'dark';
                toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                toggle.setAttribute('title', isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
            }

            if (toggle) {
                toggle.addEventListener('click', function () {
                    var next = currentTheme() === 'dark' ? 'light' : 'dark';
                    root.setAttribute('data-theme', next);
                    try { localStorage.setItem('bps-theme', next); } catch (e) {}
                    syncLabel();
                    window.dispatchEvent(new CustomEvent('themechange', { detail: { theme: next } }));
                });
            }

            syncLabel();
        })();
    </script>
    @yield('scripts')
</body>
</html>
