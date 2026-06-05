<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
    <div :style="containerStyle">
      
      <!-- Header -->
      <div :style="{ marginBottom: '40px' }">
        <h2 :style="{ fontSize: '2rem', fontWeight: '900', color: '#1A1614', letterSpacing: '-0.5px' }">
          Oportunidades de Colaboración
        </h2>
        <p :style="{ color: '#666', marginTop: '5px', fontWeight: '500' }">
          Explora convocatorias abiertas por productores y postúlate enviando un mensaje directo.
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" :style="{ textAlign: 'center', padding: '50px' }">
        <div class="loader" :style="loaderStyle"></div>
        <p :style="{ color: '#666', marginTop: '15px', fontWeight: '600' }">Cargando convocatorias disponibles...</p>
      </div>

      <!-- No Convocatorias State -->
      <div v-else-if="solicitudes.length === 0" :style="emptyStateStyle">
        <Briefcase :size="60" :style="{ color: '#FF7A1A', marginBottom: '20px', opacity: 0.8 }" />
        <h3 :style="{ fontSize: '1.4rem', fontWeight: '800', color: '#1A1614', marginBottom: '10px' }">No hay convocatorias activas</h3>
        <p :style="{ color: '#A0A0A0', maxWidth: '400px' }">
          Vuelve más tarde para ver nuevas ofertas de colaboración publicadas por productores.
        </p>
      </div>

      <!-- Active Convocatorias Grid -->
      <div v-else :style="gridStyle">
        <div v-for="sol in solicitudes" :key="sol._id" :style="cardStyle">
          
          <!-- Banner image if uploaded -->
          <div v-if="sol.imagen_cabecera" :style="bannerStyle">
            <img :src="sol.imagen_cabecera" :style="{ width: '100%', height: '100%', objectFit: 'cover' }" />
          </div>

          <!-- Card Top -->
          <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '15px' }">
            <span :style="genreBadgeStyle">{{ sol.genero_musical }}</span>
            <span :style="vacancyBadgeStyle">
              {{ sol.postulantes ? sol.postulantes.length : 0 }}
              {{ sol.limite_postulantes ? `/ ${sol.limite_postulantes}` : '' }} Candidatos
            </span>
          </div>

          <!-- Title & Description -->
          <h3 :style="{ fontSize: '1.3rem', fontWeight: '800', color: '#1A1614', marginBottom: '10px', minHeight: '52px' }">
            {{ sol.titulo }}
          </h3>
          
          <p :style="descStyle">
            {{ sol.descripcion }}
          </p>

          <!-- Producer Information -->
          <div :style="producerContainerStyle">
            <img 
              :src="sol.productor?.foto || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&h=100&fit=crop'" 
              :style="producerPhotoStyle" 
            />
            <div>
              <div :style="{ fontWeight: '800', color: '#1A1614', fontSize: '0.95rem' }">
                {{ sol.productor?.nombre_productor || sol.productor?.nombre || 'Productor' }}
              </div>
              <div :style="{ fontSize: '0.8rem', color: '#888', fontWeight: '600' }">
                Publicado el {{ formatDate(sol.created_at) }}
              </div>
            </div>
          </div>

          <!-- Card Footer Details -->
          <div :style="cardFooterStyle">
            <div v-if="sol.fecha_limite" :style="{ fontSize: '0.85rem', fontWeight: '700', color: isExpired(sol.fecha_limite) ? '#EF4444' : '#666' }">
              Límite: {{ formatDate(sol.fecha_limite) }}
            </div>
            <div v-else :style="{ fontSize: '0.85rem', fontWeight: '700', color: '#666' }">
              Sin fecha límite
            </div>

            <!-- Postularse trigger -->
            <button 
              @click="openApplyModal(sol)" 
              :disabled="hasApplied(sol) || isFull(sol) || isExpired(sol.fecha_limite)"
              :style="getApplyBtnStyle(sol)"
            >
              {{ getApplyBtnText(sol) }}
            </button>
          </div>
        </div>
      </div>

      <!-- APPLY MODAL -->
      <div v-if="showApplyModal" :style="modalOverlayStyle" @click.self="showApplyModal = false">
        <div :style="modalContentStyle">
          <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '25px' }">
            <h3 :style="{ fontSize: '1.5rem', fontWeight: '900', color: '#1A1614' }">Postularse a Colaboración</h3>
            <button @click="showApplyModal = false" :style="{ background: 'none', border: 'none', cursor: 'pointer', color: '#999' }">
              <X :size="24" />
            </button>
          </div>

          <p :style="{ color: '#555', marginBottom: '20px', fontSize: '0.95rem', lineHeight: '1.5' }">
            Estás a punto de postularte a la convocatoria de <strong :style="{ color: '#FF7A1A' }">{{ selectedSolicitud?.titulo }}</strong>. 
            Escribe un mensaje explicándole al productor por qué eres el candidato perfecto para este proyecto.
          </p>

          <form @submit.prevent="submitPostulacion" :style="{ display: 'flex', flexDirection: 'column', gap: '20px' }">
            <div :style="fieldGroupStyle">
              <label :style="labelStyle">Mensaje Motivacional</label>
              <textarea 
                v-model="mensaje" 
                placeholder="Escribe tu mensaje motivacional, enlaces a tus trabajos o demos aquí..." 
                :style="{ ...inputStyle, height: '140px', resize: 'none' }" 
                required
              ></textarea>
            </div>

            <div :style="{ display: 'flex', gap: '15px', marginTop: '10px' }">
              <button type="button" @click="showApplyModal = false" :style="modalCancelBtnStyle">
                Cancelar
              </button>
              <button type="submit" :disabled="submitting" :style="modalCreateBtnStyle">
                {{ submitting ? 'Enviando...' : 'Enviar Postulación' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Briefcase, X } from 'lucide-vue-next';
import axios from 'axios';

// State
const solicitudes = ref([]);
const loading = ref(true);
const submitting = ref(false);
const showApplyModal = ref(false);
const selectedSolicitud = ref(null);
const mensaje = ref('');
const currentUserId = ref('');

// Load userId
const loadUser = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  currentUserId.value = user._id || user.id || '';
};

