<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
    <div :style="containerStyle">
      
      <!-- Header -->
      <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '40px' }">
        <div>
          <h2 :style="{ fontSize: '2rem', fontWeight: '900', color: '#1A1614', letterSpacing: '-0.5px' }">
            Convocatorias de Colaboración
          </h2>
          <p :style="{ color: '#666', marginTop: '5px', fontWeight: '500' }">
            Publica oportunidades de trabajo y revisa las postulaciones de artistas interesados en colaborar contigo.
          </p>
        </div>
        <button @click="openCreateModal" :style="createBtnStyle">
          <Plus :size="20" /> Publicar Convocatoria
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" :style="{ textAlign: 'center', padding: '50px' }">
        <div class="loader" :style="loaderStyle"></div>
        <p :style="{ color: '#666', marginTop: '15px', fontWeight: '600' }">Cargando convocatorias...</p>
      </div>

      <!-- No Collabs State -->
      <div v-else-if="solicitudes.length === 0" :style="emptyStateStyle">
        <Briefcase :size="60" :style="{ color: '#FF7A1A', marginBottom: '20px', opacity: 0.8 }" />
        <h3 :style="{ fontSize: '1.4rem', fontWeight: '800', color: '#1A1614', marginBottom: '10px' }">No tienes convocatorias publicadas</h3>
        <p :style="{ color: '#A0A0A0', marginBottom: '25px', maxWidth: '400px' }">
          Comienza a trabajar con talentos de Mango Music creando tu primera oferta de colaboración.
        </p>
        <button @click="openCreateModal" :style="createBtnStyle">Publicar Nueva Convocatoria</button>
      </div>

      <!-- Solicitudes List -->
      <div v-else :style="{ display: 'flex', flexDirection: 'column', gap: '25px' }">
        <div v-for="sol in solicitudes" :key="sol._id" :style="cardStyle">
          
          <!-- Banner image if uploaded -->
          <div v-if="sol.imagen_cabecera" :style="bannerStyle">
            <img :src="sol.imagen_cabecera" :style="{ width: '100%', height: '100%', objectFit: 'cover' }" />
          </div>

          <!-- Card Header / Details -->
          <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', flexWrap: 'wrap', gap: '15px' }">
            <div>
              <div :style="{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '8px' }">
                <span :style="getGenreBadgeStyle(sol.genero_musical)">{{ sol.genero_musical }}</span>
                <span :style="getStatusBadgeStyle(sol.estado)">
                  {{ sol.estado === 'activa' ? 'Activa' : 'Cerrada' }}
                </span>
              </div>
              <h3 :style="{ fontSize: '1.4rem', fontWeight: '800', color: '#1A1614', marginBottom: '8px' }">
                {{ sol.titulo }}
              </h3>
              <p :style="{ color: '#555', fontSize: '1.05rem', lineHeight: '1.5', whiteSpace: 'pre-line' }">
                {{ sol.descripcion }}
              </p>
              
              <div :style="{ display: 'flex', gap: '20px', marginTop: '15px', color: '#888', fontSize: '0.9rem', fontWeight: '600', flexWrap: 'wrap' }">
                <span>Publicada el: {{ formatDate(sol.created_at) }}</span>
                <span>•</span>
                <span :style="{ color: '#FF7A1A' }">
                  {{ sol.postulantes ? sol.postulantes.length : 0 }} 
                  {{ sol.limite_postulantes ? `/ ${sol.limite_postulantes}` : '' }} Postulantes
                </span>
                <span v-if="sol.fecha_limite">•</span>
                <span v-if="sol.fecha_limite" :style="{ color: isExpired(sol.fecha_limite) ? '#EF4444' : '#888' }">
                  Límite: {{ formatDate(sol.fecha_limite) }}
                  {{ isExpired(sol.fecha_limite) ? ' (Expirado)' : '' }}
                </span>
              </div>
            </div>

            <!-- Actions for card -->
            <div :style="{ display: 'flex', gap: '10px' }">
              <button 
                @click="toggleStatus(sol)" 
                :style="sol.estado === 'activa' ? closeBtnStyle : activateBtnStyle"
              >
                {{ sol.estado === 'activa' ? 'Cerrar Convocatoria' : 'Abrir Convocatoria' }}
              </button>
              <button @click="togglePostulantes(sol)" :style="expandBtnStyle">
                <Users :size="16" />
                {{ expandedCollabs[getSolId(sol)] ? 'Ocultar Postulantes' : 'Ver Postulantes' }}
                <ChevronDown :size="16" :style="{ transform: expandedCollabs[getSolId(sol)] ? 'rotate(180deg)' : 'none', transition: '0.2s' }" />
              </button>
            </div>
          </div>

          <!-- Candidates / Postulantes Section (Expanded) -->
          <div v-if="expandedCollabs[getSolId(sol)]" :style="postulantesContainerStyle">
            <h4 :style="{ fontSize: '1.1rem', fontWeight: '800', color: '#1A1614', marginBottom: '20px', borderBottom: '1px solid #F0F0F0', paddingBottom: '10px' }">
              Artistas Postulados
            </h4>
            
            <div v-if="loadingPostulantes[getSolId(sol)]" :style="{ textAlign: 'center', padding: '20px' }">
              <div class="loader" :style="{ ...loaderStyle, width: '30px', height: '30px' }"></div>
            </div>

            <div v-else-if="!postulantes[getSolId(sol)] || postulantes[getSolId(sol)].length === 0" :style="{ textAlign: 'center', padding: '30px 0', color: '#999' }">
              <Users :size="36" :style="{ marginBottom: '10px', opacity: 0.5 }" />
              <p>Aún no hay artistas postulados a esta convocatoria.</p>
            </div>

            <div v-else :style="{ display: 'flex', flexDirection: 'column', gap: '20px' }">
              <div v-for="post in postulantes[getSolId(sol)]" :key="post.artista_id" :style="postulanteCardStyle">
                <div :style="{ display: 'flex', gap: '20px', alignItems: 'flex-start' }">
                  <!-- Artist Photo -->
                  <img 
                    :src="post.foto || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&h=100&fit=crop'" 
                    :style="artistPhotoStyle" 
                  />
                  <!-- Candidate Details -->
                  <div :style="{ flex: 1 }">
                    <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '10px' }">
                      <div>
                        <div :style="{ display: 'flex', alignItems: 'center', gap: '12px' }">
                          <h5 :style="{ fontSize: '1.1rem', fontWeight: '800', color: '#1A1614' }">
                            {{ post.nombre_artistico || post.nombre }}
                          </h5>
                          <span :style="getCandidateBadgeStyle(post.estado_postulacion)">
                            {{ translateStatus(post.estado_postulacion) }}
                          </span>
                        </div>
                        <p :style="{ fontSize: '0.85rem', color: '#999' }">
                          Postulado el {{ formatDate(post.fecha_postulacion) }}
                        </p>
                      </div>
                      
                      <div :style="{ display: 'flex', gap: '10px', alignItems: 'center' }">
                        <button @click="contactarArtista(post, sol)" :style="contactBtnStyle">
                          <Mail :size="15" /> Contactar
                        </button>
                        <button 
                          v-if="post.estado_postulacion === 'pendiente'"
                          @click="updateCandidateStatus(sol, post.artista_id, 'aceptado')"
                          :style="acceptBtnStyle"
                        >
                          Aceptar
                        </button>
                        <button 
                          v-if="post.estado_postulacion === 'pendiente'"
                          @click="updateCandidateStatus(sol, post.artista_id, 'rechazado')"
                          :style="rejectBtnStyle"
                        >
                          Rechazar
                        </button>
                      </div>
                    </div>
                    
                    <!-- Motivación -->
                    <div :style="motivationBoxStyle">
                      <p :style="{ fontWeight: '700', color: '#FF7A1A', fontSize: '0.85rem', textTransform: 'uppercase', marginBottom: '5px' }">
                        Mensaje del Artista:
                      </p>
                      <p :style="{ color: '#444', fontSize: '0.95rem', lineHeight: '1.4' }">
                        "{{ post.mensaje_motivacional }}"
                      </p>
                    </div>

                    <!-- Bio if present -->
                    <p v-if="post.biografia" :style="{ color: '#666', fontSize: '0.9rem', marginTop: '10px' }">
                      <span :style="{ fontWeight: '600' }">Biografía:</span> {{ post.biografia }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CREATE MODAL -->
      <div v-if="showCreateModal" :style="modalOverlayStyle" @click.self="showCreateModal = false">
        <div :style="modalContentStyle">
          <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '25px' }">
            <h3 :style="{ fontSize: '1.6rem', fontWeight: '900', color: '#1A1614' }">Nueva Convocatoria</h3>
            <button @click="showCreateModal = false" :style="{ background: 'none', border: 'none', cursor: 'pointer', color: '#999' }">
              <X :size="24" />
            </button>
          </div>
          
          <form @submit.prevent="createSolicitud" :style="{ display: 'flex', flexDirection: 'column', gap: '20px' }">
            <!-- Título -->
            <div :style="fieldGroupStyle">
              <label :style="labelStyle">Título de la Convocatoria</label>
              <input 
                v-model="form.titulo" 
                type="text" 
                placeholder="Ej: Busco cantante de Trap/Urbano para un EP" 
                :style="inputStyle" 
                required 
              />
            </div>

            <!-- Género -->
            <div :style="fieldGroupStyle">
              <label :style="labelStyle">Género Musical Requerido</label>
              <select v-model="form.genero_musical" :style="inputStyle" required>
                <option value="" disabled>Selecciona un género...</option>
                <option value="Pop">Pop</option>
                <option value="Rock">Rock</option>
                <option value="Trap">Trap</option>
                <option value="Urbano">Urbano</option>
                <option value="Electrónica">Electrónica</option>
                <option value="Indie">Indie</option>
                <option value="Anime">Anime</option>
                <option value="Otro">Otro</option>
              </select>
            </div>

            <!-- Descripción -->
            <div :style="fieldGroupStyle">
              <label :style="labelStyle">Descripción del Proyecto y Requisitos</label>
              <textarea 
                v-model="form.descripcion" 
                placeholder="Detalla lo que buscas, el alcance del trabajo, los beneficios y requisitos para aplicar..." 
                :style="{ ...inputStyle, height: '90px', resize: 'none' }" 
                required
              ></textarea>
            </div>

            <!-- Limits row -->
            <div :style="{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }">
              <div :style="fieldGroupStyle">
                <label :style="labelStyle">Fecha Límite</label>
                <input 
                  v-model="form.fecha_limite" 
                  type="date" 
                  :style="inputStyle" 
                />
              </div>
              <div :style="fieldGroupStyle">
                <label :style="labelStyle">Límite de Artistas</label>
                <input 
                  v-model.number="form.limite_postulantes" 
                  type="number" 
                  placeholder="Sin límite" 
                  min="1"
                  :style="inputStyle" 
                />
              </div>
            </div>

            <!-- Imagen Cabecera Upload -->
            <div :style="fieldGroupStyle">
              <label :style="labelStyle">Imagen de Cabecera (Banner)</label>
              <div :style="{ display: 'flex', gap: '15px', alignItems: 'center' }">
                <button 
                  type="button" 
                  @click="triggerImageSelect" 
                  :style="{ padding: '10px 16px', backgroundColor: '#F3F4F6', color: '#1A1614', border: '1px solid #D1D5DB', borderRadius: '12px', cursor: 'pointer', fontWeight: '700', fontSize: '0.85rem' }"
                >
                  Seleccionar Imagen
                </button>
                <span v-if="selectedFileName" :style="{ fontSize: '0.85rem', color: '#666', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', maxWidth: '200px' }">
                  {{ selectedFileName }}
                </span>
                <input 
                  type="file" 
                  ref="imageInput" 
                  @change="handleFileUpload" 
                  accept="image/*" 
                  style="display: none" 
                />
              </div>
              
              <!-- Previsualización -->
              <div v-if="imagePreview" :style="{ marginTop: '10px', width: '100%', height: '120px', borderRadius: '15px', overflow: 'hidden', border: '1px solid #E5E7EB' }">
                <img :src="imagePreview" :style="{ width: '100%', height: '100%', objectFit: 'cover' }" />
              </div>
            </div>

            <!-- Botones de Acción -->
            <div :style="{ display: 'flex', gap: '15px', marginTop: '15px' }">
              <button type="button" @click="showCreateModal = false" :style="modalCancelBtnStyle">
                Cancelar
              </button>
              <button type="submit" :disabled="submitting" :style="modalCreateBtnStyle">
                {{ submitting ? 'Publicando...' : 'Publicar Convocatoria' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- CONTACT MODAL -->
      <div v-if="showContactModal" :style="modalOverlayStyle" @click.self="showContactModal = false">
        <div :style="{ ...modalContentStyle, width: '450px', textAlign: 'center' }">
          <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }">
            <h3 :style="{ fontSize: '1.4rem', fontWeight: '900', color: '#1A1614' }">Contactar Artista</h3>
            <button @click="showContactModal = false" :style="{ background: 'none', border: 'none', cursor: 'pointer', color: '#999' }">
              <X :size="20" />
            </button>
          </div>

          <div :style="{ marginBottom: '25px' }">
            <p :style="{ color: '#666', fontSize: '0.95rem', marginBottom: '15px' }">
              Puedes contactar a <strong>{{ contactArtist?.nombre_artistico || contactArtist?.nombre }}</strong> copiando su correo o redactando un correo en Gmail.
            </p>
            <div :style="{ backgroundColor: '#FAF9F8', padding: '15px', borderRadius: '15px', border: '1px solid #EEE', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '10px', wordBreak: 'break-all' }">
              <span :style="{ fontWeight: '700', color: '#1A1614', fontSize: '1.05rem' }">
                {{ contactArtist?.email }}
              </span>
            </div>
          </div>

          <div :style="{ display: 'flex', flexDirection: 'column', gap: '12px' }">
            <button 
              @click="copiarCorreo" 
              :style="{ 
                padding: '12px', 
                backgroundColor: copySuccess ? '#10B981' : '#1A1614', 
                color: '#FFF', 
                border: 'none', 
                borderRadius: '15px', 
                fontWeight: '700', 
                cursor: 'pointer',
                transition: '0.2s'
              }"
            >
              {{ copySuccess ? '¡Correo Copiado!' : 'Copiar correo' }}
            </button>

            <button 
              @click="redactarGmail" 
              :style="{ 
                padding: '12px', 
                backgroundColor: '#FF7A1A', 
                color: '#FFF', 
                border: 'none', 
                borderRadius: '15px', 
                fontWeight: '700', 
                cursor: 'pointer',
                transition: '0.2s'
              }"
            >
              Redactar en Gmail
            </button>
            
            <button 
              @click="showContactModal = false" 
              :style="{ 
                padding: '12px', 
                backgroundColor: '#F3F4F6', 
                color: '#4B5563', 
                border: 'none', 
                borderRadius: '15px', 
                fontWeight: '700', 
                cursor: 'pointer' 
              }"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Plus, Briefcase, ChevronDown, Users, Mail, X } from 'lucide-vue-next';
