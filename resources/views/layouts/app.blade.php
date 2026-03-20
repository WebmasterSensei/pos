<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NEXUS POS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
    @stack('styles')
</head>

<body>
    <div class="app-shell">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-mark">
                    <span class="logo-icon">⬡</span>
                </div>
                <div class="logo-text">
                    <span class="logo-name">NEXUS</span>
                    <span class="logo-sub">Point of Sale</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('pos.index') }}" class="nav-item {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                    <span class="nav-icon">◈</span>
                    <span class="nav-label">Register</span>
                </a>
                <a href="{{ route('inventory.index') }}"
                    class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <span class="nav-icon">▦</span>
                    <span class="nav-label">Inventory</span>
                </a>
                <a href="{{ route('transactions.index') }}"
                    class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                    <span class="nav-icon">◎</span>
                    <span class="nav-label">Transactions</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="status-dot"></div>
                <span class="status-text">System Online</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-button">
                    <span class="logout-icon">↪</span>
                    <span class="logout-label">Logout</span>
                </button>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
    <style scoped>
        .logout-form {
            width: 100%;
            margin: 0;
        }

        .logout-button {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: rgb(231, 231, 231);
            border: none;
            color: black;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.6rem;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }

        .logout-button:hover {
            background-color: rgb(201, 201, 201);
            transform: translateY(-1px);
        }

        .logout-button:active {
            transform: translateY(1px);
        }

        .logout-icon {
            font-size: 1.1rem;
        }
    </style>