// Load active solicituds
const loadSolicitudes = async () => {
  loading.value = true;
  try {
    const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
    const res = await axios.get('/api/colaboraciones/activas', { headers });
    solicitudes.value = res.data;
  } catch (error) {
    console.error('Error cargando convocatorias activas', error);
  } finally {
    loading.value = false;
  }
};

// Open Apply Modal
const openApplyModal = (sol) => {
  // Clonamos con spread para evitar referencias reactivas pérdidas
  selectedSolicitud.value = { ...sol };
  mensaje.value = '';
  showApplyModal.value = true;
};

// Submit applying request
const submitPostulacion = async () => {
  if (!mensaje.value.trim()) {
    alert('Por favor escribe un mensaje motivacional antes de enviar.');
    return;
  }
  submitting.value = true;
  try {
    const token = localStorage.getItem('token');
    // Soporte doble formato: MongoDB devuelve _id, Eloquent a veces lo mapea como id
    const solId = selectedSolicitud.value?._id || selectedSolicitud.value?.id;

    if (!solId) {
      // Debug: imprimir el objeto completo para diagnosticar
      console.error('selectedSolicitud sin ID:', JSON.stringify(selectedSolicitud.value));
      alert('Error: No se pudo identificar la convocatoria seleccionada.');
      submitting.value = false;
      return;
    }

    await axios.post(
      `/api/colaboraciones/${solId}/aplicar`,
      { mensaje_motivacional: mensaje.value },
      { 
        headers: { 
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json'
        } 
      }
    );

    showApplyModal.value = false;
    alert('¡Tu postulación ha sido enviada con éxito!');
    await loadSolicitudes();
  } catch (error) {
    console.error('Error al enviar postulación:', error.response?.data || error.message);
    const apiError = error.response?.data?.error || 'Hubo un error al enviar tu postulación. Intenta nuevamente.';
    alert(apiError);
  } finally {
    submitting.value = false;
  }
};

