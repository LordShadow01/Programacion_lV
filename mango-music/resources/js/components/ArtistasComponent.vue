<template>
  <div class="container my-4">
    <div class="card bg-dark text-light border-mango mb-4">
      <div class="card-header bg-black border-mango d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-mango"><i class="bi bi-mic-fill"></i> Gestión de Artistas</h4>
        <button class="btn btn-mango" @click="openModal()"><i class="bi bi-plus-lg"></i> Nuevo Artista</button>
      </div>
      <div class="card-body">
        
        <!-- Search Form -->
        <div class="input-group mb-3 custom-search">
          <span class="input-group-text bg-black text-mango border-mango"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control bg-dark text-light border-mango" placeholder="Buscar por Nombre, Género o Ciudad..." v-model="searchQuery" @keyup="getArtistas">
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
          <table class="table table-dark table-hover table-striped custom-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Género</th>
                <th>Ciudad</th>
                <th>Contacto</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="artista in artistas" :key="artista.id">
                <td>{{ artista.id }}</td>
                <td class="fw-bold">{{ artista.nombre }}</td>
                <td><span class="badge bg-secondary">{{ artista.genero }}</span></td>
                <td>{{ artista.ciudad }}</td>
                <td>
                  <small class="d-block text-mango"><i class="bi bi-envelope"></i> {{ artista.correo }}</small>
                  <small class="d-block text-info"><i class="bi bi-telephone"></i> {{ artista.telefono }}</small>
                </td>
                <td>
                  <button class="btn btn-sm btn-outline-info me-2" @click="editArtista(artista)"><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteArtista(artista.id)"><i class="bi bi-trash"></i></button>
                </td>
              </tr>
              <tr v-if="artistas.length === 0">
                <td colspan="6" class="text-center text-muted">No se encontraron artistas.</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

    <!-- Modal for Create/Update -->
    <div class="modal fade" id="artistaModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-mango">
          <div class="modal-header border-mango">
            <h5 class="modal-title">{{ editMode ? 'Editar Artista' : 'Registrar Nuevo Artista' }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="validationErrors.length > 0" class="alert alert-danger bg-danger text-white border-0">
              <ul class="mb-0">
                <li v-for="error in validationErrors" :key="error">{{ error }}</li>
              </ul>
            </div>
            <form @submit.prevent="saveArtista">
              <div class="mb-3">
                <label class="form-label text-mango">Nombre del Artista</label>
                <input type="text" class="form-control bg-black text-light border-secondary" v-model="form.nombre" placeholder="Ej. The Weeknd" required>
              </div>
              <div class="mb-3">
                <label class="form-label text-mango">Género Musical</label>
                <input type="text" class="form-control bg-black text-light border-secondary" v-model="form.genero" placeholder="Ej. R&B" required>
              </div>
              <div class="mb-3">
                <label class="form-label text-mango">Ciudad</label>
                <input type="text" class="form-control bg-black text-light border-secondary" v-model="form.ciudad" placeholder="Ej. Toronto" required>
              </div>
              <div class="mb-3">
                <label class="form-label text-mango">Correo Electrónico</label>
                <input type="email" class="form-control bg-black text-light border-secondary" v-model="form.correo" placeholder="ejemplo@correo.com" required>
              </div>
              <div class="mb-3">
                <label class="form-label text-mango">Teléfono</label>
                <input type="text" class="form-control bg-black text-light border-secondary" v-model="form.telefono" placeholder="+12 3456-7890" required>
              </div>
              <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-mango">{{ editMode ? 'Actualizar' : 'Guardar' }}</button>
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
      artistas: [],
      searchQuery: '',
      form: { id: '', nombre: '', genero: '', ciudad: '', correo: '', telefono: '' },
      editMode: false,
      myModal: null,
      successMessage: '',
      errorMessage: '',
      validationErrors: []
    }
  },
  mounted() {
    this.getArtistas();
    const modalEl = document.getElementById('artistaModal');
    if(modalEl) { this.myModal = new bootstrap.Modal(modalEl); }
  },
  methods: {
    getArtistas() {
      axios.get('/artistas', { params: { search: this.searchQuery } })
        .then(res => { this.artistas = res.data; })
        .catch(err => { this.errorMessage = 'Error al cargar los artistas.'; });
    },
    openModal() {
      this.editMode = false;
      this.form = { id: '', nombre: '', genero: '', ciudad: '', correo: '', telefono: '' };
      this.validationErrors = [];
      this.myModal.show();
    },
    editArtista(artista) {
      this.editMode = true;
      this.form = { ...artista };
      this.validationErrors = [];
      this.myModal.show();
    },
    saveArtista() {
      if (!this.form.nombre || !this.form.genero || !this.form.ciudad || !this.form.correo || !this.form.telefono) {
         this.validationErrors = ['Todos los campos son obligatorios.'];
         return;
      }
      const request = this.editMode ? axios.put(`/artistas/${this.form.id}`, this.form) : axios.post('/artistas', this.form);
      
      request.then(res => {
        window.Swal.fire({ title: '¡Excelente!', text: res.data.message, icon: 'success', confirmButtonColor: '#ff9800' });
        this.getArtistas();
        this.myModal.hide();
      }).catch(err => {
        if(err.response && err.response.data.errors) {
            this.validationErrors = Object.values(err.response.data.errors).flat();
        } else {
            this.errorMessage = 'Ocurrió un error al guardar.';
        }
      });
    },
    deleteArtista(id) {
      if (confirm('¿Estás seguro de eliminar este artista? Esto borrará también sus canciones.')) {
        axios.delete(`/artistas/${id}`)
          .then(res => {
            window.Swal.fire({ title: '¡Eliminado!', text: res.data.message, icon: 'success', confirmButtonColor: '#ff9800' });
            this.getArtistas();
          }).catch(err => {
            this.errorMessage = 'No se pudo eliminar al artista.';
          });
      }
    }
  }
}
</script>

<style scoped>
.custom-search input:focus {
  box-shadow: none;
  border-color: #ff9800;
}
</style>
