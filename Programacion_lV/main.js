const { createApp } = Vue,
    sha256 = CryptoJS.SHA256;

// SQLite + OPFS se inicializa automáticamente en db.js
// El esquema está gestionado en el worker.

const app = createApp({
    components:{
        alumnos,
        buscar_alumnos: busqueda_alumnos,
        materias,
        buscar_materias: busqueda_materias,
        docentes,
        buscar_docentes: busqueda_docentes,
        inscripciones,
        buscar_inscripciones: busqueda_inscripciones,
        matriculas,
        buscar_matriculas: busqueda_matriculas
    },
    data(){
        return{
            forms:{
                alumnos:{mostrar:false},
                busqueda_alumnos:{mostrar:false},
                materias:{mostrar:false},
                busqueda_materias:{mostrar:false},
                docentes:{mostrar:false},
                busqueda_docentes:{mostrar:false},
                matriculas:{mostrar:false},
                busqueda_matriculas:{mostrar:false},
                inscripciones:{mostrar:false},
                busqueda_inscripciones:{mostrar:false}
            }
        }
    },
    methods:{
        buscar(ventana, metodo){
            this.$refs[ventana][metodo]();
        },
        abrirVentana(ventana){
            this.forms[ventana].mostrar = !this.forms[ventana].mostrar;
        },
        modificar(ventana, metodo, data){
            this.$refs[ventana][metodo](data);
        }
    }
});

// Registrar directiva
app.directive('draggable', vDraggable);

app.mount("#app");
