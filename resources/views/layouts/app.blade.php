<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --bs-body-font-size: .925rem;
        }
        body {
            min-height: 100vh;
            background: #f4f6f9;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1 0 auto;
        }
        .app-header {
            background: #155f4a;
            color: #fff;
        }
        .app-header .navbar-brand,
        .app-header .nav-link,
        .app-header .dropdown-toggle {
            color: #fff;
        }
        .app-header .nav-link:hover {
            color: #d1e7dd;
        }
        .nav-pills .nav-link.active {
            background: #155f4a;
        }
        .sidebar-card {
            position: sticky;
            top: 1rem;
        }
        .page-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        .stat-card {
            border: 0;
            border-radius: .75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .table-responsive {
            border-radius: .5rem;
        }
        .card {
            border-radius: .75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 600;
        }
        .btn-primary {
            background: #155f4a;
            border-color: #155f4a;
        }
        .btn-primary:hover,
        .btn-primary:focus {
            background: #0f4a38;
            border-color: #0f4a38;
        }
        .text-primary {
            color: #155f4a !important;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #155f4a;
            box-shadow: 0 0 0 .2rem rgba(21, 95, 74, .15);
        }
        .cal-day {
            min-height: 90px;
            border-radius: .5rem;
        }
        .cal-day .day-num {
            font-weight: 600;
        }
        .cal-day.today {
            outline: 2px solid #155f4a;
        }
        .cal-day.empty {
            background: #f9fafb;
        }
        .cal-badge {
            font-size: .68rem;
        }
        .btn-app {
            min-height: 48px;
        }
        .coleta-display {
            font-size: 3rem;
            font-weight: 700;
            text-align: center;
            color: #155f4a;
        }
        .alert-tolerancia {
            border-radius: .75rem;
        }
        .pagination .page-link {
            color: #155f4a;
        }
        .pagination .page-item.active .page-link {
            background: #155f4a;
            border-color: #155f4a;
        }
        @media (max-width: 768px) {
            .cal-day {
                min-height: 64px;
            }
            .cal-day .day-num {
                font-size: .8rem;
            }
            .coleta-display {
                font-size: 2.25rem;
            }
            .page-title {
                font-size: 1.1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @auth
    <nav class="navbar navbar-expand-lg app-header sticky-top">
        <div class="container-fluid px-3">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <i class="bi bi-basket3-fill"></i>
                <span>{{ config('app.name') }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'gerente')
                        @if(auth()->user()->role === 'admin')
                            <li class="nav-item"><a class="nav-link {{ request()->segment(1) === 'coleta' ? 'active' : '' }}" href="{{ route('coleta.create') }}"><i class="bi bi-clipboard2-check me-1"></i> Coleta</a></li>
                        @else
                            <li class="nav-item"><a class="nav-link {{ request()->segment(1) === 'coleta' ? 'active' : '' }}" href="{{ route('coleta.create') }}"><i class="bi bi-clipboard2-check me-1"></i> Coleta</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('calendario') ? 'active' : '' }}" href="{{ route('calendario') }}"><i class="bi bi-calendar3 me-1"></i> Calendário</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('relatorios.*') ? 'active' : '' }}" href="{{ route('relatorios.index') }}"><i class="bi bi-graph-up me-1"></i> Relatórios</a></li>
                    @endif
                    @if(auth()->user()->role === 'coletor')
                        <li class="nav-item"><a class="nav-link {{ request()->segment(1) === 'coletor' ? 'active' : '' }}" href="{{ route('coletor.coleta') }}"><i class="bi bi-clipboard2-check me-1"></i> Coleta</a></li>
                    @endif
                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear me-1"></i> Cadastros
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item {{ request()->routeIs('lojas.*') ? 'active' : '' }}" href="{{ route('lojas.index') }}"><i class="bi bi-shop me-2"></i>Lojas</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('balancas.*') ? 'active' : '' }}" href="{{ route('balancas.index') }}"><i class="bi bi-basket2 me-2"></i>Balancas</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-people me-2"></i>Usuários</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item {{ request()->routeIs('glpi.*') ? 'active' : '' }}" href="{{ route('glpi.config') }}"><i class="bi bi-plug me-2"></i>Configuração GLPI</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small">
                                <span class="badge bg-primary">{{ auth()->user()->role }}</span>
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endauth

    <main class="container-fluid py-3 px-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show alert-tolerancia" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show alert-tolerancia" role="alert">
                <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show alert-tolerancia" role="alert">
                <strong>Verifique os erros abaixo:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer mt-auto py-3 text-center">
        <small class="text-muted">
            {{ config('app.name') }} v{{ config('app.version') }} &middot;
            Desenvolvido por
            <a href="https://github.com/EduardoLima03" target="_blank" rel="noopener" class="text-muted">CL Dev</a>
        </small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>