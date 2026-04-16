@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom border-secondary">
        <h1 class="h2 text-info fw-bold">Dashboard</h1>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card glass-card stat-border h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-3">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-1 text-secondary">Total Alumnos</h6>
                        <h2 class="card-title fw-bold mb-0 text-white">{{ \App\Models\Alumno::count() }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card glass-card stat-border h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-3">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-1 text-secondary">Docentes</h6>
                        <h2 class="card-title fw-bold mb-0 text-white">{{ \App\Models\Docente::count() }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card glass-card stat-border h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-3">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-1 text-secondary">Materias</h6>
                        <h2 class="card-title fw-bold mb-0 text-white">{{ \App\Models\Materia::count() }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card glass-card stat-border h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-3">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-1 text-secondary">Inscripciones</h6>
                        <h2 class="card-title fw-bold mb-0 text-white">{{ \App\Models\Inscripcion::count() }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card glass-card">
        <div class="card-header border-secondary border-bottom-0 pt-3 pb-0">
            <h4 class="text-white"><i class="fa-solid fa-bolt text-info me-2"></i> Acciones Rápidas</h4>
        </div>
        <div class="card-body d-flex gap-3 flex-wrap">
            <a href="{{ route('alumnos.index') }}" class="btn btn-info-custom"><i class="fa-solid fa-plus me-1"></i> Nuevo
                Alumno</a>
            <a href="{{ route('matriculas.index') }}" class="btn btn-info-custom"><i
                    class="fa-solid fa-file-signature me-1"></i> Nueva Matrícula</a>
            <a href="{{ route('inscripciones.index') }}" class="btn btn-info-custom"><i
                    class="fa-solid fa-clipboard-list me-1"></i> Inscribir Materias</a>
        </div>
    </div>
@endsection