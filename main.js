var accion = 'nuevo',
    id = 0;

document.addEventListener("DOMContentLoaded", () => {
    // Definir referencias a elementos
    let txtBuscarAlumno = document.getElementById('txtBuscarAlumno');
    let frmAlumnos = document.getElementById('frmAlumnos');
    
    txtBuscarAlumno.addEventListener("keyup", (e) => {
        mostrarAlumnos(e.target.value);
    });
    frmAlumnos.addEventListener("submit", (e) => {
        e.preventDefault();
        guardarAlumno();
    });
    frmAlumnos.addEventListener("reset", (e) => {
        limpiarFormulario();
    });
    mostrarAlumnos();
});

function mostrarAlumnos(buscar=''){
    let $tblAlumnos = document.querySelector("#tblAlumnos tbody"),
        n = localStorage.length,
        filas = "";
    $tblAlumnos.innerHTML = "";
    
    for(let i=0; i<n; i++){
        let key = localStorage.key(i);
        // Verificar que la clave sea numérica (nuestro ID)
        if( Number(key) ){
            try {
                let data = JSON.parse(localStorage.getItem(key));
                // Filtro de búsqueda
                if( (data.nombre && data.nombre.toUpperCase().includes(buscar.toUpperCase())) || 
                    (data.codigo && data.codigo.toUpperCase().includes(buscar.toUpperCase())) ){
                    
                    filas += `
                        <tr onclick='modificarAlumno(${JSON.stringify(data)})'>
                            <td>${data.codigo}</td>
                            <td>${data.nombre}</td>
                            <td>${data.direccion}</td>
                            <td>${data.email}</td>
                            <td>${data.telefono}</td>
                            <td>
                                <button class="btn btn-danger" onclick='eliminarAlumno(${data.id}, event)'>DEL</button>
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error("Error al leer dato:", error);
            }
        }
    }
    $tblAlumnos.innerHTML = filas;
}

function eliminarAlumno(id, e){
    e.stopPropagation();
    if(confirm("¿Está seguro de eliminar el alumno?")){
        localStorage.removeItem(id);
        mostrarAlumnos();
    }
}

function modificarAlumno(alumno){
    accion = 'modificar';
    id = alumno.id;
    document.getElementById('txtCodigoAlumno').value = alumno.codigo;
    document.getElementById('txtnombreAlumno').value = alumno.nombre;
    document.getElementById('txtDireccionAlumno').value = alumno.direccion;
    document.getElementById('txtEmailAlumno').value = alumno.email;
    document.getElementById('txtTelefonoAlumno').value = alumno.telefono;
}

function guardarAlumno() {
    let txtCodigoAlumno = document.getElementById('txtCodigoAlumno');
    let txtnombreAlumno = document.getElementById('txtnombreAlumno');
    let txtDireccionAlumno = document.getElementById('txtDireccionAlumno');
    let txtEmailAlumno = document.getElementById('txtEmailAlumno');
    let txtTelefonoAlumno = document.getElementById('txtTelefonoAlumno');

    let datos = {
        id: accion == 'modificar' ? id : getId(),
        codigo: txtCodigoAlumno.value,
        nombre: txtnombreAlumno.value,
        direccion: txtDireccionAlumno.value,
        email: txtEmailAlumno.value,
        telefono: txtTelefonoAlumno.value
    };

    let codigoDuplicado = buscarAlumno(datos.codigo);
    
    if(codigoDuplicado){
        if(accion == 'nuevo'){
            alert("El codigo del alumno ya esiste: " + codigoDuplicado.nombre);
            return;
        }
        // Si es modificar, verificar que no choque con otro ID distinto
        if(accion == 'modificar' && codigoDuplicado.id != id){
            alert("El codigo del alumno ya existe en otro registro: " + codigoDuplicado.nombre);
            return;
        }
    }

    localStorage.setItem(datos.id, JSON.stringify(datos));
    limpiarFormulario();
    mostrarAlumnos();
}

function getId(){
    return new Date().getTime();
}

function limpiarFormulario(){
    // Resetear formulario HTML
    document.getElementById('frmAlumnos').reset();
    
    // Resetear variables de estado
    accion = 'nuevo';
    id = 0;
    
    // Asegurar que los inputs estén vacíos (el reset debería hacerlo, pero por si acaso)
    document.getElementById('txtCodigoAlumno').value = '';
    document.getElementById('txtnombreAlumno').value = '';
    document.getElementById('txtDireccionAlumno').value = '';
    document.getElementById('txtEmailAlumno').value = '';
    document.getElementById('txtTelefonoAlumno').value = '';
}

function buscarAlumno(codigo=''){
    let n = localStorage.length;
    for(let i = 0; i < n; i++){
        let key = localStorage.key(i);
        if(Number(key)){
            try {
                let datos = JSON.parse(localStorage.getItem(key));
                if(datos?.codigo && datos.codigo.trim().toUpperCase() == codigo.trim().toUpperCase()){
                    return datos;
                }
            } catch(e){}
        }
    }
    return null;
}
