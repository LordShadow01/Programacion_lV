@extends('layouts.app')

@section('title', 'Materias')

@section('content')
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom border-secondary">
        <h1 class="h2 text-info fw-bold">Materias</h1>
        <button class="btn btn-info-custom" data-bs-toggle="modal" data-bs-target="#createMateriaModal">
            <i class="fa-solid fa-plus me-1"></i> Nueva Materia
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
            <h4 class="text-white mb-0"><i class="fa-solid fa-book text-info me-2"></i> Listado de Materias</h4>
            <form action="{{ route('materias.index') }}" method="GET" class="d-flex w-100 w-md-auto">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-dark" placeholder="Buscar materia..." value="{{ $search ?? '' }}">
                    <button class="btn btn-info-custom" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('materias.index') }}" class="btn btn-outline-secondary">
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
                            <th>Nombre de Materia</th>
                            <th>Unidades Valorativas (UV)</th>
                            <th>Docente Asignado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materias as $materia)
                            <tr>
                                <td><strong>{{ $materia->codigo_materia }}</strong></td>
                                <td>{{ $materia->nombre_materia }}</td>
                                <td>{{ $materia->uv }}</td>
                                <td>
                                    @if($materia->docente)
                                        <span class="badge bg-secondary">{{ $materia->docente->nombre }}
                                            {{ $materia->docente->apellido }}</span>
                                    @else
                                        <span class="text-secondary fst-italic">Sin asignar</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-warning"
                                            onclick="editMateria({{ json_encode($materia) }})" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('materias.destroy', $materia->id) }}" method="POST"
                                            onsubmit="return confirm('¿Eliminar la materia {{ $materia->nombre_materia }}?');">
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
                                <td colspan="5" class="text-center text-secondary py-4">
                                    No hay materias registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="createMateriaModal" tabindex="-1" aria-labelledby="createMateriaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog border-0">
            <div class="modal-content glass-modal shadow">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="createMateriaModalLabel">Registrar Nueva Materia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('materias.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Código *</label>
                                <input type="text" name="codigo_materia" class="form-control form-control-dark" required
                                    placeholder="MAT-101">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Unidades Valorativas (UV) *</label>
                                <input type="number" name="uv" class="form-control form-control-dark" required min="1"
                                    value="4">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary">Nombre de la Materia *</label>
                            <input type="text" name="nombre_materia" class="form-control form-control-dark" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary">Asignar Docente</label>
                            <select name="id_docente" class="form-select form-control-dark">
                                <option value="">-- Sin asignar --</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id }}">{{ $docente->codigo }} - {{ $docente->nombre }}
                                        {{ $docente->apellido }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info-custom">
                            <i class="fa-solid fa-save me-1"></i> Guardar Materia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editMateriaModal" tabindex="-1" aria-labelledby="editMateriaModalLabel" aria-hidden="true">
        <div class="modal-dialog border-0">
            <div class="modal-content glass-modal shadow">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="editMateriaModalLabel">Editar Materia</h5>
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
                                <input type="text" name="codigo_materia" id="edit_codigo"
                                    class="form-control form-control-dark" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Unidades Valorativas (UV) *</label>
                                <input type="number" name="uv" id="edit_uv" class="form-control form-control-dark" required
                                    min="1">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary">Nombre de la Materia *</label>
                            <input type="text" name="nombre_materia" id="edit_nombre" class="form-control form-control-dark"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary">Asignar Docente</label>
                            <select name="id_docente" id="edit_docente" class="form-select form-control-dark">
                                <option value="">-- Sin asignar --</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id }}">{{ $docente->nombre }} {{ $docente->apellido }}</option>
                                @endforeach
                            </select>
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
            const editModal = new bootstrap.Modal(document.getElementById('editMateriaModal'));

            function editMateria(materia) {
                document.getElementById('editForm').action = `/materias/${materia.id}`;
                document.getElementById('edit_codigo').value = materia.codigo_materia;
                document.getElementById('edit_nombre').value = materia.nombre_materia;
                document.getElementById('edit_uv').value = materia.uv;
                document.getElementById('edit_docente').value = materia.id_docente || '';

                editModal.show();
            }
        </script>
    @endpush
@endsection