import axios from 'axios';
import Swal from 'sweetalert2';

// State
const solicitudes = ref([]);
const loading = ref(true);
const submitting = ref(false);
const showCreateModal = ref(false);

const expandedCollabs = ref({});
const postulantes = ref({});
const loadingPostulantes = ref({});

// Contact Modal State
const showContactModal = ref(false);
const contactArtist = ref(null);
const contactSol = ref(null);
const copySuccess = ref(false);

/**
 * Normaliza el _id de MongoDB a string puro.
 * Eloquent puede devolverlo como string, como objeto { $oid: '...' },
 * o con la propiedad id. Esta función cubre todos los casos.
 */
const getSolId = (sol) => {
  const raw = sol?._id || sol?.id;
  if (!raw) return null;
  // Si es objeto MongoDB { $oid: '...' } o { oid: '...' }
  if (typeof raw === 'object') return raw.$oid || raw.oid || String(raw);
  return String(raw);
};

const imageInput = ref(null);
const imageFile = ref(null);
const imagePreview = ref(null);
const selectedFileName = ref('');

const form = ref({
  titulo: '',
  genero_musical: '',
  descripcion: '',
  fecha_limite: '',
  limite_postulantes: null
});

// Load Producer Collabs
const loadSolicitudes = async () => {
  loading.value = true;
  try {
    const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
    const res = await axios.get('/api/colaboraciones/productor', { headers });
    solicitudes.value = res.data;
  } catch (error) {
    console.error('Error cargando convocatorias de productor', error);
  } finally {
    loading.value = false;
  }
};

