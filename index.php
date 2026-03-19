<?php
// Cabeceras para habilitar el aislamiento de origen cruzado (necesario para SQLite OPFS)
header("Cross-Origin-Opener-Policy: same-origin");
header("Cross-Origin-Embedder-Policy: require-corp");
header("Cross-Origin-Resource-Policy: same-origin");
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
    <link rel="stylesheet" href="lib/bootstrap.min.css" />
    <link rel="stylesheet" href="lib/alertify.min.css"/>
    <!-- Default theme -->
    <link rel="stylesheet" href="lib/alertify_default.min.css"/>
    <!-- Semantic UI theme -->
    <link rel="stylesheet" href="lib/alertify_semantic.min.css"/>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">::.. SISTEMA ACADEMICO ..::</a>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav">
                        <a class="nav-link" href="#" @click="abrirVentana('alumnos')">Alumnos</a>
                        <a class="nav-link" href="#" @click="abrirVentana('materias')">Materias</a>
                        <a class="nav-link" href="#" @click="abrirVentana('docentes')">Docentes</a>
                        <a class="nav-link" href="#" @click="abrirVentana('matriculas')">Matriculas</a>
                        <a class="nav-link" href="#" @click="abrirVentana('inscripciones')">Inscripciones</a>
                    </div>
                </div>
            </div>
        </nav>
        <div id="appSistema" class="container-fluid" style="position: absolute; min-height: 80vh;">
            <alumnos @buscar='buscar("busqueda_alumnos","obtenerAlumnos")' @guardar='buscar("busqueda_alumnos","obtenerAlumnos")' :forms="forms" ref="alumnos" v-show="forms.alumnos.mostrar"></alumnos>
            <buscar_alumnos @modificar='modificar("alumnos","modificarAlumno", $event)' :forms="forms" ref="busqueda_alumnos" v-show="forms.busqueda_alumnos.mostrar"></buscar_alumnos>

            <materias @buscar='buscar("busqueda_materias","obtenerMaterias")' @guardar='buscar("busqueda_materias","obtenerMaterias")' :forms="forms" ref="materias" v-show="forms.materias.mostrar"></materias>
            <buscar_materias @modificar='modificar("materias","modificarMateria", $event)' :forms="forms" ref="busqueda_materias" v-show="forms.busqueda_materias.mostrar"></buscar_materias>

            <docentes @buscar='buscar("busqueda_docentes","obtenerDocentes")' @guardar='buscar("busqueda_docentes","obtenerDocentes")' :forms="forms" ref="docentes" v-show="forms.docentes.mostrar"></docentes>
            <buscar_docentes @modificar='modificar("docentes","modificarDocente", $event)' :forms="forms" ref="busqueda_docentes" v-show="forms.busqueda_docentes.mostrar"></buscar_docentes>

            <inscripciones @buscar='buscar("busqueda_inscripciones","obtenerInscripciones")' @guardar='buscar("busqueda_inscripciones","obtenerInscripciones")' :forms="forms" ref="inscripciones" v-show="forms.inscripciones.mostrar"></inscripciones>
            <buscar_inscripciones @modificar='modificar("inscripciones","modificarInscripcion", $event)' :forms="forms" ref="busqueda_inscripciones" v-show="forms.busqueda_inscripciones.mostrar"></buscar_inscripciones>

            <matriculas @buscar='buscar("busqueda_matriculas","obtenerMatriculas")' @guardar='buscar("busqueda_matriculas","obtenerMatriculas")' :forms="forms" ref="matriculas" v-show="forms.matriculas.mostrar"></matriculas>
            <buscar_matriculas @modificar='modificar("matriculas","modificarMatricula", $event)' :forms="forms" ref="busqueda_matriculas" v-show="forms.busqueda_matriculas.mostrar"></buscar_matriculas>
        </div>
    </div>
    <script src="lib/uuid.min.js"></script>
    <script src="lib/crypto-js.min.js"></script>
    <script src="lib/alertify.min.js"></script>
    <!-- CARGA DE SQLITE WASM CON OPFS -->
    <script src="sqlite3.js"></script>
    <script src="db.js"></script>
    <script src="lib/bootstrap.bundle.min.js"></script>
    <script src="lib/vue.global.js"></script>
    <script src="directivas/draggable.js"></script>
    <script src="componentes/alumnos.js"></script>
    <script src="componentes/busqueda_alumnos.js"></script>
    <script src="componentes/materias.js"></script>
    <script src="componentes/busqueda_materias.js"></script>
    <script src="componentes/docentes.js"></script>
    <script src="componentes/busqueda_docentes.js"></script>
    <script src="componentes/inscripciones.js"></script>
    <script src="componentes/busqueda_inscripciones.js"></script>
    <script src="componentes/matriculas.js"></script>
    <script src="componentes/busqueda_matriculas.js"></script>
    <script src="main.js"></script>
</body>
</html>
