<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
    <!-- Header -->
    <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '40px' }">
      <h1 :style="{ fontSize: '2.2rem', fontWeight: '900', color: '#1A1614', letterSpacing: '-0.5px', margin: 0 }">Todos los Eventos</h1>
      <button @click="router.back()" :style="backBtnStyle">
        <ArrowLeft :size="18" /> Volver
      </button>
    </div>

    <!-- Filters -->
    <div :style="filtersContainerStyle">
      <div :style="searchBoxStyle">
        <Search :size="20" :style="{ color: '#FF7A1A' }" />
        <input v-model="searchQuery" type="text" placeholder="Buscar por nombre o lugar del evento..." :style="searchInputStyle" />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" :style="{ textAlign: 'center', padding: '50px', color: '#FF7A1A' }">
      Cargando eventos...
    </div>

    <!-- Empty State -->
    <div v-else-if="events.length === 0" :style="{ textAlign: 'center', padding: '50px', backgroundColor: '#FFF', borderRadius: '30px', border: '1px solid #F0F0F0' }">
      <p :style="{ fontSize: '1.2rem', fontWeight: '600', color: '#A0A0A0' }">No se encontraron eventos programados.</p>
    </div>

    <!-- Empty Search Results State -->
    <div v-else-if="eventosFiltrados.length === 0" :style="{ textAlign: 'center', padding: '50px', backgroundColor: '#FFF', borderRadius: '30px', border: '1px solid #F0F0F0' }">
      <p :style="{ fontSize: '1.2rem', fontWeight: '600', color: '#A0A0A0' }">No se encontraron eventos que coincidan con tu búsqueda.</p>
    </div>

    <!-- Events Grid -->
    <div v-else :style="{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '30px' }">
      <div v-for="evento in eventosFiltrados" :key="evento.id" class="tarjeta-evento" :style="eventCardStyle">
        <div :style="eventImageStyle(evento.imagen)"></div>
        <div :style="{ padding: '20px' }">
          <h4 :style="{ fontWeight: '900', color: '#1A1614', fontSize: '1.2rem', marginBottom: '10px' }">{{ evento.nombre }}</h4>
          <p v-if="evento.cantante_invitado" :style="{ fontSize: '0.9rem', color: '#FF7A1A', fontWeight: '800', marginBottom: '15px' }">
            Invitado: {{ evento.cantante_invitado }}
          </p>
          <div :style="{ color: '#666', fontSize: '0.95rem', display: 'flex', flexDirection: 'column', gap: '10px' }">
            <div :style="{ display: 'flex', alignItems: 'center', gap: '10px' }">
              <MapPin :size="18" :style="{ color: '#A0A0A0' }" /> {{ evento.lugar }}
            </div>
            <div :style="{ display: 'flex', alignItems: 'center', gap: '10px' }">
              <Calendar :size="18" :style="{ color: '#A0A0A0' }" /> {{ evento.fecha }} a las {{ evento.hora }}
            </div>
          </div>
          <div :style="{ marginTop: '20px', paddingTop: '15px', borderTop: '1px solid #F0F0F0', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }">
            <span :style="{ color: '#1A1614', fontWeight: '900', fontSize: '1.1rem' }">
              {{ evento.es_gratis ? 'ENTRADA LIBRE' : '$' + evento.precio }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Search, MapPin, Calendar, ArrowLeft } from 'lucide-vue-next';
import axios from 'axios';

const router = useRouter();
const events = ref([]);
const searchQuery = ref('');
const loading = ref(true);

const fetchEvents = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/eventos');
    events.value = (response.data.data || response.data).sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
  } catch (error) {
    console.error('Error fetching events:', error);
  } finally {
    loading.value = false;
  }
};

const eventosFiltrados = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  if (!query) return events.value;
  return events.value.filter(evento => {
    const nombre = (evento.nombre || evento.titulo || '').toLowerCase();
    return nombre.includes(query);
  });
});

onMounted(() => {
  fetchEvents();
});

// Styles
const backBtnStyle = { padding: '10px 20px', backgroundColor: '#FFF', border: '1px solid #E5E5E5', borderRadius: '12px', fontWeight: '800', color: '#1A1614', display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer', boxShadow: '0 4px 15px rgba(0,0,0,0.02)' };
const filtersContainerStyle = { display: 'flex', gap: '20px', marginBottom: '40px' };
const searchBoxStyle = { flex: 1, maxWidth: '600px', display: 'flex', alignItems: 'center', gap: '15px', backgroundColor: '#FFF', padding: '14px 25px', borderRadius: '22px', border: '1px solid #E5E5E5', boxShadow: '0 10px 30px rgba(0,0,0,0.02)' };
const searchInputStyle = { border: 'none', outline: 'none', backgroundColor: 'transparent', fontSize: '1rem', fontWeight: '600', width: '100%', color: '#1A1614' };

const eventCardStyle = { backgroundColor: '#FFF', borderRadius: '30px', overflow: 'hidden', boxShadow: '0 15px 45px rgba(0,0,0,0.03)', border: '1px solid #F0F0F0', transition: '0.3s', cursor: 'default' };
const eventImageStyle = (img) => ({ width: '100%', height: '200px', backgroundImage: `url(${img || 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800&q=80'})`, backgroundSize: 'cover', backgroundPosition: 'center' });
</script>

<style scoped>
.tarjeta-evento:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.06) !important; }
</style>
