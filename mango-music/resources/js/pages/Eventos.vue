<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
      <h1 :style="{ fontSize: '1.8rem', fontWeight: '800', color: '#1A1614', marginBottom: '30px' }">Gestión de Eventos</h1>

      <div :style="{ maxWidth: '1000px', margin: '0 auto' }">
        
        <!-- PUBLISH / EDIT FORM CARD -->
        <div :style="mainCardStyle">
          <h3 :style="{ textAlign: 'center', fontSize: '1.4rem', fontWeight: '800', color: '#1A1614', marginBottom: '40px' }">
            {{ isEditing ? 'Editar Evento' : 'Publicar Nuevo Evento' }}
          </h3>
          
          <form @submit.prevent="handleFormSubmit">
            <div :style="dropzoneCardStyle">
              <div :style="{ marginBottom: '15px' }">
                <Upload :size="32" :style="{ color: '#FF7A1A' }" />
              </div>
              <h3 :style="{ fontSize: '1.2rem', fontWeight: '800', color: '#1A1614', marginBottom: '8px' }">Foto del Lugar</h3>
              <p :style="{ color: '#A0A0A0', fontSize: '1rem', marginBottom: '20px' }">Sube una imagen para tu evento</p>
              
              <div :style="{ display: 'flex', gap: '10px', width: '100%', maxWidth: '500px' }">
                <input v-model="form.imagen" type="text" placeholder="https://url-de-la-imagen.com/foto.jpg" :style="{ ...inputStyle, flex: 1 }" />
                <button type="button" @click="$refs.fileInput.click()" :style="uploadButtonStyle">Subir Archivo</button>
                <input type="file" ref="fileInput" @change="onFileChange" style="display: none" accept="image/*" />
              </div>
              
              <div v-if="form.imagen" :style="{ marginTop: '20px' }">
                <img :src="form.imagen" :style="{ width: '150px', height: '100px', borderRadius: '15px', objectFit: 'cover', border: '2px solid #FF7A1A' }" />
              </div>
            </div>

            <div :style="{ marginTop: '40px', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px 40px' }">
              <div :style="fieldGroupStyle">
                <label :style="labelStyle">Nombre del Evento</label>
                <input v-model="form.nombre" type="text" placeholder="Ej: Concierto Acústico" :style="inputStyle" required />
              </div>
              <div :style="fieldGroupStyle">
                <label :style="labelStyle">Cantante Invitado</label>
                <input v-model="form.cantante_invitado" type="text" placeholder="Artista principal" :style="inputStyle" />
              </div>
              <div :style="fieldGroupStyle">
                <label :style="labelStyle">Lugar</label>
                <input v-model="form.lugar" type="text" placeholder="Club, Teatro, etc." :style="inputStyle" required />
              </div>
              <div :style="{ display: 'flex', gap: '15px' }">
                <div :style="{ flex: 1 }">
                  <label :style="labelStyle">Fecha</label>
                  <input v-model="form.fecha" type="date" :style="inputStyle" required />
                </div>
                <div :style="{ flex: 1 }">
                  <label :style="labelStyle">Hora</label>
                  <input v-model="form.hora" type="time" :style="inputStyle" required />
                </div>
              </div>
              <div :style="fieldGroupStyle">
                <label :style="labelStyle">Tipo de Entrada</label>
                <div :style="{ display: 'flex', gap: '15px' }">
                  <button type="button" @click="form.es_gratis = true" :style="form.es_gratis ? activeToggleStyle : inactiveToggleStyle">Gratis</button>
                  <button type="button" @click="form.es_gratis = false" :style="!form.es_gratis ? activeToggleStyle : inactiveToggleStyle">Pagado</button>
                </div>
              </div>
              <div v-if="!form.es_gratis" :style="fieldGroupStyle">
                <label :style="labelStyle">Precio ($)</label>
                <input v-model="form.precio" type="number" step="0.01" :style="inputStyle" required />
              </div>
            </div>

            <div :style="{ ...fieldGroupStyle, marginTop: '20px' }">
              <label :style="labelStyle">Descripción</label>
              <textarea v-model="form.descripcion" :style="{ ...inputStyle, minHeight: '80px', resize: 'none' }"></textarea>
            </div>

            <div :style="{ display: 'flex', gap: '20px', marginTop: '40px', justifyContent: 'center' }">
              <button type="submit" :style="publishButtonStyle">
                {{ isEditing ? 'Guardar Cambios' : 'Publicar Evento' }}
              </button>
              <button v-if="isEditing" type="button" @click="cancelEdit" :style="cancelButtonStyle">Cancelar</button>
              <button v-else type="button" @click="resetForm" :style="cancelButtonStyle">Limpiar</button>
            </div>
          </form>
        </div>

        <!-- MY EVENTS HISTORY -->
        <div :style="{ marginTop: '80px' }">
          <h2 :style="{ fontSize: '1.6rem', fontWeight: '900', color: '#1A1614', marginBottom: '30px' }">Historial de tus Eventos</h2>
          
          <div v-if="misEventos.length === 0" :style="{ textAlign: 'center', padding: '40px', backgroundColor: '#FFF', borderRadius: '30px', border: '1px solid #F0F0F0' }">
            <p :style="{ color: '#A0A0A0' }">No has publicado ningún evento todavía.</p>
          </div>
          
          <div v-else :style="{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: '25px' }">
            <div v-for="evento in misEventos" :key="evento.id" :style="eventCardStyle">
              <div :style="eventImageStyle(evento.imagen)">
                <div :style="cardActionOverlay">
                  <button @click="startEdit(evento)" :style="iconButtonStyle"><Edit2 :size="18" /></button>
                  <button @click="deleteEvento(evento.id)" :style="iconButtonStyle"><Trash2 :size="18" /></button>
                </div>
              </div>
              <div :style="{ padding: '20px' }">
                <h4 :style="{ fontWeight: '800', color: '#1A1614', fontSize: '1.1rem' }">{{ evento.nombre }}</h4>
                <div :style="{ marginTop: '10px', color: '#666', fontSize: '0.85rem' }">
                  <div :style="{ display: 'flex', alignItems: 'center', gap: '5px' }"><MapPin :size="14" /> {{ evento.lugar }}</div>
                  <div :style="{ display: 'flex', alignItems: 'center', gap: '5px', marginTop: '4px' }"><Calendar :size="14" /> {{ evento.fecha }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Upload, MapPin, Calendar, Edit2, Trash2 } from 'lucide-vue-next';
