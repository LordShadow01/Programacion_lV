@extends('layouts.app')

@section('title', 'Alumnos')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom border-secondary">
    <h1 class="h2 text-info fw-bold">Módulo de Alumnos</h1>
    <button class="btn btn-info-custom" data-bs-toggle="modal" data-bs-target="#createAlumnoModal">
        <i class="fa-solid fa-plus me-1"></i> Nuevo Alumno
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
        <h4 class="text-white mb-0"><i class="fa-solid fa-users text-info me-2"></i> Listado de Alumnos</h4>
        <form action="{{ route('alumnos.index') }}" method="GET" class="d-flex w-100 w-md-auto">
            <div class="input-group">
                <input type="text" name="search" class="form-control form-control-dark" placeholder="Buscar alumno..." value="{{ $search ?? '' }}">
                <button class="btn btn-info-custom" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('alumnos.index') }}" class="btn btn-outline-secondary">
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
                        <th>Código</th>
                        <th>Estudiante</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Departamento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                    <tr>
                        <td>{{ $alumno->codigo }}</td>
                        <td>
                            <strong>{{ $alumno->nombre }} {{ $alumno->apellido }}</strong>
                        </td>
                        <td>{{ $alumno->email ?? 'N/A' }}</td>
                        <td>{{ $alumno->telefono ?? 'N/A' }}</td>
                        <td>{{ $alumno->departamento ?? 'N/A' }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-warning" onclick="editAlumno({{ json_encode($alumno) }})" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('alumnos.destroy', $alumno->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar a {{ $alumno->nombre }}?');">
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
                        <td colspan="6" class="text-center text-secondary py-4">
                            No hay alumnos registrados. ¡Agrega el primero!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createAlumnoModal" tabindex="-1" aria-labelledby="createAlumnoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg border-0">
        <div class="modal-content glass-modal shadow">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="createAlumnoModalLabel">Registrar Nuevo Alumno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('alumnos.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Código *</label>
                            <input type="text" name="codigo" class="form-control form-control-dark" required placeholder="0000000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Fecha Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control form-control-dark">
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Nombres *</label>
                            <input type="text" name="nombre" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Apellidos *</label>
                            <input type="text" name="apellido" class="form-control form-control-dark" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Email</label>
                            <input type="email" name="email" class="form-control form-control-dark" placeholder="correo@ejemplo.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Teléfono</label>
                            <input type="text" name="telefono" class="form-control form-control-dark" placeholder="0000-0000">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary">Departamento</label>
                        <select name="departamento" class="form-select form-control-dark">
                            <option value="">Seleccione Departamento</option>
                            <option value="Ahuachapán">Ahuachapán</option>
                            <option value="Santa Ana">Santa Ana</option>
                            <option value="Sonsonate">Sonsonate</option>
                            <option value="Chalatenango">Chalatenango</option>
                            <option value="La Libertad">La Libertad</option>
                            <option value="San Salvador">San Salvador</option>
                            <option value="Cuscatlán">Cuscatlán</option>
                            <option value="La Paz">La Paz</option>
                            <option value="Cabañas">Cabañas</option>
                            <option value="San Vicente">San Vicente</option>
                            <option value="Usulután">Usulután</option>
                            <option value="San Miguel">San Miguel</option>
                            <option value="Morazán">Morazán</option>
                            <option value="La Unión">La Unión</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary">Dirección Completa</label>
                        <input type="text" name="direccion" class="form-control form-control-dark">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info-custom"><i class="fa-solid fa-save me-1"></i> Guardar Alumno</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editAlumnoModal" tabindex="-1" aria-labelledby="editAlumnoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg border-0">
        <div class="modal-content glass-modal shadow">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="editAlumnoModalLabel">Editar Alumno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Código *</label>
                            <input type="text" name="codigo" id="edit_codigo" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Fecha Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento" class="form-control form-control-dark">
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Nombres *</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Apellidos *</label>
                            <input type="text" name="apellido" id="edit_apellido" class="form-control form-control-dark" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control form-control-dark">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Teléfono</label>
                            <input type="text" name="telefono" id="edit_telefono" class="form-control form-control-dark">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary">Departamento</label>
                        <select name="departamento" id="edit_departamento" class="form-select form-control-dark">
                            <option value="">Seleccione Departamento</option>
                            <option value="Ahuachapán">Ahuachapán</option>
                            <option value="Santa Ana">Santa Ana</option>
                            <option value="Sonsonate">Sonsonate</option>
                            <option value="Chalatenango">Chalatenango</option>
                            <option value="La Libertad">La Libertad</option>
                            <option value="San Salvador">San Salvador</option>
                            <option value="Cuscatlán">Cuscatlán</option>
                            <option value="La Paz">La Paz</option>
                            <option value="Cabañas">Cabañas</option>
                            <option value="San Vicente">San Vicente</option>
                            <option value="Usulután">Usulután</option>
                            <option value="San Miguel">San Miguel</option>
                            <option value="Morazán">Morazán</option>
                            <option value="La Unión">La Unión</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary">Dirección Completa</label>
                        <input type="text" name="direccion" id="edit_direccion" class="form-control form-control-dark">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning"><i class="fa-solid fa-save me-1"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const editModal = new bootstrap.Modal(document.getElementById('editAlumnoModal'));
    
    function editAlumno(alumno) {
        document.getElementById('editForm').action = `/alumnos/${alumno.id}`;
        document.getElementById('edit_codigo').value = alumno.codigo;
        document.getElementById('edit_nombre').value = alumno.nombre;
        document.getElementById('edit_apellido').value = alumno.apellido;
        document.getElementById('edit_email').value = alumno.email || '';
        document.getElementById('edit_telefono').value = alumno.telefono || '';
        document.getElementById('edit_departamento').value = alumno.departamento || '';
        document.getElementById('edit_direccion').value = alumno.direccion || '';
        document.getElementById('edit_fecha_nacimiento').value = alumno.fecha_nacimiento || '';
        
        editModal.show();
    }
</script>
@endpush
@endsection