// Open Create Modal
const openCreateModal = () => {
  form.value = { titulo: '', genero_musical: '', descripcion: '', fecha_limite: '', limite_postulantes: null };
  imageFile.value = null;
  imagePreview.value = null;
  selectedFileName.value = '';
  showCreateModal.value = true;
};

const triggerImageSelect = () => {
  imageInput.value.click();
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    imageFile.value = file;
    selectedFileName.value = file.name;
    imagePreview.value = URL.createObjectURL(file);
  }
};

// Create Request using FormData
const createSolicitud = async () => {
  submitting.value = true;
  
  const fd = new FormData();
  fd.append('titulo', form.value.titulo);
  fd.append('genero_musical', form.value.genero_musical);
  fd.append('descripcion', form.value.descripcion);
  
  if (form.value.fecha_limite) {
    fd.append('fecha_limite', form.value.fecha_limite);
  }
  if (form.value.limite_postulantes) {
    fd.append('limite_postulantes', form.value.limite_postulantes);
  }
  if (imageFile.value) {
    fd.append('imagen_cabecera', imageFile.value);
  }

  try {
    const headers = { 
      Authorization: `Bearer ${localStorage.getItem('token')}`,
      'Content-Type': 'multipart/form-data'
    };
    await axios.post('/api/colaboraciones/productor', fd, { headers });
    showCreateModal.value = false;
    await loadSolicitudes();
    Swal.fire({
      icon: 'success',
      title: '¡Convocatoria publicada!',
      text: 'Tu convocatoria ya está visible para los artistas de Mango Music.',
      confirmButtonColor: '#FF7A1A',
      borderRadius: '20px',
    });
  } catch (error) {
    console.error('Error al crear convocatoria de colaboración', error);
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors;
      const errorMsg = Object.values(errors).flat().join('<br>');
      Swal.fire({
        icon: 'warning',
        title: 'Errores de validación',
        html: errorMsg,
        confirmButtonColor: '#FF7A1A',
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Hubo un problema al publicar',
        text: 'Por favor, revisa los campos e inténtalo de nuevo. Si el error persiste, contacta soporte.',
        confirmButtonColor: '#FF7A1A',
      });
    }
  } finally {
    submitting.value = false;
  }
};