import axios from 'axios';
import Swal from 'sweetalert2';

const user = JSON.parse(localStorage.getItem('user') || '{}');
const misEventos = ref([]);
const isEditing = ref(false);
const editingId = ref(null);

const form = reactive({
  nombre: '',
  cantante_invitado: '',
  lugar: '',
  fecha: '',
  hora: '',
  es_gratis: true,
  precio: null,
  descripcion: '',
  imagen: '',
  user_id: user.id
});

const fetchMyEvents = async () => {
  try {
    const response = await axios.get(`/api/eventos/usuario/${user.id}`);
    misEventos.value = response.data;
  } catch (error) {
    console.error('Error fetching events:', error);
  }
};

const onFileChange = async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const formData = new FormData();
  formData.append('file', file);
  try {
    const token = localStorage.getItem('token');
    const res = await axios.post('/api/upload/image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        Authorization: `Bearer ${token}`
      }
    });
    form.imagen = res.data.url;
  } catch (e) { Swal.fire('Error', 'No se pudo subir la imagen', 'error'); }
};

const handleFormSubmit = async () => {
  try {
    const token = localStorage.getItem('token');
    const headers = { Authorization: `Bearer ${token}` };
    if (isEditing.value) {
      await axios.put(`/api/eventos/${editingId.value}`, form, { headers });
      Swal.fire('¡Éxito!', 'Evento actualizado correctamente', 'success');
    } else {
      await axios.post('/api/eventos', form, { headers });
      Swal.fire('¡Éxito!', 'Evento publicado correctamente', 'success');
    }
    cancelEdit();
    fetchMyEvents();
  } catch (e) {
    // Mostrar el primer error de validación de Laravel si existe
    const errors = e.response?.data?.errors;
    const msg = errors
      ? Object.values(errors).flat().join('<br>')
      : (e.response?.data?.message || 'No se pudo procesar el evento');
    Swal.fire({ icon: 'error', title: 'Error', html: msg });
  }
};

