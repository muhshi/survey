<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Survei') - BPS Kabupaten Demak</title>
    <meta name="description" content="@yield('meta_description', 'Platform survei digital BPS Kabupaten Demak')">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    @yield('head')

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #0284c7; /* Sky 600 - BPS Blue */
            --primary-dark: #0369a1; /* Sky 700 */
            --primary-light: #7dd3fc; /* Sky 300 */
            --accent: #f59e0b; /* Amber 500 - BPS Orange */
            --text-primary: #f8fafc; /* Light for dark theme */
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --surface: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
            --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            --radius-md: 12px;
            --radius-lg: 20px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #0f172a !important;
            background-image: 
                radial-gradient(at 0% 0%, hsla(210,100%,20%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(220,100%,15%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(190,100%,20%,1) 0, transparent 50%),
                radial-gradient(at 50% 100%, hsla(220,100%,10%,1) 0, transparent 50%) !important;
            background-attachment: fixed !important;
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; }

        a { text-decoration: none; color: inherit; }

        /* === NAVBAR === */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px) saturate(1.8);
            -webkit-backdrop-filter: blur(20px) saturate(1.8);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
        }
        .navbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.15rem;
            color: var(--text-primary);
        }
        .navbar-brand .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 0.85rem;
        }
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }
        .navbar-links a {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            transition: all 0.2s ease;
        }
        .navbar-links a:hover {
            background: rgba(0,0,0,0.04);
            color: var(--text-primary);
        }
        .navbar-links .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 8px 20px;
            font-weight: 600;
        }
        .navbar-links .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
        }

        /* === MAIN CONTENT === */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* === FOOTER === */
        .site-footer {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-muted);
            font-size: 0.825rem;
        }
        .site-footer a {
            color: var(--primary);
            font-weight: 500;
        }
        .site-footer a:hover { text-decoration: underline; }

        /* === UTILITIES === */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }
        .badge-slate { background: #f1f5f9; color: #475569; }

        @media (max-width: 768px) {
            .navbar { padding: 0 1rem; }
            .main-content { padding: 1rem; }
            .navbar-links .hide-mobile { display: none; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-inner">
            <a href="/" class="navbar-brand">
                <span class="logo-icon">BPS</span>
                <span>Survei Digital</span>
            </a>
            <ul class="navbar-links">
                <li><a href="{{ route('survey.index') }}">Daftar Survei</a></li>
                @auth
                    <li><a href="{{ url('/admin') }}" class="btn-primary">Dashboard</a></li>
                @else
                    <li><a href="{{ route('filament.admin.auth.login') }}" class="btn-primary">Masuk</a></li>
                @endauth
            </ul>
        </div>
    </nav>

    <!-- Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <p>&copy; {{ date('Y') }} Badan Pusat Statistik Kabupaten Demak</p>
        <p style="margin-top: 4px;">Platform Survei Digital — Didukung oleh <a href="https://surveyjs.io" target="_blank">SurveyJS</a></p>
    </footer>

    @yield('scripts')
</body>
</html>
