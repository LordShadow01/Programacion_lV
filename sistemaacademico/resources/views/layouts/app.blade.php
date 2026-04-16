<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema Académico')</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Theme Additions -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-dark text-light">

<div class="container-fluid p-0">
    <div class="row g-0 flex-nowrap">
        <!-- Sidebar -->
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-darker border-end border-secondary sidebar-wrapper">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                <a href="/" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none mt-4 w-100">
                    <span class="fs-4 fw-bold text-info w-100 text-center text-sm-start brand-text shadow-sm">
                        <i class="fa-solid fa-graduation-cap me-2"></i><span class="d-none d-sm-inline">Académico</span>
                    </span>
                </a>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100 mt-3" id="menu">
                    <li class="nav-item w-100 mb-2">
                        <a href="{{ route('dashboard') }}" class="nav-link px-0 text-light align-middle theme-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie me-2 ms-3"></i> <span class="ms-1 d-none d-sm-inline">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item w-100 mb-2">
                        <a href="{{ route('alumnos.index') }}" class="nav-link px-0 text-light align-middle theme-nav-link {{ request()->routeIs('alumnos.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users me-2 ms-3"></i> <span class="ms-1 d-none d-sm-inline">Alumnos</span>
                        </a>
                    </li>
                    <li class="nav-item w-100 mb-2">
                        <a href="{{ route('docentes.index') }}" class="nav-link px-0 text-light align-middle theme-nav-link {{ request()->routeIs('docentes.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-chalkboard-user me-2 ms-3"></i> <span class="ms-1 d-none d-sm-inline">Docentes</span>
                        </a>
                    </li>
                    <li class="nav-item w-100 mb-2">
                        <a href="{{ route('materias.index') }}" class="nav-link px-0 text-light align-middle theme-nav-link {{ request()->routeIs('materias.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-book me-2 ms-3"></i> <span class="ms-1 d-none d-sm-inline">Materias</span>
                        </a>
                    </li>
                    <li class="nav-item w-100 mb-2">
                        <a href="{{ route('matriculas.index') }}" class="nav-link px-0 text-light align-middle theme-nav-link {{ request()->routeIs('matriculas.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-file-signature me-2 ms-3"></i> <span class="ms-1 d-none d-sm-inline">Matrículas</span>
                        </a>
                    </li>
                    <li class="nav-item w-100 mb-2">
                        <a href="{{ route('inscripciones.index') }}" class="nav-link px-0 text-light align-middle theme-nav-link {{ request()->routeIs('inscripciones.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-clipboard-list me-2 ms-3"></i> <span class="ms-1 d-none d-sm-inline">Inscripciones</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col py-4 px-4 main-bg">
            @yield('content')
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
</body>
</html>
