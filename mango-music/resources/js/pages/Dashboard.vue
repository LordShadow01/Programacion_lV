<template>
  <main class="contenido-principal" :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
    <!-- Search & User Bar -->
    <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '40px' }">
      <div :style="searchBoxStyle">
        <Search :size="20" :style="{ color: '#FF7A1A' }" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="¿Qué quieres escuchar hoy?"
          :style="searchInputStyle"
        />
      </div>
      <div :style="{ display: 'flex', alignItems: 'center', gap: '15px' }">
        <div :style="userBadgeStyle" @click="router.push('/perfil')" style="cursor: pointer;">
          <div :style="{ width: '32px', height: '32px', borderRadius: '10px', overflow: 'hidden', backgroundColor: '#FFF9F5', display: 'flex', alignItems: 'center', justifyContent: 'center' }">
            <img v-if="userPhoto" :src="userPhoto" :style="{ width: '100%', height: '100%', objectFit: 'cover' }" />
            <User v-else :size="18" :style="{ color: '#FF7A1A' }" />
          </div>
          <span :style="{ fontWeight: '800', fontSize: '1rem', color: '#1A1614' }">{{ userName }}</span>
        </div>
      </div>
    </div>

    <!-- ARTIST STATS BOX -->
    <div v-if="userRole === 'artista' && !isSearching" :style="statsBoxStyle">
      <div :style="statItemStyle">
        <div :style="statValueStyle">{{ artistStats.seguidores }}</div>
        <div :style="statLabelStyle">Seguidores</div>
      </div>
      <div :style="statDividerStyle"></div>
      <div :style="statItemStyle">
        <div :style="statValueStyle">{{ artistStats.likes }}</div>
        <div :style="statLabelStyle">Me gustas</div>
      </div>
      <div :style="statDividerStyle"></div>
      <div :style="statItemStyle">
        <div :style="statValueStyle">{{ artistStats.publicaciones }}</div>
        <div :style="statLabelStyle">Publicaciones</div>
      </div>
    </div>

    <!-- GLOBAL SEARCH RESULTS VIEW -->
    <div v-if="isSearching">
      <h2 :style="{ fontSize: '2rem', fontWeight: '900', color: '#1A1614', letterSpacing: '-0.5px', marginBottom: '30px' }">
        Resultados de Búsqueda para "{{ searchQuery }}"
      </h2>
      
      <!-- Songs Results -->
      <h3 :style="{ fontSize: '1.4rem', fontWeight: '800', color: '#666', marginBottom: '20px' }">Canciones Encontradas</h3>
      <div :style="{ display: 'flex', flexWrap: 'wrap', gap: '30px', marginBottom: '40px' }">
        <div v-if="searchSongsResults.length === 0" :style="{ width: '100%', padding: '20px', color: '#A0A0A0' }">
          No se encontraron canciones.
        </div>
        <div
          v-for="song in searchSongsResults"
          :key="song.id"
          @click="playSong(song, searchSongsResults)"
          class="song-card-premium"
          :style="songCardStyle"
        >
          <div :style="songImageStyle(song.image)">
            <div class="play-overlay-icon" :style="playOverlayStyle">
              <Play :size="45" fill="#FFF" color="#FFF" />
            </div>
          </div>
          <div :style="{ marginTop: '18px', display: 'flex', flexDirection: 'column' }">
            <div :style="{ fontWeight: '800', color: '#1A1614', fontSize: '1.1rem' }">{{ song.title }}</div>
            <div
              @click.stop="router.push(`/artista/${song.artist_id}`)"
              :style="{ fontSize: '1rem', color: '#A0A0A0', fontWeight: '600', marginTop: '2px', cursor: 'pointer' }"
            >{{ song.artist }}</div>
          </div>
        </div>
      </div>

      <!-- Events Results -->
      <h3 :style="{ fontSize: '1.4rem', fontWeight: '800', color: '#666', marginBottom: '20px' }">Eventos Encontrados</h3>
      <div :style="{ display: 'flex', flexWrap: 'wrap', gap: '30px' }">
        <div v-if="searchEventsResults.length === 0" :style="{ width: '100%', padding: '20px', color: '#A0A0A0' }">
          No se encontraron eventos.
        </div>
        <div v-for="evento in searchEventsResults" :key="evento.id" class="tarjeta-evento" :style="eventCardStyle">
          <div :style="eventImageStyle(evento.imagen)"></div>
          <div :style="{ padding: '20px' }">
            <h4 :style="{ fontWeight: '800', color: '#1A1614', fontSize: '1.1rem', marginBottom: '10px' }">{{ evento.nombre }}</h4>
            <div :style="{ color: '#666', fontSize: '0.9rem', display: 'flex', flexDirection: 'column', gap: '8px' }">
              <div :style="{ display: 'flex', alignItems: 'center', gap: '8px' }">
                <MapPin :size="16" :style="{ color: '#FF7A1A' }" /> {{ evento.lugar }}
              </div>
              <div :style="{ display: 'flex', alignItems: 'center', gap: '8px' }">
                <Calendar :size="16" :style="{ color: '#FF7A1A' }" /> {{ evento.fecha }}
              </div>
            </div>
            <div :style="{ marginTop: '15px', color: '#FF7A1A', fontWeight: '800' }">
              {{ evento.es_gratis ? 'GRATIS' : '$' + evento.precio }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- NORMAL DASHBOARD VIEW -->
    <div v-else>
      <!-- GLOBAL TRENDS SECTION -->
      <div :style="{ marginTop: '50px', marginBottom: '30px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }">
        <h2 :style="{ fontSize: '1.8rem', fontWeight: '900', color: '#1A1614', letterSpacing: '-0.5px', margin: 0 }">Tendencias Globales</h2>
        <span @click="router.push('/tendencias')" :style="{ color: '#FF7A1A', fontWeight: '800', cursor: 'pointer', fontSize: '1rem', textTransform: 'uppercase' }">Explorar Todo</span>
      </div>

      <div :style="{ display: 'flex', flexWrap: 'wrap', gap: '30px' }">
        <div v-if="globalSongs.length === 0" :style="{ width: '100%', textAlign: 'center', padding: '50px', color: '#A0A0A0' }">
          <p v-if="userRole === 'artista'" :style="{ fontSize: '1.2rem', fontWeight: '600' }">Aún no hay música publicada. ¡Sé el primero en subir una canción!</p>
          <p v-else :style="{ fontSize: '1.2rem', fontWeight: '600' }">No se encontró música aún. Explora a tus artistas favoritos.</p>
        </div>
        <div
          v-for="song in globalSongs"
          :key="song.id"
          @click="playSong(song, globalSongs)"
          class="song-card-premium"
          :style="songCardStyle"
        >
          <div :style="songImageStyle(song.image)">
            <div class="play-overlay-icon" :style="playOverlayStyle">
              <Play :size="45" fill="#FFF" color="#FFF" />
            </div>
          </div>
          <div :style="{ marginTop: '18px', display: 'flex', flexDirection: 'column' }">
            <div :style="{ fontWeight: '800', color: '#1A1614', fontSize: '1.1rem' }">{{ song.title }}</div>
            <div
              @click.stop="router.push(`/artista/${song.artist_id}`)"
              :style="{ fontSize: '1rem', color: '#A0A0A0', fontWeight: '600', marginTop: '2px', cursor: 'pointer' }"
            >{{ song.artist }}</div>
          </div>
        </div>
      </div>

      <!-- UPCOMING EVENTS SECTION -->
      <div :style="{ marginTop: '60px', marginBottom: '30px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }">
        <h2 :style="{ fontSize: '1.8rem', fontWeight: '900', color: '#1A1614', letterSpacing: '-0.5px', margin: 0 }">Próximos Eventos</h2>
        <span @click="router.push('/eventos/todos')" :style="{ color: '#FF7A1A', fontWeight: '800', cursor: 'pointer', fontSize: '1rem', textTransform: 'uppercase' }">Ver Todos</span>
      </div>

      <div :style="{ display: 'flex', flexWrap: 'wrap', gap: '30px' }">
        <div v-if="globalEvents.length === 0" :style="{ width: '100%', textAlign: 'center', padding: '50px', color: '#A0A0A0' }">
          <p :style="{ fontSize: '1.2rem', fontWeight: '600' }">No hay eventos programados próximamente.</p>
        </div>
        <div v-for="evento in globalEvents" :key="evento.id" class="tarjeta-evento" :style="eventCardStyle">
          <div :style="eventImageStyle(evento.imagen)"></div>
          <div :style="{ padding: '20px' }">
            <h4 :style="{ fontWeight: '800', color: '#1A1614', fontSize: '1.1rem', marginBottom: '10px' }">{{ evento.nombre }}</h4>
            <div :style="{ color: '#666', fontSize: '0.9rem', display: 'flex', flexDirection: 'column', gap: '8px' }">
              <div :style="{ display: 'flex', alignItems: 'center', gap: '8px' }">
                <MapPin :size="16" :style="{ color: '#FF7A1A' }" /> {{ evento.lugar }}
              </div>
              <div :style="{ display: 'flex', alignItems: 'center', gap: '8px' }">
                <Calendar :size="16" :style="{ color: '#FF7A1A' }" /> {{ evento.fecha }}
              </div>
            </div>
            <div :style="{ marginTop: '15px', color: '#FF7A1A', fontWeight: '800' }">
              {{ evento.es_gratis ? 'GRATIS' : '$' + evento.precio }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Search, User, Play, MapPin, Calendar } from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { playSong } from '@/playerState';

const router = useRouter();
const searchQuery = ref('');
const globalSongs = ref([]);
const globalEvents = ref([]);

const isSearching = ref(false);
const searchSongsResults = ref([]);
const searchEventsResults = ref([]);

let user = null;
try {
  const userRaw = localStorage.getItem('user');
  user = userRaw ? JSON.parse(userRaw) : null;
} catch (e) {
  user = null;
}

const userName = ref(user ? user.nombre : 'Invitado');
const userPhoto = ref(user ? user.foto : null);
const userRole = ref(user ? user.rol : 'oyente');

const fetchSongs = async () => {
  try {
    const response = await axios.get('/api/canciones');
    globalSongs.value = (response.data.data || response.data).map(song => ({
      id: song.id,
      title: song.titulo,
      artist: song.artista,
      artist_id: song.user_id,
      image: song.caratula || 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=400&h=400&fit=crop',
      url_audio: song.url_audio
    })).slice(0, 4); // Only show top 4 on dashboard
  } catch (error) {
    console.error('Error fetching songs:', error);
  }
};

const fetchEvents = async () => {
  try {
    const response = await axios.get('/api/eventos');
    globalEvents.value = response.data.data ? response.data.data.sort((a, b) => new Date(a.fecha) - new Date(b.fecha)).slice(0, 4) : response.data.sort((a, b) => new Date(a.fecha) - new Date(b.fecha)).slice(0, 4);
  } catch (error) {
    console.error('Error fetching events:', error);
  }
};

const handleSearch = async () => {
  const q = searchQuery.value.trim();
  if (q.length === 0) {
    isSearching.value = false;
    searchSongsResults.value = [];
    searchEventsResults.value = [];
    return;
  }

  isSearching.value = true;
  try {
    const [songsRes, eventsRes] = await Promise.all([
      axios.get('/api/buscar/canciones', { params: { q } }),
      axios.get('/api/buscar/eventos', { params: { q } })
    ]);

    searchSongsResults.value = (songsRes.data.data || songsRes.data).map(song => ({
      id: song.id,
      title: song.titulo,
      artist: song.artista,
      artist_id: song.user_id,
      image: song.caratula || 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=400&h=400&fit=crop',
      url_audio: song.url_audio
    }));

    searchEventsResults.value = (eventsRes.data.data || eventsRes.data);
  } catch (error) {
    console.error('Error searching:', error);
  }
};

let searchTimeout = null;
watch(searchQuery, (newVal) => {
  // Instantly clear results when query is emptied
  if (!newVal.trim()) {
    if (searchTimeout) clearTimeout(searchTimeout);
    isSearching.value = false;
    searchSongsResults.value = [];
    searchEventsResults.value = [];
    return;
  }
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    handleSearch();
  }, 300);
});

