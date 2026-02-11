const { createApp } = Vue,
    Dexie = window.Dexie,
    db = new Dexie("db_academica");


createApp({
    data(){
        return{
            alumno:{
                idAlumno:0,
                codigo:"",
                nombre:"",
                direccion:"",
                email:"",
                telefono:""
            },
            accion:'nuevo',
            id:0,
            idAlumno:0,
            buscar:'',
            alumnos:[]
        }
    },
    methods:{
        async obtenerAlumnos(){
            this.alumnos = await db.alumnos.filter(
                alumno => alumno.codigo.toLowerCase().includes(this.buscar.toLowerCase()) 
                    || alumno.nombre.toLowerCase().includes(this.buscar.toLowerCase())
            ).toArray();
        },
        async eliminarAlumno(idAlumno, e){
            e.stopPropagation();
            if(confirm("¿Está seguro de eliminar el alumno?")){
                localStorage.removeItem(id);
                await db.alumnos.delete(idAlumno);
                this.obtenerAlumnos();
            }
        },
        modificarAlumno(alumno){
            this.accion = 'modificar';
            this.id = alumno.id;
            this.idAlumno = alumno.idAlumno;
            this.alumno.codigo = alumno.codigo;
            this.alumno.nombre = alumno.nombre;
            this.alumno.direccion = alumno.direccion;
            this.alumno.email = alumno.email;
            this.alumno.telefono = alumno.telefono;
        },
        async guardarAlumno() {
            let datos = {
                id: this.accion=='modificar' ? this.id : this.getId(),
                idAlumno: this.accion=='modificar' ? this.idAlumno : this.getId(),
                codigo: this.alumno.codigo,
                nombre: this.alumno.nombre,
                direccion: this.alumno.direccion,
                email: this.alumno.email,
                telefono: this.alumno.telefono
            }, codigoDuplicado = this.buscarAlumno(datos.codigo);
            if(codigoDuplicado && this.accion=='nuevo'){
                alert("El codigo del alumno ya existe, "+ codigoDuplicado.nombre);
            };
            this.buscar = datos.codigo;
            await this.obtenerAlumnos();

            if(this.alumnos.length > 0 && this.accion=='nuevo'){
                alert("El codigo del alumno ya existe, "+ this.alumnos[0].nombre);
                return; //Termina la ejecucion de la funcion
            }
            localStorage.setItem( datos.id, JSON.stringify(datos));
            db.alumnos.put(datos);
            this.limpiarFormulario();
            this.obtenerAlumnos();
        },
        getId(){
            return new Date().getTime();
        },
        limpiarFormulario(){
            this.accion = 'nuevo';
            this.id = 0;
            this.idAlumno = 0;
            this.alumno.codigo = '';
            this.alumno.nombre = '';
            this.alumno.direccion = '';
            this.alumno.email = '';
            this.alumno.telefono = '';
        },
        buscarAlumno(codigo=''){
            let n = localStorage.length;
            for(let i = 0; i < n; i++){
                let key = localStorage.key(i);
                let datos = JSON.parse(localStorage.getItem(key));
                if(datos?.codigo && datos.codigo.trim().toUpperCase() == codigo.trim().toUpperCase()){
                    return datos;
                }
            }
            return null;
        }
    },
    mounted(){
        db.version(1).stores({
            "alumnos": "idAlumno, codigo, nombre, direccion, email, telefono"
        });
        this.obtenerAlumnos();
    }
}).mount("#app");