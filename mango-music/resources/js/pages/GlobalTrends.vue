<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
    <!-- Header -->
    <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '40px' }">
      <h1 :style="{ fontSize: '2.2rem', fontWeight: '900', color: '#1A1614', letterSpacing: '-0.5px', margin: 0 }">Explorar Todo</h1>
      <button @click="router.back()" :style="backBtnStyle">
        <ArrowLeft :size="18" /> Volver
      </button>
    </div>

    <!-- Filters -->
    <div :style="filtersContainerStyle">
      <div :style="searchBoxStyle">
        <Search :size="20" :style="{ color: '#FF7A1A' }" />
        <input v-model="searchQuery" type="text" placeholder="Buscar por título o artista..." :style="searchInputStyle" />
      </div>
      <div :style="genreSelectBoxStyle">
        <Filter :size="20" :style="{ color: '#FF7A1A' }" />
        <select v-model="selectedGenre" :style="selectStyle">
          <option value="">Todos los géneros</option>
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
    </div>

    <!-- Loading State -->
    <div v-if="loading" :style="{ textAlign: 'center', padding: '50px', color: '#FF7A1A' }">
      Cargando música...
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredSongs.length === 0" :style="{ textAlign: 'center', padding: '50px', backgroundColor: '#FFF', borderRadius: '30px', border: '1px solid #F0F0F0' }">
      <p :style="{ fontSize: '1.2rem', fontWeight: '600', color: '#A0A0A0' }">No se encontraron canciones.</p>
    </div>

    <!-- Songs Table -->
    <div v-else :style="tableContainerStyle">
      <table :style="{ width: '100%', borderCollapse: 'collapse' }">
        <thead>
          <tr :style="tableHeaderRowStyle">
            <th :style="tableHeaderStyle">#</th>
            <th :style="tableHeaderStyle">Título</th>
            <th :style="tableHeaderStyle">Artista</th>
            <th :style="tableHeaderStyle">Género</th>
            <th :style="{ ...tableHeaderStyle, textAlign: 'right' }">Acción</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(song, index) in filteredSongs" :key="song.id" :style="tableRowStyle" class="song-row">
            <td :style="tableCellStyle">
              <div :style="{ color: '#A0A0A0', fontWeight: '800' }">{{ index + 1 }}</div>
            </td>
            <td :style="tableCellStyle">
              <div :style="{ display: 'flex', alignItems: 'center', gap: '15px' }">
                <div :style="songImageStyle(song.image)"></div>
                <div :style="{ fontWeight: '800', color: '#1A1614', fontSize: '1.05rem' }">{{ song.title }}</div>
              </div>
            </td>
            <td :style="tableCellStyle">
              <span @click="router.push(`/artista/${song.artist_id}`)" :style="{ color: '#666', fontWeight: '600', cursor: 'pointer' }" class="artist-link">
                {{ song.artist }}
              </span>
            </td>
            <td :style="tableCellStyle">
              <span :style="genreBadgeStyle">{{ song.genero || 'Otro' }}</span>
            </td>
            <td :style="{ ...tableCellStyle, textAlign: 'right' }">
              <div :style="{ display: 'flex', gap: '10px', justifyContent: 'flex-end' }">
                <button @click="openAddToPlaylistModal(song)" :style="addToPlaylistBtnStyle" title="Añadir a Playlist">
                  <Plus :size="18" color="#FF7A1A" />
                </button>
                <button @click="playSong(song, filteredSongs)" :style="playBtnStyle">
                  <Play :size="18" fill="#FFF" color="#FFF" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ADD TO PLAYLIST MODAL -->
    <div v-if="showPlaylistModal" :style="modalOverlayStyle" @click.self="showPlaylistModal = false">
      <div :style="modalContentStyle">
        <h3 :style="{ fontSize: '1.5rem', fontWeight: '900', color: '#1A1614', marginBottom: '20px' }">Añadir a Playlist</h3>
        <div v-if="userPlaylists.length === 0" :style="{ color: '#666', marginBottom: '20px' }">
          No tienes playlists creadas. Ve a tu Biblioteca para crear una.
        </div>
        <div v-else :style="{ display: 'flex', flexDirection: 'column', gap: '10px', maxHeight: '300px', overflowY: 'auto', marginBottom: '20px' }">
          <div v-for="pl in userPlaylists" :key="pl._id" @click="addSongToPlaylist(pl._id)" class="playlist-row" :style="playlistRowStyle">
            <img :src="pl.caratula || 'https://images.unsplash.com/photo-1493225255756-d9584f8606e9?w=400&h=400&fit=crop'" :style="{ width: '40px', height: '40px', borderRadius: '8px', objectFit: 'cover' }" />
            <div :style="{ fontWeight: '700', color: '#1A1614' }">{{ pl.titulo }}</div>
          </div>
        </div>
        <button @click="showPlaylistModal = false" :style="modalCancelBtnStyle">Cancelar</button>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { Search, Filter, Play, ArrowLeft, Plus } from 'lucide-vue-next';
import axios from 'axios';
import { playSong } from '@/playerState';
import Swal from 'sweetalert2';

const router = useRouter();
const allSongs = ref([]);
const filteredSongs = ref([]);
const searchQuery = ref('');
const selectedGenre = ref('');
const loading = ref(true);

const showPlaylistModal = ref(false);
const userPlaylists = ref([]);
const songToAdd = ref(null);

const fetchSongs = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/canciones');
    const songs = (response.data.data || response.data).map(song => ({
      id: song.id,
      title: song.titulo,
      artist: song.artista,
      artist_id: song.user_id,
      genero: song.genero,
      image: song.caratula || 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=400&h=400&fit=crop',
      url_audio: song.url_audio
    }));
    allSongs.value = songs;
    applyFilters();
  } catch (error) {
    console.error('Error fetching songs:', error);
  } finally {
    loading.value = false;
  }
};

