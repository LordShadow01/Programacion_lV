document.addEventListener("DOMContentLoaded", () => {
    const frmAlumnos = document.getElementById("frmAlumnos");
    const txtCodigoAlumno = document.getElementById("txtCodigoAlumno");
    const txtnombreAlumno = document.getElementById("txtnombreAlumno");
    const txtDireccionAlumno = document.getElementById("txtDireccionAlumno");
    const txtEmailAlumno = document.getElementById("txtEmailAlumno");
    const txtTelefonoAlumno = document.getElementById("txtTelefonoAlumno");

    frmAlumnos.addEventListener("submit", (e) => {
        e.preventDefault();

        let datos = {
            id: getId(),
            codigo: txtCodigoAlumno.value,
            nombre: txtnombreAlumno.value,
            direccion: txtDireccionAlumno.value,
            email: txtEmailAlumno.value,
            telefono: txtTelefonoAlumno.value
        };

        let codigoDuplicado = buscarAlumno(datos.codigo);
        if (codigoDuplicado) {
            alert("El código del alumno ya existe: " + codigoDuplicado.nombre);
            return;
        }

        localStorage.setItem(datos.id, JSON.stringify(datos));
        limpiarFormulario();
        console.log("Alumno guardado:", datos);
    });

    function getId() {
        // Genera un id único basado en la fecha y un random
        return 'alumno_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    }

    function limpiarFormulario() {
        frmAlumnos.reset();
    }

    function buscarAlumno(codigo = '') {
        for (let i = 0; i < localStorage.length; i++) {
            let key = localStorage.key(i);
            let datos = {};
            try {
                datos = JSON.parse(localStorage.getItem(key));
            } catch (e) {
                continue;
            }
            if (datos?.codigo && datos.codigo.trim().toUpperCase() === codigo.trim().toUpperCase()) {
                return datos;
            }
        }
        return null;
    }
});