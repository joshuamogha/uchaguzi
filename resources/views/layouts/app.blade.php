<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'KKKT TEMBONI Election System') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --brand-primary: #154c79;
            --brand-secondary: #f3b61f;
            --brand-accent: #e8f1f8;
            --brand-text: #132238;
            --brand-success: #1f7a4c;
            --app-nav-height: 72px;
        }
        body {
            background: linear-gradient(180deg, #f7f7f2 0%, #eef3f7 100%);
            color: var(--brand-text);
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: .03em;
        }
        .hero-card,
        .surface-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 1rem 2rem rgba(21, 76, 121, .08);
        }
        .hero-card {
            background: linear-gradient(135deg, var(--brand-primary), #2b6c97);
            color: #fff;
        }
        .surface-card .card-header {
            background: transparent;
        }
        .candidate-photo {
            width: 100%;
            height: 220px;
            object-fit: cover;
            object-position: center center;
            border-radius: .85rem .85rem 0 0;
            background: #f1f5f9;
        }
        .candidate-avatar-sm {
            width: 56px;
            height: 56px;
            object-fit: cover;
            object-position: center center;
            border-radius: 50%;
        }
        .contest-badge {
            background: rgba(243, 182, 31, .15);
            color: #7c5800;
            font-weight: 600;
        }
        .selection-card {
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
            border-radius: 1rem;
            overflow: hidden;
            height: 100%;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .selection-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .75rem 1.5rem rgba(19, 34, 56, .08);
        }
        .selection-card.selected {
            border-color: var(--brand-success);
            box-shadow: 0 0 0 .2rem rgba(31, 122, 76, .15);
        }
        .stats-grid .card {
            border: 0;
            border-radius: 1rem;
        }
        .page-subtle {
            color: #4f6477;
        }
        .sticky-summary {
            position: sticky;
            top: 1rem;
        }
        .qr-box {
            min-height: 128px;
        }
        .admin-shell {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 1.5rem;
            align-items: start;
            min-height: calc(100vh - var(--app-nav-height));
            transition: grid-template-columns .2s ease, gap .2s ease;
        }
        .admin-shell.sidebar-collapsed {
            grid-template-columns: 0 minmax(0, 1fr);
            gap: 0;
        }
        .admin-sidebar {
            position: sticky;
            top: 0;
            height: calc(100vh - var(--app-nav-height));
            background: #000000;
            border-radius: 0;
            box-shadow: none;
            overflow-y: auto;
            overflow-x: hidden;
            transition: opacity .2s ease, transform .2s ease;
        }
        .admin-shell.sidebar-collapsed .admin-sidebar {
            opacity: 0;
            pointer-events: none;
            transform: translateX(-18px);
        }
        .admin-sidebar .sidebar-header {
            background: #000000;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }
        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, .82);
            border-radius: .75rem;
            padding: .75rem .9rem;
            font-weight: 500;
        }
        .admin-sidebar .nav-link:hover {
            background: rgba(255, 255, 255, .08);
            color: #ffffff;
        }
        .admin-sidebar .nav-link.active {
            background: rgba(255, 255, 255, .16);
            color: #ffffff;
        }
        .sidebar-section-title {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: rgba(255, 255, 255, .45);
            margin-bottom: .75rem;
        }
        .admin-sidebar .text-muted {
            color: rgba(255, 255, 255, .58) !important;
        }
        .sidebar-user-card {
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 1rem;
        }
        .sidebar-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .14);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        .admin-content {
            padding: 1.5rem 1.5rem 1.5rem 0;
        }
        .admin-content-toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }
        .sidebar-toggle-btn {
            border: 1px solid rgba(19, 34, 56, .12);
            background: #ffffff;
            color: var(--brand-text);
            border-radius: 999px;
            padding: .55rem .95rem;
            font-weight: 600;
            line-height: 1;
        }
        .sidebar-toggle-btn:hover {
            background: #f5f8fb;
        }
        @media (max-width: 991.98px) {
            .admin-shell {
                grid-template-columns: 1fr;
            }
            .admin-shell.sidebar-collapsed {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .admin-sidebar {
                position: static;
                height: auto;
            }
            .admin-shell.sidebar-collapsed .admin-sidebar {
                display: none;
            }
            .admin-content {
                padding: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand text-primary" href="{{ route('home') }}">KKKT TEMBINI</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('vote.verify.form') }}">Vote</a></li>
                @auth
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>
                @endauth
            </ul>
            <div class="d-flex align-items-center gap-2">
                @auth
                    <span class="small text-muted">{{ auth()->user()->email }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm" type="submit">Logout</button>
                    </form>
                @else
                    <a class="btn btn-primary btn-sm" href="{{ route('login') }}"> Login</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

@php
    $isAdminArea = auth()->check() && request()->routeIs('admin.*');
    $currentElection = request()->route('election');
    $isAdminUser = auth()->check() && auth()->user()->isAdmin();
@endphp

<main class="{{ $isAdminArea ? 'py-0' : 'py-4 py-lg-5' }}">
    <div class="{{ $isAdminArea ? 'container-fluid px-0' : 'container' }}">
        @if ($isAdminArea)
            <div class="admin-shell" id="adminShell">
                <aside class="admin-sidebar">
                    <div class="sidebar-header p-4">
                        <div class="small text-uppercase fw-semibold opacity-75 mb-2">Administration</div>
                        <div class="h5 mb-1">Control Panel</div>
                        {{-- <div class="small opacity-75">Manage master data, elections, voter lists, and reports.</div> --}}
                    </div>
                    <div class="p-3">
                        <div class="sidebar-user-card p-3 mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="sidebar-avatar">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-white text-truncate">{{ auth()->user()->name }}</div>
                                    <div class="small text-muted text-truncate">{{ auth()->user()->email }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="sidebar-section-title">Core</div>
                        <nav class="nav flex-column gap-1 mb-4">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                            <a class="nav-link {{ request()->routeIs('admin.elections.*') ? 'active' : '' }}" href="{{ route('admin.elections.index') }}">Elections</a>
                            @if ($isAdminUser)
                                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Users</a>
                                <a class="nav-link {{ request()->routeIs('admin.communities.*') ? 'active' : '' }}" href="{{ route('admin.communities.index') }}">Communities</a>
                                <a class="nav-link {{ request()->routeIs('admin.church-groups.*') ? 'active' : '' }}" href="{{ route('admin.church-groups.index') }}">Church Groups</a>
                                <a class="nav-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}" href="{{ route('admin.members.index') }}">Members</a>
                            @endif
                        </nav>

                        @if ($currentElection)
                            <div class="sidebar-section-title">Current Election</div>
                            <div class="small text-muted mb-3">{{ $currentElection->title }}</div>
                            <nav class="nav flex-column gap-1 mb-4">
                                <a class="nav-link {{ request()->routeIs('admin.elections.results.manual-entry') ? 'active' : '' }}" href="{{ route('admin.elections.results.manual-entry', $currentElection) }}">Manual Ballot Entry</a>
                                <a class="nav-link" href="{{ route('public.elections.candidates', $currentElection) }}" target="_blank">Public Candidate Page</a>
                                <a class="nav-link" href="{{ route('admin.elections.candidates.export-sheet', $currentElection) }}" target="_blank">Candidate Sheet</a>
                                <a class="nav-link" href="{{ route('admin.elections.candidates.export-contest-pdf', $currentElection) }}" target="_blank">Candidate List PDF</a>
                                @if ($isAdminUser)
                                    <a class="nav-link {{ request()->routeIs('admin.elections.contests.*') ? 'active' : '' }}" href="{{ route('admin.elections.contests.index', $currentElection) }}">Contests</a>
                                    <a class="nav-link {{ request()->routeIs('admin.elections.candidates.*') ? 'active' : '' }}" href="{{ route('admin.elections.candidates.index', $currentElection) }}">Candidates</a>
                                    <a class="nav-link {{ request()->routeIs('admin.elections.voters.*') ? 'active' : '' }}" href="{{ route('admin.elections.voters.index', $currentElection) }}">Voters</a>
                                    @can('viewResults', $currentElection)
                                        <a class="nav-link {{ request()->routeIs('admin.elections.results.*') ? 'active' : '' }}" href="{{ route('admin.elections.results.index', $currentElection) }}">Results Report</a>
                                        <a class="nav-link" href="{{ route('public.elections.results', $currentElection) }}" target="_blank">Public Results Page</a>
                                    @endcan
                                @endif
                            </nav>
                        @endif

                        @if ($isAdminUser)
                            <div class="sidebar-section-title">Shortcuts</div>
                            <nav class="nav flex-column gap-1">
                                <a class="nav-link" href="{{ route('admin.elections.create') }}">Create Election</a>
                                <a class="nav-link" href="{{ route('admin.members.create') }}">Add Member</a>
                                <a class="nav-link" href="{{ route('admin.communities.create') }}">Add Community</a>
                            </nav>
                        @endif

                        <div class="sidebar-section-title mt-4">Account</div>
                        <nav class="nav flex-column gap-1">
                            <a class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}">My Profile</a>
                        </nav>
                    </div>
                </aside>

                <div class="admin-content">
                    <div class="admin-content-toolbar">
                        <button class="sidebar-toggle-btn" type="button" id="sidebarToggle" aria-expanded="true">
                            Hide Sidebar
                        </button>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please fix the following issues:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        @else
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following issues:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        @endif
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@if ($isAdminArea)
    <script>
        (() => {
            const adminShell = document.getElementById('adminShell');
            const sidebarToggle = document.getElementById('sidebarToggle');

            if (!adminShell || !sidebarToggle) {
                return;
            }

            const storageKey = 'uchaguzi-admin-sidebar-collapsed';
            const applySidebarState = (collapsed) => {
                adminShell.classList.toggle('sidebar-collapsed', collapsed);
                sidebarToggle.textContent = collapsed ? 'Show Sidebar' : 'Hide Sidebar';
                sidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            };

            const storedState = window.localStorage.getItem(storageKey) === 'true';
            applySidebarState(storedState);

            sidebarToggle.addEventListener('click', () => {
                const collapsed = !adminShell.classList.contains('sidebar-collapsed');
                applySidebarState(collapsed);
                window.localStorage.setItem(storageKey, collapsed ? 'true' : 'false');
            });
        })();
    </script>
@endif
@stack('scripts')
</body>
</html>