// Toggle status (activa/cerrada)
const toggleStatus = async (sol) => {
  const nuevoEstado = sol.estado === 'activa' ? 'cerrada' : 'activa';
  // Soporte doble formato de ID: MongoDB (_id) o Eloquent mapeado (id)
  const solId = sol._id || sol.id;
  if (!solId) {
    console.error('toggleStatus: convocatoria sin ID', JSON.stringify(sol));
    alert('No se pudo identificar la convocatoria. Recarga la página e intenta de nuevo.');
    return;
  }
  try {
    const headers = { 
      Authorization: `Bearer ${localStorage.getItem('token')}`,
      'Content-Type': 'application/json'
    };
    const res = await axios.patch(`/api/colaboraciones/productor/${solId}/estado`, {
      estado: nuevoEstado
    }, { headers });
    
    // Actualización reactiva: leer el campo del modelo refrescado que retorna el backend
    const estadoActualizado = res.data?.estado;
    if (estadoActualizado) {
      sol.estado = estadoActualizado;
    } else {
      // Fallback: si el backend no retornó el campo, forzamos el nuevo estado optimistamente
      sol.estado = nuevoEstado;
    }
  } catch (error) {
    console.error('Error cambiando el estado de la convocatoria', error);
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cambiar el estado. Verifica tu conexión e intenta de nuevo.', confirmButtonColor: '#FF7A1A' });
  }
};

