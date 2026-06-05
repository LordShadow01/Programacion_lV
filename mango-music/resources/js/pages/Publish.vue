<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
      <!-- Page Header -->
      <h1 :style="{ fontSize: '1.5rem', fontWeight: '800', color: '#000', marginBottom: '30px' }">Publicar</h1>

      <!-- Tabs -->
      <div :style="{ display: 'flex', justifyContent: 'center', gap: '40px', marginBottom: '20px' }">
        <div @click="activeTab = 'publish'" :style="activeTab === 'publish' ? activeTabStyle : inactiveTabStyle">Nueva Publicación</div>
        <div @click="activeTab = 'drafts'" :style="activeTab === 'drafts' ? activeTabStyle : inactiveTabStyle">
          Mis Borradores <span :style="{ marginLeft: '5px', opacity: 0.5 }">{{ drafts.length }}</span>
        </div>
      </div>

      <!-- PUBLISH TAB -->
      <div v-if="activeTab === 'publish'" :style="{ maxWidth: '900px', margin: '0 auto' }">
        <!-- Format Selection -->
        <p :style="{ textAlign: 'center', fontSize: '1.1rem', color: '#666', marginBottom: '20px' }">Selecciona el formato que quieres cargar:</p>
        <div :style="{ display: 'flex', gap: '20px', marginBottom: '40px' }">
          <button @click="contentType = 'video'" :style="contentType === 'video' ? videoBtnActiveStyle : videoBtnInactiveStyle">
            <Play :size="18" fill="#FFF" /> Video (MP4)
          </button>
          <button @click="contentType = 'audio'" :style="contentType === 'audio' ? musicBtnActiveStyle : musicBtnInactiveStyle">
            <Music :size="18" :style="{ color: '#FF7A1A' }" /> Música (MP3)
          </button>
        </div>

        <!-- MAIN WHITE CARD -->
        <div :style="mainCardStyle">
          <!-- Dropzone Card -->
          <div :style="dropzoneCardStyle" @click="triggerFileSelect">
            <div :style="{ marginBottom: '15px' }">
              <UploadIcon :size="32" :style="{ color: '#FF7A1A' }" />
            </div>
            <h3 :style="{ fontSize: '1.4rem', fontWeight: '800', color: '#1A1614', marginBottom: '8px' }">
              {{ selectedFile ? 'Archivo seleccionado' : 'Arrastra tu archivo aquí' }}
            </h3>
            <p :style="{ color: '#A0A0A0', fontSize: '1.1rem', marginBottom: '25px' }">
              {{ selectedFile ? selectedFile.name : 'O si lo prefieres, búscalo en tu equipo' }}
            </p>
            <button :style="selectFileButtonStyle">{{ selectedFile ? 'Cambiar Archivo' : 'Seleccionar Archivo' }}</button>
            <input type="file" ref="fileInput" @change="onFileChange" :accept="contentType === 'audio' ? 'audio/*' : 'video/*'" style="display: none" />
          </div>

          <!-- Progress Bar if uploading -->
          <div v-if="uploadProgress > 0" :style="{ marginTop: '20px' }">
            <div :style="{ height: '8px', backgroundColor: '#EEE', borderRadius: '10px', overflow: 'hidden' }">
              <div :style="{ width: uploadProgress + '%', height: '100%', backgroundColor: '#FF7A1A', transition: '0.3s' }"></div>
            </div>
            <p :style="{ textAlign: 'center', fontSize: '0.9rem', color: '#FF7A1A', marginTop: '5px', fontWeight: '700' }">Cargando: {{ uploadProgress }}%</p>
          </div>

          <!-- Form Section -->
          <div :style="{ marginTop: '60px' }">
            <h3 :style="{ textAlign: 'center', fontSize: '1.3rem', fontWeight: '800', color: '#000', marginBottom: '40px' }">Colocar información del archivo</h3>
            
            <div :style="{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px 40px' }">
              <div :style="fieldGroupStyle">
                <label :style="labelStyle">Título del contenido</label>
                <input v-model="form.titulo" type="text" placeholder="Ej: Mi Gran Lanzamiento" :style="inputStyle" required />
              </div>
              <div :style="fieldGroupStyle">
                <label :style="labelStyle">Género principal</label>
                <select v-model="form.genero" :style="inputStyle" required>
                  <option value="" disabled>Selecciona un género...</option>
                  <option value="Pop">Pop</option>
                  <option value="Rock">Rock</option>
                  <option value="Urbano">Urbano</option>
                  <option value="Electrónica">Electrónica</option>
                  <option value="Cumbia">Cumbia</option>
                  <option value="Regional">Regional</option>
                  <option value="Alternativo">Alternativo</option>
                  <option value="Otro">Otro</option>
                </select>
              </div>
              
              <div :style="fieldGroupStyle">
                <label :style="labelStyle">Nombre del Artista</label>
                <input v-model="form.artista" type="text" placeholder="Nombre que aparecerá" :style="inputStyle" required />
              </div>
              <div :style="fieldGroupStyle">
                <label :style="labelStyle">Carátula (Imagen Local)</label>
                <div :style="{ display: 'flex', gap: '10px' }">
                  <input :value="form.caratula ? 'Imagen cargada' : ''" readonly placeholder="Selecciona una imagen" :style="{ ...inputStyle, flex: 1 }" />
                  <button @click="triggerCoverSelect" :style="smallButtonStyle"><ImageIcon :size="18" /></button>
                  <input type="file" ref="coverInput" @change="onCoverChange" accept="image/*" style="display: none" />
                </div>
              </div>
            </div>

            <div :style="{ ...fieldGroupStyle, marginTop: '20px' }">
              <label :style="labelStyle">Descripción breve</label>
              <textarea v-model="form.descripcion" placeholder="Cuéntanos un poco sobre este lanzamiento..." :style="{ ...inputStyle, minHeight: '100px', resize: 'none' }"></textarea>
            </div>
          </div>

          <!-- Action Buttons -->
          <div :style="{ display: 'flex', gap: '20px', marginTop: '50px', justifyContent: 'center' }">
            <button @click="handlePublish" :disabled="isUploading" :style="publishButtonStyle">
              {{ isUploading ? 'Cargando...' : 'Publicar Ahora' }}
            </button>
            <button :style="draftButtonStyle">Guardar en Borradores</button>
          </div>
        </div>
      </div>

      <!-- DRAFTS TAB -->
      <div v-else :style="{ maxWidth: '1100px', margin: '0 auto' }">
        <div :style="draftsGridStyle">
          <div v-for="draft in drafts" :key="draft.id" class="draft-card-hover" :style="draftCardStyle">
            <div :style="draftThumbStyle">
              <component :is="draft.type === 'video' ? Play : Music" :size="40" :style="{ color: '#FF7A1A' }" />
            </div>
            <div>
              <h4 :style="{ fontSize: '1.2rem', fontWeight: '900', color: '#1A1614', marginBottom: '8px' }">{{ draft.title }}</h4>
              <p :style="{ fontSize: '1rem', color: '#A0A0A0', fontWeight: '600' }">{{ draft.date }} • {{ draft.type === 'video' ? 'Video' : 'Audio' }}</p>
            </div>
            <div :style="{ width: '100%', marginTop: 'auto' }">
              <button @click="continueEditing(draft)" :style="draftEditBtnStyle">Continuar Editando</button>
              <button @click="deleteDraft(draft.id)" :style="draftDeleteBtnStyle">Eliminar Borrador</button>
            </div>
          </div>
        </div>
      </div>
    </main>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Play, Music, Upload as UploadIcon, Image as ImageIcon } from 'lucide-vue-next';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useRouter } from 'vue-router';

