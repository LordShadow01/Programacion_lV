@extends('layouts.app')

@section('title', 'Inscripciones')

@section('content')
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom border-secondary">
        <h1 class="h2 text-info fw-bold">Inscripciones</h1>
        <button class="btn btn-info-custom" data-bs-toggle="modal" data-bs-target="#createInscripcionModal">
            <i class="fa-solid fa-plus me-1"></i> Nueva Inscripción
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
            <h4 class="text-white mb-0"><i class="fa-solid fa-clipboard-list text-info me-2"></i> Listado de Inscripciones</h4>
            <form action="{{ route('inscripciones.index') }}" method="GET" class="d-flex w-100 w-md-auto">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-dark" placeholder="Buscar inscripción..." value="{{ $search ?? '' }}">
                    <button class="btn btn-info-custom" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('inscripciones.index') }}" class="btn btn-outline-secondary">
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
                            <th>Alumno</th>
                            <th>Materia</th>
                            <th>Ciclo</th>
                            <th>Año</th>
                            <th>Nota Final</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inscripciones as $inscripcion)
                            <tr>
                                <td>
                                    @if($inscripcion->alumno)
                                        <strong>{{ $inscripcion->alumno->nombre }} {{ $inscripcion->alumno->apellido }}</strong><br>
                                        <small class="text-secondary">{{ $inscripcion->alumno->codigo }}</small>
                                    @else
                                        <span class="text-danger">Alumno Eliminado</span>
                                    @endif
                                </td>
                                <td>
                                    @if($inscripcion->materia)
                                        <strong>{{ $inscripcion->materia->nombre_materia }}</strong><br>
                                        <small class="text-secondary">{{ $inscripcion->materia->codigo_materia }}</small>
                                    @else
                                        <span class="text-danger">Materia Eliminada</span>
                                    @endif
                                </td>
                                <td>{{ $inscripcion->ciclo }}</td>
                                <td>{{ $inscripcion->anio }}</td>
                                <td>
                                    <strong class="{{ $inscripcion->nota_final >= 6 ? 'text-success' : 'text-danger' }}">
                                        {{ $inscripcion->nota_final }}
                                    </strong>
                                </td>
                                <td>
                                    @if($inscripcion->estado_materia == 'Aprobada')
                                        <span class="badge bg-success">Aprobada</span>
                                    @elseif($inscripcion->estado_materia == 'Cursando')
                                        <span class="badge bg-primary">Cursando</span>
                                    @elseif($inscripcion->estado_materia == 'Reprobada')
                                        <span class="badge bg-danger">Reprobada</span>
                                    @else
                                        <span class="badge bg-secondary">Retirada</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-warning"
                                            onclick="editInscripcion({{ json_encode($inscripcion) }})" title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('inscripciones.destroy', $inscripcion->id) }}" method="POST"
                                            onsubmit="return confirm('¿Seguro que deseas eliminar esta inscripción?');">
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
                                    No hay inscripciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="createInscripcionModal" tabindex="-1" aria-labelledby="createInscripcionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg border-0">
            <div class="modal-content glass-modal shadow">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="createInscripcionModalLabel">Registrar Nueva Inscripción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('inscripciones.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Alumno *</label>
                                <select name="id_alumno" class="form-select form-control-dark" required>
                                    <option value="">-- Seleccionar Alumno --</option>
                                    @foreach($alumnos as $alumno)
                                        <option value="{{ $alumno->id }}">{{ $alumno->codigo }} - {{ $alumno->nombre }}
                                            {{ $alumno->apellido }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Materia *</label>
                                <select name="id_materia" class="form-select form-control-dark" required>
                                    <option value="">-- Seleccionar Materia --</option>
                                    @foreach($materias as $materia)
                                        <option value="{{ $materia->id }}">{{ $materia->codigo_materia }} -
                                            {{ $materia->nombre_materia }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Ciclo *</label>
                                <select name="ciclo" class="form-select form-control-dark" required>
                                    <option value="Ciclo I">Ciclo I</option>
                                    <option value="Ciclo II">Ciclo II</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Año *</label>
                                <input type="number" name="anio" class="form-control form-control-dark" required
                                    value="{{ date('Y') }}" min="2000" max="2100">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Nota Final</label>
                                <input type="number" name="nota_final" class="form-control form-control-dark" step="0.01"
                                    min="0" max="10" value="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Estado de Materia *</label>
                                <select name="estado_materia" class="form-select form-control-dark" required>
                                    <option value="Cursando">Cursando</option>
                                    <option value="Aprobada">Aprobada</option>
                                    <option value="Reprobada">Reprobada</option>
                                    <option value="Retirada">Retirada</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info-custom">
                            <i class="fa-solid fa-save me-1"></i> Guardar Inscripción
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editInscripcionModal" tabindex="-1" aria-labelledby="editInscripcionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg border-0">
            <div class="modal-content glass-modal shadow">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="editInscripcionModalLabel">Editar Inscripción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Alumno *</label>
                                <select name="id_alumno" id="edit_alumno" class="form-select form-control-dark" required>
                                    <option value="">-- Seleccionar Alumno --</option>
                                    @foreach($alumnos as $alumno)
                                        <option value="{{ $alumno->id }}">{{ $alumno->codigo }} - {{ $alumno->nombre }}
                                            {{ $alumno->apellido }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Materia *</label>
                                <select name="id_materia" id="edit_materia" class="form-select form-control-dark" required>
                                    <option value="">-- Seleccionar Materia --</option>
                                    @foreach($materias as $materia)
                                        <option value="{{ $materia->id }}">{{ $materia->codigo_materia }} -
                                            {{ $materia->nombre_materia }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Ciclo *</label>
                                <select name="ciclo" id="edit_ciclo" class="form-select form-control-dark" required>
                                    <option value="Ciclo I">Ciclo I</option>
                                    <option value="Ciclo II">Ciclo II</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Año *</label>
                                <input type="number" name="anio" id="edit_anio" class="form-control form-control-dark"
                                    required min="2000" max="2100">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Nota Final</label>
                                <input type="number" name="nota_final" id="edit_nota" class="form-control form-control-dark"
                                    step="0.01" min="0" max="10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary">Estado de Materia *</label>
                                <select name="estado_materia" id="edit_estado" class="form-select form-control-dark"
                                    required>
                                    <option value="Cursando">Cursando</option>
                                    <option value="Aprobada">Aprobada</option>
                                    <option value="Reprobada">Reprobada</option>
                                    <option value="Retirada">Retirada</option>
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
            const editModal = new bootstrap.Modal(document.getElementById('editInscripcionModal'));

            function editInscripcion(inscripcion) {
                document.getElementById('editForm').action = `/inscripciones/${inscripcion.id}`;
                document.getElementById('edit_alumno').value = inscripcion.id_alumno;
                document.getElementById('edit_materia').value = inscripcion.id_materia;
                document.getElementById('edit_ciclo').value = inscripcion.ciclo;
                document.getElementById('edit_anio').value = inscripcion.anio;
                document.getElementById('edit_nota').value = inscripcion.nota_final;
                document.getElementById('edit_estado').value = inscripcion.estado_materia;

                editModal.show();
            }
        </script>
    @endpush
@endsection