// Toggle candidates panel — ahora acepta el objeto sol completo para extraer el ID de forma segura
const togglePostulantes = async (sol) => {
  const id = getSolId(sol);
  if (!id) {
    console.error('togglePostulantes: sol sin ID válido', sol);
    return;
  }
  if (expandedCollabs.value[id]) {
    expandedCollabs.value[id] = false;
  } else {
    expandedCollabs.value[id] = true;
    if (!postulantes.value[id]) {
      await loadPostulantes(id);
    }
  }
};

// Load specific candidates
const loadPostulantes = async (id) => {
  loadingPostulantes.value[id] = true;
  try {
    const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
    const res = await axios.get(`/api/colaboraciones/productor/${id}/postulantes`, { headers });
    // El backend devuelve el array directamente o dentro de una propiedad
    const data = res.data;
    postulantes.value[id] = Array.isArray(data) ? data : (data.postulantes || data.data || []);
    console.log(`[loadPostulantes] id=${id} -> cargados:`, postulantes.value[id].length, 'postulantes');
  } catch (error) {
    console.error('Error cargando candidatos', error.response?.data || error.message);
    postulantes.value[id] = [];
  } finally {
    loadingPostulantes.value[id] = false;
  }
};

// Update Candidate Status (Aceptar/Rechazar)
const updateCandidateStatus = async (sol, artistaId, nuevoEstado) => {
  const solId = typeof sol === 'object' ? getSolId(sol) : sol;
  try {
    const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
    await axios.patch(`/api/colaboraciones/productor/${solId}/postulantes/${artistaId}/estado`, {
      estado_postulacion: nuevoEstado
    }, { headers });
    
    // Reload candidates for this collaboration
    await loadPostulantes(solId);
  } catch (error) {
    console.error('Error al actualizar el estado del postulante', error);
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el estado del artista.', confirmButtonColor: '#FF7A1A' });
  }
};