const router = useRouter();
const activeTab = ref('publish');
const contentType = ref('audio');

const fileInput = ref(null);
const coverInput = ref(null);
const selectedFile = ref(null);
const uploadProgress = ref(0);
const isUploading = ref(false);

const form = reactive({
  titulo: '',
  genero: '',
  artista: '',
  caratula: '',
  url_audio: '',
  descripcion: ''
});

onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user'));
  if (user) {
    form.artista = user.nombre_artistico || user.nombre;
  }
});

const triggerFileSelect = () => fileInput.value.click();
const triggerCoverSelect = () => coverInput.value.click();

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    if (!form.titulo) form.titulo = file.name.split('.').slice(0, -1).join('.');
  }
};

const onCoverChange = async (e) => {
  const file = e.target.files[0];
  if (file) {
    const formData = new FormData();
    formData.append('file', file);
    try {
      const token = localStorage.getItem('token');
      const res = await axios.post('/api/upload', formData, {
        headers: { 
          'Content-Type': 'multipart/form-data',
          Authorization: `Bearer ${token}` 
        }
      });
      form.caratula = res.data.url;
      Swal.fire({ icon: 'success', title: 'Carátula cargada', timer: 1000, showConfirmButton: false });
    } catch (err) {
      Swal.fire('Error', 'No se pudo subir la imagen', 'error');
    }
  }
};

