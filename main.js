const { createApp, ref, computed, onMounted } = Vue;

const app = createApp({
    setup() {
        // Estado reactivo
        const alumnos = ref([]);
        const busqueda = ref('');
        const accion = ref('nuevo');
        
        // Objeto inicial para el formulario
        const alumnoInicial = {
            id: null,
            codigo: '',
            nombre: '',
            direccion: '',
            municipio: '',
            departamento: '',
            telefono: '',
            fechaNacimiento: '',
            sexo: ''
        };
        
        const alumno = ref({ ...alumnoInicial });

        // Cargar datos al iniciar ...
        onMounted(() => {
            obtenerAlumnos();
        });

        const obtenerAlumnos = () => {
            alumnos.value = [];
            const n = localStorage.length;
            for (let i = 0; i < n; i++) {
                const key = localStorage.key(i);
                if (!isNaN(key)) { // Filtrar solo claves numéricas (IDs)
                    try {
                        const data = JSON.parse(localStorage.getItem(key));
                        alumnos.value.push(data);
                    } catch (e) {
                        console.error('Error parsing student data', e);
                    }
                }
            }
        };

        const guardarAlumno = () => {
            // Validación básica de código duplicado
            const existeCodigo = alumnos.value.find(a => a.codigo === alumno.value.codigo && a.id !== alumno.value.id);
            if (existeCodigo) {
                alert(`El código ${alumno.value.codigo} ya está registrado a nombre de ${existeCodigo.nombre}.`);
                return;
            }

            if (accion.value === 'nuevo') {
                alumno.value.id = new Date().getTime();
            }

            // Guardar en localStorage
            localStorage.setItem(alumno.value.id, JSON.stringify(alumno.value));
            
            // Actualizar lista y limpiar
            obtenerAlumnos();
            resetForm();
        };

        const eliminarAlumno = (id) => {
            if (confirm('¿Está seguro de eliminar este alumno?')) {
                localStorage.removeItem(id);
                obtenerAlumnos();
                if (alumno.value.id === id) {
                    resetForm();
                }
            }
        };

        const seleccionarAlumno = (item) => {
            alumno.value = { ...item };
            accion.value = 'modificar';
        };

        const resetForm = () => {
            alumno.value = { ...alumnoInicial };
            accion.value = 'nuevo';
        };

        // Propiedad computada para búsqueda
        const alumnosFiltrados = computed(() => {
            if (!busqueda.value) return alumnos.value;
            const term = busqueda.value.toLowerCase();
            return alumnos.value.filter(a => 
                a.codigo.toLowerCase().includes(term) || 
                a.nombre.toLowerCase().includes(term)
            );
        });

        return {
            alumno,
            alumnos,
            accion,
            busqueda,
            guardarAlumno,
            eliminarAlumno,
            seleccionarAlumno,
            resetForm,
            alumnosFiltrados
        };
    }
});

app.mount('#app');