const applyFilters = () => {
  let result = allSongs.value;
  
  if (searchQuery.value.trim() !== '') {
    const term = searchQuery.value.toLowerCase();
    result = result.filter(s => 
      s.title.toLowerCase().includes(term) || 
      s.artist.toLowerCase().includes(term)
    );
  }
  
  if (selectedGenre.value !== '') {
    result = result.filter(s => s.genero && s.genero.toLowerCase() === selectedGenre.value.toLowerCase());
  }
  
  filteredSongs.value = result;
};

watch([searchQuery, selectedGenre], () => {
  applyFilters();
});

onMounted(() => {
  fetchSongs();
  fetchPlaylists();
});

const fetchPlaylists = async () => {
  try {
    const res = await axios.get('/api/playlists', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });
    userPlaylists.value = res.data;
  } catch (error) {
    console.error('Error fetching playlists', error);
  }
};

const openAddToPlaylistModal = (song) => {
  songToAdd.value = song;
  showPlaylistModal.value = true;
};

const addSongToPlaylist = async (playlistId) => {
  if (!songToAdd.value) return;
  try {
    await axios.post(`/api/playlists/${playlistId}/songs`, {
      cancion_id: songToAdd.value.id
    }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });
    Swal.fire({
      icon: 'success',
      title: '¡Canción añadida!',
      text: `"${songToAdd.value.title}" fue agregada a la playlist correctamente.`,
      timer: 2000,
      showConfirmButton: false,
      customClass: {
        popup: 'mango-swal2-popup',
        title: 'mango-swal2-title',
        htmlContainer: 'mango-swal2-text'
      }
    });
    showPlaylistModal.value = false;
  } catch (error) {
    console.error('Error adding song to playlist', error);
  }
};

// Styles
const backBtnStyle = { padding: '10px 20px', backgroundColor: '#FFF', border: '1px solid #E5E5E5', borderRadius: '12px', fontWeight: '800', color: '#1A1614', display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer', boxShadow: '0 4px 15px rgba(0,0,0,0.02)' };
const filtersContainerStyle = { display: 'flex', gap: '20px', marginBottom: '40px', flexWrap: 'wrap' };
const searchBoxStyle = { flex: 1, minWidth: '300px', display: 'flex', alignItems: 'center', gap: '15px', backgroundColor: '#FFF', padding: '14px 25px', borderRadius: '22px', border: '1px solid #E5E5E5', boxShadow: '0 10px 30px rgba(0,0,0,0.02)' };
const searchInputStyle = { border: 'none', outline: 'none', backgroundColor: 'transparent', fontSize: '1rem', fontWeight: '600', width: '100%', color: '#1A1614' };
const genreSelectBoxStyle = { display: 'flex', alignItems: 'center', gap: '10px', backgroundColor: '#FFF', padding: '14px 25px', borderRadius: '22px', border: '1px solid #E5E5E5', boxShadow: '0 10px 30px rgba(0,0,0,0.02)' };
const selectStyle = { border: 'none', outline: 'none', backgroundColor: 'transparent', fontSize: '1rem', fontWeight: '600', color: '#1A1614', cursor: 'pointer' };

const tableContainerStyle = { backgroundColor: '#FFF', borderRadius: '30px', padding: '30px', border: '1px solid #E5E5E5', boxShadow: '0 15px 45px rgba(0,0,0,0.02)' };
const tableHeaderRowStyle = { borderBottom: '2px solid #F0F0F0' };
const tableHeaderStyle = { textAlign: 'left', padding: '15px 10px', color: '#A0A0A0', fontWeight: '800', fontSize: '0.9rem', textTransform: 'uppercase' };
const tableRowStyle = { borderBottom: '1px solid #F8F8F8', transition: '0.2s' };
const tableCellStyle = { padding: '15px 10px', verticalAlign: 'middle' };
const songImageStyle = (img) => ({ width: '50px', height: '50px', backgroundImage: `url(${img})`, backgroundSize: 'cover', backgroundPosition: 'center', borderRadius: '12px' });
const genreBadgeStyle = { backgroundColor: '#FFF9F5', color: '#FF7A1A', padding: '6px 12px', borderRadius: '8px', fontSize: '0.85rem', fontWeight: '800' };
const playBtnStyle = { width: '40px', height: '40px', borderRadius: '50%', backgroundColor: '#FF7A1A', border: 'none', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', boxShadow: '0 4px 15px rgba(255,122,26,0.3)' };
const addToPlaylistBtnStyle = { width: '40px', height: '40px', borderRadius: '50%', backgroundColor: '#FFF9F5', border: '1px solid #FFDAB9', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer' };

const modalOverlayStyle = { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0,0,0,0.5)', backdropFilter: 'blur(5px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 5000 };
const modalContentStyle = { backgroundColor: '#FFF', padding: '30px', borderRadius: '30px', width: '400px', boxShadow: '0 20px 40px rgba(0,0,0,0.1)' };
const playlistRowStyle = { display: 'flex', alignItems: 'center', gap: '15px', padding: '10px', borderRadius: '12px', cursor: 'pointer', transition: '0.2s' };
const modalCancelBtnStyle = { width: '100%', padding: '12px', backgroundColor: '#F5F5F5', color: '#666', border: 'none', borderRadius: '15px', fontWeight: '800', cursor: 'pointer' };
</script>

<style scoped>
.song-row:hover { background-color: #FAFAFA; }
.artist-link:hover { color: '#FF7A1A'; text-decoration: underline; }
.playlist-row:hover { background-color: #F9F9F9; transform: scale(1.02); }
</style>