const handlePublish = async () => {
  if (!selectedFile.value) {
    Swal.fire('Error', 'Por favor selecciona un archivo de audio o video.', 'warning');
    return;
  }

  try {
    isUploading.value = true;
    const user = JSON.parse(localStorage.getItem('user'));
    const token = localStorage.getItem('token');
    
    // 1. Upload the main file
    const formData = new FormData();
    formData.append('file', selectedFile.value);
    
    const uploadRes = await axios.post('/api/upload', formData, {
      headers: { 
        'Content-Type': 'multipart/form-data',
        Authorization: `Bearer ${token}` 
      },
      onUploadProgress: (p) => {
        uploadProgress.value = Math.round((p.loaded * 100) / p.total);
      }
    });

    const fileUrl = uploadRes.data.url;

    // 2. Save the metadata
    const dataToSend = {
      ...form,
      url_audio: fileUrl,
      user_id: user.id
    };

    await axios.post('/api/canciones', dataToSend, {
      headers: { Authorization: `Bearer ${token}` }
    });
    
    Swal.fire({
      icon: 'success',
      title: '¡Publicado!',
      text: 'Tu contenido ya está disponible en la plataforma.',
      timer: 2000,
      showConfirmButton: false
    });
    
    router.push('/dashboard');
  } catch (error) {
    console.error(error);
    const errorMsg = error.response?.data?.message || error.response?.data?.errors || 'Ocurrió un error al publicar tu contenido.';
    Swal.fire('Error', typeof errorMsg === 'string' ? errorMsg : JSON.stringify(errorMsg), 'error');
  } finally {
    isUploading.value = false;
    uploadProgress.value = 0;
  }
};

const drafts = ref([
  { id: 1, title: 'Próximo Hit de Verano', type: 'audio', date: 'Hace 2 horas' },
]);

const deleteDraft = (id) => drafts.value = drafts.value.filter(d => d.id !== id);
const continueEditing = (draft) => { contentType.value = draft.type; activeTab.value = 'publish'; };

// STYLES
const publishButtonStyle = {
  padding: '18px 60px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '20px',
  fontSize: '1.1rem', fontWeight: '900', cursor: 'pointer', boxShadow: '0 15px 35px rgba(255,122,26,0.35)',
  transition: '0.3s', opacity: isUploading.value ? 0.7 : 1
};
const draftButtonStyle = { padding: '18px 40px', backgroundColor: '#F5F5F5', color: '#666', border: 'none', borderRadius: '20px', fontSize: '1rem', fontWeight: '700', cursor: 'pointer' };
const activeTabStyle = { fontSize: '1rem', fontWeight: '800', color: '#FF7A1A', cursor: 'pointer', borderBottom: '3px solid #FF7A1A', paddingBottom: '8px' };
const inactiveTabStyle = { ...activeTabStyle, color: '#A0A0A0', fontWeight: '600', borderBottom: '3px solid transparent' };
const videoBtnActiveStyle = { flex: 1, padding: '15px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '15px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '10px', fontWeight: '800', cursor: 'pointer' };
const videoBtnInactiveStyle = { ...videoBtnActiveStyle, backgroundColor: '#FFF', color: '#A0A0A0', border: '1.5px solid #EEE' };
const musicBtnActiveStyle = { flex: 1, padding: '15px', backgroundColor: '#FFF', color: '#FF7A1A', border: '2px solid #FF7A1A', borderRadius: '15px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '10px', fontWeight: '800', cursor: 'pointer' };
const musicBtnInactiveStyle = { ...musicBtnActiveStyle, color: '#A0A0A0', border: '1.5px solid #EEE' };
const draftsGridStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '30px' };
const draftCardStyle = { backgroundColor: '#FFF', padding: '30px', borderRadius: '35px', boxShadow: '0 15px 40px rgba(0,0,0,0.03)', border: '1px solid #F8F8F8', display: 'flex', flexDirection: 'column', alignItems: 'center', textAlign: 'center', transition: 'transform 0.3s' };
const draftThumbStyle = { width: '100px', height: '100px', backgroundColor: '#FFF9F5', borderRadius: '25px', display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: '20px' };
const draftEditBtnStyle = { width: '100%', padding: '12px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '15px', fontSize: '1.1rem', fontWeight: '800', cursor: 'pointer', marginTop: '20px' };
const draftDeleteBtnStyle = { padding: '12px', backgroundColor: '#FFF0F0', color: '#FF4B4B', border: 'none', borderRadius: '15px', display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', marginTop: '10px', width: '100%' };
const mainCardStyle = { backgroundColor: '#FFF', padding: '50px', borderRadius: '40px', boxShadow: '0 10px 40px rgba(0,0,0,0.02)' };
const dropzoneCardStyle = { backgroundColor: '#FFF9F5', border: '1px solid #FFEFE5', borderRadius: '30px', padding: '60px', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', textAlign: 'center', cursor: 'pointer' };
const selectFileButtonStyle = { backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', padding: '12px 30px', borderRadius: '12px', fontSize: '1.1rem', fontWeight: '800', cursor: 'pointer' };
const smallButtonStyle = { padding: '10px 15px', backgroundColor: '#FDFCFB', border: '1px solid #EEE', borderRadius: '12px', color: '#FF7A1A', cursor: 'pointer' };
const fieldGroupStyle = { display: 'flex', flexDirection: 'column', gap: '10px' };
const labelStyle = { fontSize: '1.1rem', fontWeight: '800', color: '#1A1614' };
const inputStyle = { padding: '15px 20px', borderRadius: '12px', border: '1px solid #F0F0F0', backgroundColor: '#FBFBFB', fontSize: '1.1rem', color: '#333', outline: 'none' };
</script>