// Helper validations
const hasApplied = (sol) => {
  if (!sol.postulantes) return false;
  return sol.postulantes.some(p => (p.artista_id || '').toString() === currentUserId.value.toString());
};

const isFull = (sol) => {
  if (!sol.limite_postulantes) return false;
  const count = sol.postulantes ? sol.postulantes.length : 0;
  return count >= parseInt(sol.limite_postulantes);
};

const isExpired = (dateStr) => {
  if (!dateStr) return false;
  return new Date(dateStr) < new Date();
};

const getApplyBtnText = (sol) => {
  if (hasApplied(sol)) return 'Ya Postulado';
  if (isFull(sol)) return 'Límite Alcanzado';
  if (isExpired(sol.fecha_limite)) return 'Expirado';
  return 'Postularse';
};

const getApplyBtnStyle = (sol) => {
  const disabled = hasApplied(sol) || isFull(sol) || isExpired(sol.fecha_limite);
  return {
    padding: '8px 18px',
    backgroundColor: disabled ? '#E5E7EB' : '#FF7A1A',
    color: disabled ? '#9CA3AF' : '#FFF',
    border: 'none',
    borderRadius: '15px',
    fontWeight: '800',
    fontSize: '0.85rem',
    cursor: disabled ? 'not-allowed' : 'pointer',
    transition: '0.2s'
  };
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

onMounted(() => {
  loadUser();
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

const emptyStateStyle = {
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: '100px 20px',
  textAlign: 'center'
};

const gridStyle = {
  display: 'grid',
  gridTemplateColumns: 'repeat(auto-fill, minmax(320px, 1fr))',
  gap: '30px'
};

const cardStyle = {
  border: '1px solid #F0F0F0',
  borderRadius: '24px',
  padding: '25px',
  backgroundColor: '#FFF',
  boxShadow: '0 8px 24px rgba(0,0,0,0.01)',
  display: 'flex',
  flexDirection: 'column',
  transition: 'transform 0.2s',
  cursor: 'default',
  overflow: 'hidden'
};

const bannerStyle = {
  width: 'calc(100% + 50px)',
  margin: '-25px -25px 20px -25px',
  height: '140px',
  backgroundColor: '#F3F4F6'
};

const genreBadgeStyle = {
  padding: '4px 10px',
  borderRadius: '12px',
  fontSize: '0.75rem',
  fontWeight: '700',
  textTransform: 'uppercase',
  backgroundColor: '#FFF5EE',
  color: '#FF7A1A'
};

const vacancyBadgeStyle = {
  ...genreBadgeStyle,
  backgroundColor: '#EBFDF2',
  color: '#10B981'
};

const descStyle = {
  color: '#666',
  fontSize: '0.95rem',
  lineHeight: '1.5',
  marginBottom: '20px',
  overflow: 'hidden',
  display: '-webkit-box',
  webkitLineClamp: 3,
  webkitBoxOrient: 'vertical',
  minHeight: '66px'
};

const producerContainerStyle = {
  display: 'flex',
  alignItems: 'center',
  gap: '12px',
  paddingTop: '15px',
  borderTop: '1px solid #F3F4F6',
  marginBottom: '20px',
  marginTop: 'auto'
};

const producerPhotoStyle = {
  width: '38px',
  height: '38px',
  borderRadius: '50%',
  objectFit: 'cover'
};

const cardFooterStyle = {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center'
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
  width: '500px',
  maxWidth: '90%',
  boxShadow: '0 25px 50px rgba(0,0,0,0.1)'
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
