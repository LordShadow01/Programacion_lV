document.addEventListener("DOMContentLoaded", () => {
    const frmAlumnos = document.getElementById("frmAlumnos");
    const txtCodigoAlumno = document.getElementById("txtCodigoAlumno");
    const txtNombreAlumno = document.getElementById("txtnombreAlumno");
    const txtDireccionAlumno = document.getElementById("txtDireccionAlumno");
    const txtEmailAlumno = document.getElementById("txtEmailAlumno");
    const txtTelefonoAlumno = document.getElementById("txtTelefonoAlumno");
    const idAlumno = document.getElementById("idAlumno");
    const tblAlumnos = document.querySelector("#tblAlumnos tbody");

    // Load initial data
    mostrarAlumnos();

    frmAlumnos.addEventListener("submit", (e) => {
        e.preventDefault();
        guardarAlumno();
    });

    // Event delegation for Edit and Delete buttons
    if (tblAlumnos) {
        tblAlumnos.addEventListener("click", (e) => {
            const target = e.target;
            // Handle Delete
            if (target.classList.contains("btn-eliminar")) {
                const id = target.dataset.id;
                eliminarAlumno(id);
            }
            // Handle Row Click (Edit) - check if it's not the delete button
            else {
                const row = target.closest("tr");
                if (row && row.dataset.id) {
                    const id = row.dataset.id;
                    cargarAlumno(id);
                }
            }
        });
    }

    // Handle "Nuevo" (Reset) button specifically if needed.
    const btnCancelar = document.getElementById("btnCancelarAlumno");
    if (btnCancelar) {
        btnCancelar.addEventListener("click", () => {
            limpiarFormulario();
        });
    }

    function guardarAlumno() {
        const codigo = txtCodigoAlumno.value.trim();
        const existingId = idAlumno.value;

        // Check for duplicates
        const duplicado = buscarAlumnoPorCodigo(codigo, existingId);
        if (duplicado) {
            return alert(`El código del alumno ya existe: ${duplicado.nombre}`);
        }

        let datos = {
            id: existingId ? existingId : getId(),
            codigo: codigo,
            nombre: txtNombreAlumno.value.trim(),
            direccion: txtDireccionAlumno.value.trim(),
            email: txtEmailAlumno.value.trim(),
            telefono: txtTelefonoAlumno.value.trim()
        };

        localStorage.setItem(datos.id, JSON.stringify(datos));
        limpiarFormulario();
        mostrarAlumnos();
    }

    function mostrarAlumnos() {
        if (!tblAlumnos) return;
        
        tblAlumnos.innerHTML = "";
        let filas = "";
        
        // Loop through all localStorage items
        for (let i = 0; i < localStorage.length; i++) {
            let key = localStorage.key(i);
            // Filter only our items (simple check if it's proper JSON and has our fields)
            try {
                let data = JSON.parse(localStorage.getItem(key));
                if (data && data.codigo && data.nombre) {
                    filas += `
                        <tr data-id="${data.id}" style="cursor: pointer;">
                            <td>${data.codigo}</td>
                            <td>${data.nombre}</td>
                            <td>${data.direccion}</td>
                            <td>${data.email}</td>
                            <td>${data.telefono}</td>
                            <td>
                                <button class="btn btn-danger btn-sm btn-eliminar" data-id="${data.id}">DEL</button>
                            </td>
                        </tr>
                    `;
                }
            } catch (e) {
                // Ignore non-JSON items in localStorage
            }
        }
        tblAlumnos.innerHTML = filas;
    }

    function cargarAlumno(id) {
        try {
            const data = JSON.parse(localStorage.getItem(id));
            if (data) {
                idAlumno.value = data.id;
                txtCodigoAlumno.value = data.codigo;
                txtNombreAlumno.value = data.nombre;
                txtDireccionAlumno.value = data.direccion;
                txtEmailAlumno.value = data.email;
                txtTelefonoAlumno.value = data.telefono;
            }
        } catch (e) {
            console.error("Error loading student:", e);
        }
    }

    function eliminarAlumno(id) {
        if (confirm("¿Estás seguro de eliminar este alumno?")) {
            localStorage.removeItem(id);
            mostrarAlumnos();
            limpiarFormulario();
        }
    }

    function buscarAlumnoPorCodigo(codigo, currentId = '') {
        for (let i = 0; i < localStorage.length; i++) {
            let key = localStorage.key(i);
            try {
                let data = JSON.parse(localStorage.getItem(key));
                // Match code but exclude current record if editing
                if (data && data.codigo && 
                    data.codigo.toUpperCase() === codigo.toUpperCase() &&
                    data.id !== currentId) {
                    return data;
                }
            } catch (e) {}
        }
        return null;
    }

    function getId() {
        return 'alumno_' + Date.now();
    }

    function limpiarFormulario() {
        frmAlumnos.reset();
        idAlumno.value = "";
    }
});
