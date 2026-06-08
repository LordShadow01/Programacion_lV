window.permisoNotificaciones = false;
document.addEventListener('DOMContentLoaded', event => {
    solicitarPermisoNotificaciones();

    document.getElementById('txtMensaje').addEventListener('keyup', (event) => {
        event.preventDefault();

        if (event.key === 'Enter') {
            enviarMensaje();
        }
    });
    frmAlumno.addEventListener('submit', (event) => {
        event.preventDefault();
        guardarAlumno();
    });
    txtBuscarAlumno.addEventListener('keyup', (event) => {
        event.preventDefault();
        listarAlumnos();
    });
    listarAlumnos();

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('Service Worker registrado con éxito:', registration);
                let opciones = {
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('BCJoKOi4Ypa9fc0CdJ6EdE9vR2l9SOqO30_0ZWdiZj1W2k2CDbZMqcr_QY0kRIVFkaSEBvoPNgyDRGolAg-KhkM')
                };
                registration.pushManager.subscribe(opciones)
                    .then(subscription => {
                        console.log('Suscripción exitosa:', subscription);
                        fetch('/api/suscripcion', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(subscription)
                        })
                        .then(res => res.json())
                        .then(resData => console.log('Suscripción guardada en el servidor:', resData))
                        .catch(err => console.error('Error al guardar la suscripción en el servidor:', err));
                    })
                    .catch(error => {
                        console.log('Error al suscribirse:', error);
                    });
            })
            .catch(error => {
                console.log('Error al registrar el Service Worker:', error);
            });
    }
});

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4),
        base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/'),
        binaryString = atob(base64),
        bytes = new Uint8Array(binaryString.length);
    for (let i = 0; i < binaryString.length; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes;
}


async function listarAlumnos() {
    let resp = await fetch(`/api/alumnos?buscar=${txtBuscarAlumno.value}`),
        alumnos = await resp.json();
    tbodyAlumnos.innerHTML = '';
    let filas = '';
    alumnos.forEach(alumno => {
        filas += `
            <tr onclick='modificarAlumno(${JSON.stringify(alumno)})'>
                <td>${alumno.codigo}</td>
                <td>${alumno.nombre}</td>
                <td>${alumno.direccion}</td>
                <td>${alumno.telefono}</td>
                <td>${alumno.email}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="eliminarAlumno('${alumno._id}', event)">Eliminar</button>
                </td>
            </tr>
        `;
    });
    tbodyAlumnos.innerHTML = filas;
}
async function eliminarAlumno(idalumno, event) {
    event.stopPropagation();
    if (!confirm("¿Estas seguro de eliminar el alumno?")) {
        return;
    }
    let resp = await fetch(`/api/alumnos/${idalumno}`, {
        method: 'DELETE'
    });
    let msg = await resp.json();
    if (msg.acknowledged === false) {
        alertify.error(`Error al eliminar el alumno: ${msg}`);
        return;
    }
    frmAlumno.reset();
    frmAlumno.dataset.accion = 'nuevo';
    frmAlumno.dataset.idalumno = '';
    listarAlumnos();
}
function modificarAlumno(alumno) {
    frmAlumno.dataset.accion = 'modificar';
    frmAlumno.dataset.idalumno = alumno._id;
    txtCodigoAlumno.value = alumno.codigo;
    txtNombreAlumno.value = alumno.nombre;
    txtDireccionAlumno.value = alumno.direccion;
    txtTelefonoAlumno.value = alumno.telefono;
    txtEmailAlumno.value = alumno.email;
}

async function guardarAlumno() {
    let data = {
        idalumno: frmAlumno.dataset.idalumno,
        codigo: txtCodigoAlumno.value,
        nombre: txtNombreAlumno.value,
        direccion: txtDireccionAlumno.value,
        telefono: txtTelefonoAlumno.value,
        email: txtEmailAlumno.value
    }, metodo = 'POST';
    if (frmAlumno.dataset.accion === 'modificar') {
        metodo = 'PUT';
    }
    let resp = await fetch('/api/alumnos', {
        method: metodo,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });
    let msg = await resp.json();
    if (msg.acknowledged === false) {
        alertify.error(`Error al guardar el alumno: ${msg}`);
        return;
    }
    frmAlumno.reset();
    frmAlumno.dataset.accion = 'nuevo';
    frmAlumno.dataset.idalumno = '';
    listarAlumnos();
}

function agregarMensaje(data) {
    const ul = document.getElementById('ulMensajes'),
        li = document.createElement('li');
    li.innerText = data.titulo + ': ' + data.mensaje;
    ul.appendChild(li);
}
function enviarMensaje() {
    let data = {
        titulo: 'Luis Hernandez',
        mensaje: txtMensaje.value
    };
    socket.emit('mensajeRecibido', data);
    txtMensaje.value = '';
    //agregarMensaje(data);
}
const socket = io.connect('http://localhost:3000', { 'forceNew': true, 'transports': ['websocket', 'polling'] });
socket.on('connect', () => {
    document.getElementById('estado').innerText = 'Conectado';
});
socket.on('disconnect', () => {
    document.getElementById('estado').innerText = 'Desconectado';
});
socket.on('mensajeEnviar', (data) => {
    crearNotificacionLocal(data.titulo, data.mensaje);
    agregarMensaje(data);
});

function solicitarPermisoNotificaciones() {
    if (Notification.permission === 'granted') {
        window.permisoNotificaciones = true;
        console.log('Permiso concedido');
    } else {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                window.permisoNotificaciones = true;
                console.log('Permiso concedido: ', permission);
            } else {
                window.permisoNotificaciones = false;
                console.log('Permiso denegado: ', permission);
            }
        });
    }
}
function crearNotificacionLocal(titulo = '', mensaje = '') {
    /*if (!window.permisoNotificaciones) {
        console.log('Permiso de notificaciones denegado, o no concedido.');
        return;
    }
    let notificacion = new Notification(titulo, {
        body: mensaje,
        icon: 'https://avatars.githubusercontent.com/u/938710?v=4'
    });
    notificacion.onclick = () => {
        window.open('https://github.com/luishernandezpw/PrograIV-Semi-2026', '_blank');
        notificacion.close();
    };
    setTimeout(() => {
        notificacion.close();
    }, 5000);*/
}