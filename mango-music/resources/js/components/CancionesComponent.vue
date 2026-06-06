<template>
  <div class="container my-4">
    <div class="card bg-dark text-light border-mango mb-4">
      <div class="card-header bg-black border-mango d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-mango"><i class="bi bi-music-note-beamed"></i> Catálogo de Canciones</h4>
        <button class="btn btn-mango" @click="openModal()"><i class="bi bi-plus-lg"></i> Agregar Canción</button>
      </div>
      <div class="card-body">
        
        <!-- Search Form -->
        <div class="input-group mb-3 custom-search">
          <span class="input-group-text bg-black text-mango border-mango"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control bg-dark text-light border-mango" placeholder="Buscar por Título, Duración o Nombre del Artista..." v-model="searchQuery" @keyup="getCanciones">
        </div>

        <!-- Notification Alerts -->
        <div v-if="successMessage" class="alert alert-success alert-dismissible bg-mango text-black border-0 fade show" role="alert">
          {{ successMessage }}
          <button type="button" class="btn-close" @click="successMessage = ''"></button>
        </div>
        <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ errorMessage }}
          <button type="button" class="btn-close" @click="errorMessage = ''"></button>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
          <table class="table table-dark table-hover table-striped custom-table align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Artista</th>
                <th>Audio Local</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="cancion in canciones" :key="cancion.id">
                <td>{{ cancion.id }}</td>
                <td>
                  <div class="fw-bold">{{ cancion.titulo }}</div>
                  <small class="text-white"><i class="bi bi-clock"></i> {{ cancion.duracion }}</small>
                </td>
                <td class="text-mango" v-if="cancion.artista">{{ cancion.artista.nombre }}</td>
                <td v-else class="text-danger">Desconocido</td>
                <td>
                  <audio v-if="cancion.archivo_audio" controls style="height: 35px; width: 220px;" class="custom-audio">
                    <source :src="'/storage/' + cancion.archivo_audio" type="audio/mpeg">
                    Tu navegador no soporta el audio.
                  </audio>
                  <span v-else class="text-muted fst-italic"><i class="bi bi-volume-mute"></i> Sin audio</span>
                </td>
                <td>
                  <button class="btn btn-sm btn-outline-info me-2 my-1" @click="editCancion(cancion)"><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-sm btn-outline-danger my-1" @click="deleteCancion(cancion.id)"><i class="bi bi-trash"></i></button>
                </td>
              </tr>
              <tr v-if="canciones.length === 0">
                <td colspan="5" class="text-center text-muted">No se encontraron canciones en el catálogo.</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

    <!-- Modal for Create/Update -->
    <div class="modal fade" id="cancionModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-mango">
          <div class="modal-header border-mango">
            <h5 class="modal-title">{{ editMode ? 'Editar Canción' : 'Publicar Canción' }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="validationErrors.length > 0" class="alert alert-danger bg-danger text-white border-0">
              <ul class="mb-0">
                <li v-for="error in validationErrors" :key="error">{{ error }}</li>
              </ul>
            </div>
            <div v-if="artistas.length === 0" class="alert alert-warning mb-3">
              No hay artistas registrados. Registra uno primero antes de añadir canciones.
            </div>
            <form @submit.prevent="saveCancion">
              <div class="mb-3">
                <label class="form-label text-mango">Título de la Canción</label>
                <input type="text" class="form-control bg-black text-light border-secondary" v-model="form.titulo" placeholder="Ej. Blinding Lights" required>
              </div>
              <div class="mb-3">
                <label class="form-label text-mango">Artista</label>
                <select class="form-select bg-black text-light border-secondary" v-model="form.artista_id" required>
                  <option value="" disabled>Seleccione un artista...</option>
                  <option v-for="a in artistas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label text-mango">Duración (ej. 03:20)</label>
                <input type="text" class="form-control bg-black text-light border-secondary" v-model="form.duracion" placeholder="00:00" required>
              </div>
              <div class="mb-3">
                <label class="form-label text-mango"><i class="bi bi-file-music"></i> Subir Archivo de Audio (MP3/WAV)</label>
                <input type="file" ref="audioFile" @change="handleFileUpload" class="form-control bg-black text-light border-secondary" accept="audio/*">
                <small class="text-muted" v-if="editMode && form.archivo_audio">Ya hay un audio subido. Sube otro para reemplazarlo.</small>
              </div>
              <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-mango" :disabled="artistas.length === 0">{{ editMode ? 'Actualizar' : 'Guardar' }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      canciones: [],
      artistas: [],
      searchQuery: '',
      form: { id: '', titulo: '', artista_id: '', duracion: '', archivo_audio: null },
      audioFile: null,
      editMode: false,
      myModal: null,
      successMessage: '',
      errorMessage: '',
      validationErrors: []
    }
  },
  mounted() {
    this.getCanciones();
    this.getArtistas();
    const modalEl = document.getElementById('cancionModal');
    if(modalEl) { this.myModal = new bootstrap.Modal(modalEl); }
  },
  methods: {
    getCanciones() {
      axios.get('/canciones', { params: { search: this.searchQuery } })
        .then(res => { this.canciones = res.data; })
        .catch(err => { this.errorMessage = 'Error al cargar canciones.'; });
    },
    getArtistas() {
      axios.get('/artistas')
        .then(res => { this.artistas = res.data; })
        .catch(console.error);
    },
    handleFileUpload(event) {
      this.audioFile = event.target.files[0];
    },
    openModal() {
      this.getArtistas();
      this.editMode = false;
      this.form = { id: '', titulo: '', artista_id: '', duracion: '', archivo_audio: null };
      this.audioFile = null;
      if(this.$refs.audioFile) this.$refs.audioFile.value = '';
      this.validationErrors = [];
      this.myModal.show();
    },
    editCancion(cancion) {
      this.getArtistas();
      this.editMode = true;
      this.form = { ...cancion };
      this.audioFile = null;
      if(this.$refs.audioFile) this.$refs.audioFile.value = '';
      this.validationErrors = [];
      this.myModal.show();
    },
    saveCancion() {
      if (!this.form.titulo || !this.form.artista_id || !this.form.duracion) {
         this.validationErrors = ['Título, artista y duración son obligatorios.'];
         return;
      }
      
      let formData = new FormData();
      formData.append('titulo', this.form.titulo);
      formData.append('artista_id', this.form.artista_id);
      formData.append('duracion', this.form.duracion);
      if (this.audioFile) {
        formData.append('audio', this.audioFile);
      }
      if (this.editMode) {
        formData.append('_method', 'PUT'); // Trick Laravel para recibir multipart en PUT
      }

      const request = this.editMode 
          ? axios.post(`/canciones/${this.form.id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' }}) 
          : axios.post('/canciones', formData, { headers: { 'Content-Type': 'multipart/form-data' }});
      
      request.then(res => {
        window.Swal.fire({ title: '¡Excelente!', text: res.data.message, icon: 'success', confirmButtonColor: '#ff9800' });
        this.getCanciones();
        this.myModal.hide();
      }).catch(err => {
        if(err.response && err.response.data.errors) {
            this.validationErrors = Object.values(err.response.data.errors).flat();
        } else {
            this.errorMessage = 'Ocurrió un error al guardar la canción.';
        }
      });
    },
    deleteCancion(id) {
      if (confirm('¿Estás seguro de eliminar esta canción? También se borrará el audio asociado.')) {
        axios.delete(`/canciones/${id}`)
          .then(res => {
            window.Swal.fire({ title: '¡Eliminada!', text: res.data.message, icon: 'success', confirmButtonColor: '#ff9800' });
            this.getCanciones();
          }).catch(err => {
            this.errorMessage = 'No se pudo eliminar la canción.';
          });
      }
    }
  }
}
</script>

<style scoped>
.custom-search input:focus, .custom-search select:focus {
  box-shadow: none;
  border-color: #ff9800;
}
.custom-audio {
  filter: invert(1) brightness(0.6) sepia(1) hue-rotate(180deg) saturate(3);
}
</style>