// Abre modal de contacto con opciones
const contactarArtista = (postulante, sol) => {
  const email = postulante.email;
  if (!email) {
    Swal.fire({ icon: 'warning', title: 'Sin correo', text: 'No se encontró el correo de este artista.', confirmButtonColor: '#FF7A1A' });
    return;
  }
  contactArtist.value = postulante;
  contactSol.value = sol;
  copySuccess.value = false;
  showContactModal.value = true;
};

// Copiar correo al portapapeles
const copiarCorreo = async () => {
  if (contactArtist.value?.email) {
    try {
      await navigator.clipboard.writeText(contactArtist.value.email);
      copySuccess.value = true;
      setTimeout(() => {
        copySuccess.value = false;
      }, 2000);
    } catch (err) {
      console.error('Error al copiar correo:', err);
    }
  }
};

// Abrir enlace en Gmail
const redactarGmail = () => {
  const email = contactArtist.value?.email;
  const titulo = contactSol.value?.titulo || 'Convocatoria';
  const asunto = encodeURIComponent(`Consulta sobre Mango Music - Convocatoria: ${titulo}`);
  const url = `https://mail.google.com/mail/?view=cm&fs=1&to=${email}&su=${asunto}`;
  window.open(url, '_blank');
};

// Date validation check
const isExpired = (dateStr) => {
  if (!dateStr) return false;
  return new Date(dateStr) < new Date();
};

// Helper format date
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Status mapping
const translateStatus = (status) => {
  const mapping = {
    'pendiente': 'Pendiente',
    'aceptado': 'Aceptado',
    'rechazado': 'Rechazado'
  };
  return mapping[status] || 'Pendiente';
};

// Badge Styles helpers
const getGenreBadgeStyle = (genre) => {
  return {
    padding: '4px 10px',
    borderRadius: '12px',
    fontSize: '0.8rem',
    fontWeight: '700',
    textTransform: 'uppercase',
    backgroundColor: '#FFF5EE',
    color: '#FF7A1A'
  };
};

const getStatusBadgeStyle = (status) => {
  const isActive = status === 'activa';
  return {
    padding: '4px 10px',
    borderRadius: '12px',
    fontSize: '0.8rem',
    fontWeight: '700',
    textTransform: 'uppercase',
    backgroundColor: isActive ? '#EBFDF2' : '#F5F5F5',
    color: isActive ? '#10B981' : '#666'
  };
};

const getCandidateBadgeStyle = (status) => {
  let bgColor = '#FEF3C7';
  let color = '#D97706';
  
  if (status === 'aceptado') {
    bgColor = '#D1FAE5';
    color = '#059669';
  } else if (status === 'rechazado') {
    bgColor = '#FEE2E2';
    color = '#DC2626';
  }

  return {
    padding: '2px 8px',
    borderRadius: '10px',
    fontSize: '0.75rem',
    fontWeight: '700',
    textTransform: 'uppercase',
    backgroundColor: bgColor,
    color: color
  };
};

onMounted(() => {
  loadSolicitudes();
});

// STYLES
const containerStyle = {
  backgroundColor: '#FFF',
  padding: '50px',
  borderRadius: '40px',
  boxShadow: '0 20px 60px rgba(0,0,0,0.02)',
  minHeight: '80vh'
};

const createBtnStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '12px 25px',
  backgroundColor: '#FF7A1A',
  color: '#FFF',
  border: 'none',
  borderRadius: '30px',
  fontWeight: '800',
  cursor: 'pointer',
  transition: '0.2s',
  boxShadow: '0 4px 15px rgba(255, 122, 26, 0.15)'
};

const emptyStateStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '80px 20px',
  textAlign: 'center'
};

const cardStyle = {
  border: '1px solid #F0F0F0',
  borderRadius: '24px',
  padding: '30px',
  backgroundColor: '#FFF',
  boxShadow: '0 8px 24px rgba(0,0,0,0.01)',
  transition: '0.2s',
  overflow: 'hidden'
};

