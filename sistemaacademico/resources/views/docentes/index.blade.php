@extends('layouts.app')

@section('title', 'Docentes')

@section('content')
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom border-secondary">
        <h1 class="h2 text-info fw-bold">Docentes</h1>
        <button class="btn btn-info-custom" data-bs-toggle="modal" data-bs-target="#createDocenteModal">
            <i class="fa-solid fa-plus me-1"></i> Nuevo Docente
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
            <h4 class="text-white mb-0"><i class="fa-solid fa-chalkboard-user text-info me-2"></i> Listado de Docentes</h4>
            <form action="{{ route('docentes.index') }}" method="GET" class="d-flex w-100 w-md-auto">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-dark" placeholder="Buscar docente..." value="{{ $search ?? '' }}">
                    <button class="btn btn-info-custom" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('docentes.index') }}" class="btn btn-outline-secondary">
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
                            <th>Docente</th>
                            <th>Especialidad</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($docentes as $docente)
                            <tr>
                                <td>{{ $docente->codigo }}</td>
                                <td>
                                    <strong>{{ $docente->nombre }} {{ $docente->apellido }}</strong>
                                </td>
                                <td>{{ $docente->especialidad ?? 'N/A' }}</td>
                                <td>{{ $docente->email ?? 'N/A' }}</td>
                                <td>{{ $docente->telefono ?? 'N/A' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-warning"
                                            onclick="editDocente({{ json_encode($docente) }})" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('docentes.destroy', $docente->id) }}" method="POST"
                                            onsubmit="return confirm('¿Seguro que deseas eliminar al docente {{ $docente->nombre }}?');">
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
                                    No hay docentes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="createDocenteModal" tabindex="-1" aria-labelledby="createDocenteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg border-0">
            <div class="modal-content glass-modal shadow">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="createDocenteModalLabel">Registrar Nuevo Docente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('docentes.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Código *</label>
                                <input type="text" name="codigo" class="form-control form-control-dark" required
                                    placeholder="00000000-0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Especialidad</label>
                                <input type="text" name="especialidad" class="form-control form-control-dark"
                                    placeholder="Ej. Matemáticas">
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
                                <input type="email" name="email" class="form-control form-control-dark"
                                    placeholder="correo@ejemplo.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Teléfono</label>
                                <input type="text" name="telefono" class="form-control form-control-dark"
                                    placeholder="0000-0000">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info-custom">
                            <i class="fa-solid fa-save me-1"></i> Guardar Docente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editDocenteModal" tabindex="-1" aria-labelledby="editDocenteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg border-0">
            <div class="modal-content glass-modal shadow">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="editDocenteModalLabel">Editar Docente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Código *</label>
                                <input type="text" name="codigo" id="edit_codigo" class="form-control form-control-dark"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Especialidad</label>
                                <input type="text" name="especialidad" id="edit_especialidad"
                                    class="form-control form-control-dark">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Nombres *</label>
                                <input type="text" name="nombre" id="edit_nombre" class="form-control form-control-dark"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Apellidos *</label>
                                <input type="text" name="apellido" id="edit_apellido" class="form-control form-control-dark"
                                    required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control form-control-dark">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Teléfono</label>
                                <input type="text" name="telefono" id="edit_telefono"
                                    class="form-control form-control-dark">
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
            const editModal = new bootstrap.Modal(document.getElementById('editDocenteModal'));

            function editDocente(docente) {
                document.getElementById('editForm').action = `/docentes/${docente.id}`;
                document.getElementById('edit_codigo').value = docente.codigo;
                document.getElementById('edit_nombre').value = docente.nombre;
                document.getElementById('edit_apellido').value = docente.apellido;
                document.getElementById('edit_email').value = docente.email || '';
                document.getElementById('edit_telefono').value = docente.telefono || '';
                document.getElementById('edit_especialidad').value = docente.especialidad || '';

                editModal.show();
            }
        </script>
    @endpush
@endsection