const startEdit = (evento) => {
  isEditing.value = true;
  editingId.value = evento.id;
  Object.assign(form, evento);
};

const cancelEdit = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
};

const resetForm = () => {
  form.nombre = ''; form.cantante_invitado = ''; form.lugar = ''; form.fecha = '';
  form.hora = ''; form.es_gratis = true; form.precio = null; form.descripcion = ''; form.imagen = '';
};

const deleteEvento = async (id) => {
  const result = await Swal.fire({
    title: '¿Estás seguro?',
    text: "No podrás revertir esta acción",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#FF7A1A',
    confirmButtonText: 'Sí, eliminar'
  });
  if (result.isConfirmed) {
    const token = localStorage.getItem('token');
    await axios.delete(`/api/eventos/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    fetchMyEvents();
    Swal.fire('Eliminado', 'El evento ha sido borrado', 'success');
  }
};

onMounted(fetchMyEvents);

const mainCardStyle = { backgroundColor: '#FFF', padding: '40px', borderRadius: '35px', boxShadow: '0 10px 40px rgba(0,0,0,0.02)', border: '1px solid #F0F0F0' };
const dropzoneCardStyle = { backgroundColor: '#FFF9F5', border: '1px dashed #FFD0B0', borderRadius: '25px', padding: '30px', display: 'flex', flexDirection: 'column', alignItems: 'center', textAlign: 'center' };
const inputStyle = { padding: '12px 20px', borderRadius: '12px', border: '1px solid #F0F0F0', backgroundColor: '#FBFBFB', fontSize: '1rem', outline: 'none', width: '100%' };
const labelStyle = { fontSize: '0.9rem', fontWeight: '800', color: '#1A1614', marginBottom: '8px' };
const fieldGroupStyle = { display: 'flex', flexDirection: 'column' };
const uploadButtonStyle = { padding: '10px 20px', backgroundColor: '#FFF', border: '1px solid #EEE', borderRadius: '10px', fontWeight: '700', cursor: 'pointer' };
const publishButtonStyle = { padding: '15px 40px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '15px', fontWeight: '900', cursor: 'pointer', boxShadow: '0 10px 25px rgba(255,122,26,0.2)' };
const cancelButtonStyle = { padding: '15px 30px', backgroundColor: '#F5F5F5', color: '#666', border: 'none', borderRadius: '15px', fontWeight: '700', cursor: 'pointer' };
const activeToggleStyle = { flex: 1, padding: '10px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '10px', fontWeight: '800' };
const inactiveToggleStyle = { ...activeToggleStyle, backgroundColor: '#F0F0F0', color: '#AAA' };

const eventCardStyle = { backgroundColor: '#FFF', borderRadius: '25px', overflow: 'hidden', border: '1px solid #F0F0F0', position: 'relative' };
const eventImageStyle = (img) => ({ width: '100%', height: '160px', backgroundImage: `url(${img || 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800'})`, backgroundSize: 'cover', backgroundPosition: 'center', position: 'relative' });
const cardActionOverlay = { position: 'absolute', top: '10px', right: '10px', display: 'flex', gap: '8px' };
const iconButtonStyle = { width: '35px', height: '35px', borderRadius: '50%', border: 'none', backgroundColor: 'rgba(255,255,255,0.9)', display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', boxShadow: '0 4px 10px rgba(0,0,0,0.1)' };
</script>