const bannerStyle = {
  width: 'calc(100% + 60px)',
  margin: '-30px -30px 25px -30px',
  height: '160px',
  backgroundColor: '#F3F4F6'
};

const expandBtnStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '8px',
  padding: '10px 18px',
  backgroundColor: '#F5F5F5',
  color: '#1A1614',
  border: 'none',
  borderRadius: '20px',
  fontWeight: '700',
  cursor: 'pointer',
  fontSize: '0.9rem'
};

const closeBtnStyle = {
  ...expandBtnStyle,
  backgroundColor: '#FFF1F1',
  color: '#EF4444'
};

const activateBtnStyle = {
  ...expandBtnStyle,
  backgroundColor: '#EBFDF2',
  color: '#10B981'
};

const acceptBtnStyle = {
  padding: '8px 16px',
  backgroundColor: '#10B981',
  color: '#FFF',
  border: 'none',
  borderRadius: '15px',
  fontWeight: '700',
  fontSize: '0.85rem',
  cursor: 'pointer',
  transition: '0.2s'
};

const rejectBtnStyle = {
  padding: '8px 16px',
  backgroundColor: '#EF4444',
  color: '#FFF',
  border: 'none',
  borderRadius: '15px',
  fontWeight: '700',
  fontSize: '0.85rem',
  cursor: 'pointer',
  transition: '0.2s'
};

const postulantesContainerStyle = {
  marginTop: '30px',
  paddingTop: '25px',
  borderTop: '1px dashed #F0F0F0'
};

const postulanteCardStyle = {
  backgroundColor: '#FAF9F8',
  borderRadius: '20px',
  padding: '20px',
  border: '1px solid #F5F5F5'
};

const artistPhotoStyle = {
  width: '60px',
  height: '60px',
  borderRadius: '50%',
  objectFit: 'cover',
  boxShadow: '0 4px 10px rgba(0,0,0,0.05)'
};

const contactBtnStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '6px',
  padding: '8px 16px',
  backgroundColor: '#FF7A1A',
  color: '#FFF',
  border: 'none',
  borderRadius: '15px',
  fontWeight: '700',
  fontSize: '0.85rem',
  cursor: 'pointer',
  textDecoration: 'none',
  transition: '0.2s'
};

const motivationBoxStyle = {
  backgroundColor: '#FFF',
  border: '1px solid #EEE',
  borderRadius: '15px',
  padding: '15px',
  marginTop: '15px'
};

// Modal styles
const modalOverlayStyle = {
  position: 'fixed',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  backgroundColor: 'rgba(0,0,0,0.4)',
  backdropFilter: 'blur(8px)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: 6000
};

const modalContentStyle = {
  backgroundColor: '#FFF',
  padding: '40px',
  borderRadius: '35px',
  width: '520px',
  maxWidth: '90%',
  boxShadow: '0 25px 50px rgba(0,0,0,0.1)',
  maxHeight: '90vh',
  overflowY: 'auto'
};

const fieldGroupStyle = {
  display: 'flex',
  flexDirection: 'column',
  gap: '8px'
};

const labelStyle = {
  fontSize: '0.9rem',
  fontWeight: '700',
  color: '#1A1614'
};

const inputStyle = {
  padding: '12px 18px',
  borderRadius: '15px',
  border: '1px solid #E5E7EB',
  backgroundColor: '#F9FAFB',
  fontSize: '0.95rem',
  outline: 'none',
  width: '100%',
  transition: '0.2s'
};

const modalCancelBtnStyle = {
  flex: 1,
  padding: '14px',
  backgroundColor: '#F3F4F6',
  color: '#4B5563',
  border: 'none',
  borderRadius: '18px',
  fontWeight: '800',
  cursor: 'pointer'
};

const modalCreateBtnStyle = {
  flex: 1,
  padding: '14px',
  backgroundColor: '#FF7A1A',
  color: '#FFF',
  border: 'none',
  borderRadius: '18px',
  fontWeight: '800',
  cursor: 'pointer'
};

const loaderStyle = {
  width: '40px',
  height: '40px',
  border: '4px solid #F3F4F6',
  borderTop: '4px solid #FF7A1A',
  borderRadius: '50%',
  animation: 'spin 1s linear infinite',
  margin: '0 auto'
};
</script>

<style scoped>
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.loader {
  animation: spin 1s linear infinite;
}
</style>