const artistStats = ref({ seguidores: 0, likes: 0, publicaciones: 0 });

const fetchStats = async () => {
  try {
    if (userRole.value === 'artista' && user) {
      const songsRes = await axios.get('/api/canciones');
      const userSongs = (songsRes.data.data || songsRes.data).filter(s => s.user_id == user.id);
      artistStats.value.publicaciones = userSongs.length;

      const followersRes = await axios.get(`/api/seguidores/count/${user.id}`);
      artistStats.value.seguidores = followersRes.data.count || 0;

      const likesRes = await axios.get(`/api/likes/count/artista/${user.id}`);
      artistStats.value.likes = likesRes.data.count || 0;
    }
  } catch (error) {
    console.error('Error fetching stats:', error);
  }
};

onMounted(() => {
  fetchSongs();
  fetchEvents();
  fetchStats();
});

// STYLES
const searchBoxStyle = { display: 'flex', alignItems: 'center', gap: '15px', backgroundColor: '#FFF', padding: '14px 25px', borderRadius: '22px', width: '550px', boxShadow: '0 10px 40px rgba(0,0,0,0.03)', border: '1px solid #F0F0F0' };
const searchInputStyle = { border: 'none', outline: 'none', backgroundColor: 'transparent', fontSize: '1rem', fontWeight: '600', width: '100%', color: '#1A1614' };
const userBadgeStyle = { display: 'flex', alignItems: 'center', gap: '12px', backgroundColor: '#FFF', padding: '12px 25px', borderRadius: '18px', boxShadow: '0 10px 30px rgba(0,0,0,0.02)', border: '1px solid #F0F0F0' };
const statsBoxStyle = { backgroundColor: '#FFF', padding: '30px', borderRadius: '35px', boxShadow: '0 15px 45px rgba(0,0,0,0.02)', border: '1px solid #F8F8F8', display: 'flex', alignItems: 'center', justifyContent: 'space-around', maxWidth: '700px' };
const statItemStyle = { textAlign: 'center', flex: 1 };
const statValueStyle = { fontSize: '2rem', fontWeight: '900', color: '#1A1614' };
const statLabelStyle = { fontSize: '1rem', color: '#A0A0A0', fontWeight: '700', textTransform: 'uppercase', marginTop: '5px' };
const statDividerStyle = { width: '1.5px', height: '40px', backgroundColor: '#F0F0F0' };
const songCardStyle = { flex: '1 1 220px', minWidth: '220px', maxWidth: '280px', backgroundColor: '#FFF', padding: '22px', borderRadius: '35px', boxShadow: '0 15px 45px rgba(0,0,0,0.03)', cursor: 'pointer', transition: '0.4s', border: '1px solid #F8F8F8', display: 'flex', flexDirection: 'column' };
const songImageStyle = (img) => ({ width: '100%', height: '200px', backgroundImage: `url(${img})`, backgroundSize: 'cover', backgroundPosition: 'center', borderRadius: '25px', position: 'relative', overflow: 'hidden' });
const playOverlayStyle = { position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', backgroundColor: 'rgba(0,0,0,0.25)', display: 'flex', alignItems: 'center', justifyContent: 'center', opacity: 0, transition: '0.3s' };
const eventCardStyle = { flex: '1 1 280px', minWidth: '280px', maxWidth: '320px', backgroundColor: '#FFF', borderRadius: '30px', overflow: 'hidden', boxShadow: '0 15px 45px rgba(0,0,0,0.03)', border: '1px solid #F8F8F8' };
const eventImageStyle = (img) => ({ width: '100%', height: '180px', backgroundImage: `url(${img || 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800&q=80'})`, backgroundSize: 'cover', backgroundPosition: 'center' });
</script>

<style scoped>
.song-card-premium:hover { transform: translateY(-10px); }
.song-card-premium:hover .play-overlay-icon { opacity: 1 !important; }
</style>
