@extends('layouts.app')

@section('title', 'Matrículas')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom border-secondary">
    <h1 class="h2 text-info fw-bold">Gestión de Matrículas</h1>
    <button class="btn btn-info-custom" data-bs-toggle="modal" data-bs-target="#createMatriculaModal">
        <i class="fa-solid fa-plus me-1"></i> Nueva Matrícula
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card glass-card">
    <div class="card-header border-secondary border-bottom pt-3 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <h4 class="text-white mb-0"><i class="fa-solid fa-file-signature text-info me-2"></i> Historial de Matrículas</h4>
        <form action="{{ route('matriculas.index') }}" method="GET" class="d-flex w-100 w-md-auto">
            <div class="input-group">
                <input type="text" name="search" class="form-control form-control-dark" placeholder="Buscar matrícula..." value="{{ $search ?? '' }}">
                <button class="btn btn-info-custom" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('matriculas.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
    
    <div class="card-body px-0 px-md-3">
        <div class="table-responsive">
            <table class="table table-dark-custom align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Alumno</th>
                        <th>Año</th>
                        <th>Ciclo</th>
                        <th>Fecha de Pago</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matriculas as $matricula)
                    <tr>
                        <td>#{{ str_pad($matricula->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            @if($matricula->alumno)
                                <strong>{{ $matricula->alumno->nombre }} {{ $matricula->alumno->apellido }}</strong><br>
                                <small class="text-secondary">{{ $matricula->alumno->codigo }}</small>
                            @else
                                <span class="text-danger">Alumno Eliminado</span>
                            @endif
                        </td>
                        <td>{{ $matricula->anio_academico }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $matricula->ciclo }}</span>
                        </td>
                        <td>{{ $matricula->fecha_pago ? \Carbon\Carbon::parse($matricula->fecha_pago)->format('d/m/Y') : 'N/A' }}</td>
                        <td>
                            @if($matricula->estado == 'Pagado')
                                <span class="badge bg-success">Pagado</span>
                            @elseif($matricula->estado == 'Pendiente')
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            @else
                                <span class="badge bg-danger">Retirado</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-warning" onclick="editMatricula({{ json_encode($matricula) }})" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('matriculas.destroy', $matricula->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar el registro de matrícula?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">
                            No hay matrículas registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createMatriculaModal" tabindex="-1" aria-labelledby="createMatriculaModalLabel" aria-hidden="true">
    <div class="modal-dialog border-0">
        <div class="modal-content glass-modal shadow">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="createMatriculaModalLabel">Registrar Matrícula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('matriculas.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Alumno *</label>
                        <select name="id_alumno" class="form-select form-control-dark" required>
                            <option value="">-- Seleccionar Alumno --</option>
                            @foreach($alumnos as $alumno)
                                <option value="{{ $alumno->id }}">{{ $alumno->codigo }} - {{ $alumno->nombre }} {{ $alumno->apellido }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Año Académico *</label>
                            <input type="number" name="anio_academico" class="form-control form-control-dark" required value="{{ date('Y') }}" min="2000" max="2100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Ciclo *</label>
                            <select name="ciclo" class="form-select form-control-dark" required>
                                <option value="Ciclo I">Ciclo I</option>
                                <option value="Ciclo II">Ciclo II</option>
                                <option value="Ciclo Extraordinario">Ciclo Extraordinario</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Fecha de Pago</label>
                            <input type="date" name="fecha_pago" class="form-control form-control-dark">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Estado Financiero *</label>
                            <select name="estado" class="form-select form-control-dark" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Pagado">Pagado</option>
                                <option value="Retirado">Retirado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info-custom">
                        <i class="fa-solid fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editMatriculaModal" tabindex="-1" aria-labelledby="editMatriculaModalLabel" aria-hidden="true">
    <div class="modal-dialog border-0">
        <div class="modal-content glass-modal shadow">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="editMatriculaModalLabel">Editar Matrícula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Alumno *</label>
                        <select name="id_alumno" id="edit_alumno" class="form-select form-control-dark" required>
                            <option value="">-- Seleccionar Alumno --</option>
                            @foreach($alumnos as $alumno)
                                <option value="{{ $alumno->id }}">{{ $alumno->codigo }} - {{ $alumno->nombre }} {{ $alumno->apellido }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Año Académico *</label>
                            <input type="number" name="anio_academico" id="edit_anio" class="form-control form-control-dark" required min="2000" max="2100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Ciclo *</label>
                            <select name="ciclo" id="edit_ciclo" class="form-select form-control-dark" required>
                                <option value="Ciclo I">Ciclo I</option>
                                <option value="Ciclo II">Ciclo II</option>
                                <option value="Ciclo Extraordinario">Ciclo Extraordinario</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Fecha de Pago</label>
                            <input type="date" name="fecha_pago" id="edit_fecha_pago" class="form-control form-control-dark">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Estado Financiero *</label>
                            <select name="estado" id="edit_estado" class="form-select form-control-dark" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Pagado">Pagado</option>
                                <option value="Retirado">Retirado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-save me-1"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const editModal = new bootstrap.Modal(document.getElementById('editMatriculaModal'));

    function editMatricula(matricula) {
        document.getElementById('editForm').action = `/matriculas/${matricula.id}`;
        document.getElementById('edit_alumno').value = matricula.id_alumno;
        document.getElementById('edit_anio').value = matricula.anio_academico;
        document.getElementById('edit_ciclo').value = matricula.ciclo;
        
        let fecha = matricula.fecha_pago ? matricula.fecha_pago.split(' ')[0] : '';
        document.getElementById('edit_fecha_pago').value = fecha;
        
        document.getElementById('edit_estado').value = matricula.estado;
        
        editModal.show();
    }
</script>
@endpush
@endsection
