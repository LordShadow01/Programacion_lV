<template>
  <div v-if="shouldShow" :style="playerWrapperStyle">
    <div :style="playerContainerStyle">
      <!-- Top Progress Bar (Subtle & Slim) -->
      <div 
        class="progress-track-container"
        :style="progressTrackStyle" 
        @mousedown="startDrag"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
      >
        <div :style="visibleTrackStyle">
          <div :style="progressBarStyle"></div>
        </div>
        <div :style="progressKnobStyle"></div>
      </div>

      <!-- Main Player Content -->
      <div :style="contentGridStyle">
        
        <!-- LEFT: Artist & Song Info -->
        <div :style="{ display: 'flex', alignItems: 'center', gap: '16px', minWidth: '280px' }">
          <div :style="thumbWrapperStyle">
            <img :src="currentSong.image || currentSong.caratula" :style="thumbStyle" />
            <div v-if="isPlaying" :style="playingIndicatorOverlay">
              <div class="bar-anim"></div>
              <div class="bar-anim"></div>
              <div class="bar-anim"></div>
            </div>
          </div>
          <div :style="{ display: 'flex', flexDirection: 'column', overflow: 'hidden' }">
            <h4 :style="songTitleStyle">{{ currentSong.title || currentSong.titulo || 'Sin reproducir' }}</h4>
            <span @click="goToArtist" :style="artistLinkStyle">
              {{ currentSong.artist || currentSong.artista || 'Mango Music' }}
            </span>
          </div>
          <button @click="toggleLike" :style="iconButtonStyle">
            <Heart :size="20" :fill="isLiked ? '#FF7A1A' : 'none'" :color="isLiked ? '#FF7A1A' : '#FFF'" :style="{ transition: '0.3s' }" />
          </button>
        </div>

        <!-- CENTER: Playback Controls -->
        <div :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px' }">
          <div :style="{ display: 'flex', alignItems: 'center', gap: '30px' }">
            <button @click="shuffle = !shuffle" :style="{ ...controlButtonStyle, color: shuffle ? '#FF7A1A' : '#FFF' }">
              <Shuffle :size="18" />
            </button>
            <button @click="playPrev" :style="controlButtonStyle"><SkipBack :size="22" fill="#FFF" /></button>
            
            <div @click="togglePlay" :style="mainPlayButtonStyle">
              <Pause v-if="isPlaying" :size="24" fill="#1A1614" />
              <Play v-else :size="24" fill="#1A1614" :style="{ marginLeft: '3px' }" />
            </div>

            <button @click="playNext(shuffle)" :style="controlButtonStyle"><SkipForward :size="22" fill="#FFF" /></button>
            <button @click="repeat = !repeat" :style="{ ...controlButtonStyle, color: repeat ? '#FF7A1A' : '#FFF' }">
              <Repeat :size="18" />
            </button>
          </div>
        </div>

        <!-- RIGHT: Volume & Utilities -->
        <div :style="{ display: 'flex', alignItems: 'center', gap: '20px', justifyContent: 'flex-end', minWidth: '280px' }">
          <span v-if="showTime" :style="timeTextStyle">{{ formatTime(currentTime) }} / {{ formatTime(duration) }}</span>
          <div :style="{ display: 'flex', alignItems: 'center', gap: '10px' }">
            <Volume2 :size="18" color="#FFF" />
            <div :style="volumeTrackStyle">
              <input type="range" v-model="volume" min="0" max="1" step="0.01" class="vol-range" />
            </div>
          </div>
          <div :style="{ position: 'relative' }">
            <button @click="togglePlaylistDropdown" :style="{ ...controlButtonStyle, color: showPlaylistDropdown ? '#FF7A1A' : '#FFF' }">
              <ListMusic :size="20" />
            </button>
            <div v-if="showPlaylistDropdown" :style="dropdownStyle">
              <div :style="dropdownHeaderStyle">Añadir a Playlist</div>
              <div v-if="loadingPlaylists" :style="dropdownItemStyle">Cargando...</div>
              <div v-else-if="playlists.length === 0" :style="dropdownEmptyStyle">
                <span :style="{ fontSize: '0.85rem', color: '#AAA', display: 'block', marginBottom: '8px' }">No tienes playlists. ¡Crea una en tu biblioteca!</span>
                <button @click="goToLibrary" :style="goToLibraryButtonStyle">Ir a Biblioteca</button>
              </div>
              <div v-else :style="{ maxHeight: '200px', overflowY: 'auto' }">
                <div 
                  v-for="playlist in playlists" 
                  :key="playlist.id || playlist._id" 
                  @click="addCurrentSongToPlaylist(playlist.id || playlist._id)"
                  :style="dropdownItemStyle"
                  class="playlist-item"
                >
                  {{ playlist.titulo }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- AUDIO CORE -->
    <audio 
      ref="audioPlayer" 
      :src="audioUrl" 
      @timeupdate="updateTime" 
      @loadedmetadata="onLoadedMetadata"
      @ended="handleEnded"
    ></audio>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Play, Pause, SkipBack, SkipForward, Volume2, Heart, Repeat, Shuffle, ListMusic } from 'lucide-vue-next';
import { isPlaying, currentSong, playNext, playPrev } from '@/playerState';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const audioPlayer = ref(null);
const currentTime = ref(0);
const duration = ref(0);
const volume = ref(0.7);
const isLiked = ref(false);
const shuffle = ref(false);
const repeat = ref(false);
const isHovered = ref(false);
const isDragging = ref(false);
const showKnobAndGrownTrack = computed(() => isHovered.value || isDragging.value);

const audioUrl = computed(() => {
  const url = currentSong.value.url_audio || '';
  if (url.includes('/storage/audio/')) {
    return url.replace('/storage/audio/', '/api/stream/audio/');
  }
  return url;
});

let trackRect = null;

const startDrag = (e) => {
  isDragging.value = true;
  isHovered.value = true;
  
  // SOLUCIÓN: Buscamos siempre el contenedor principal de la barra, no el hijo donde se hizo clic
  const mainTrack = document.querySelector('.progress-track-container');
  if (mainTrack) {
    trackRect = mainTrack.getBoundingClientRect();
  }
  
  handleDragMove(e.clientX);
  
  window.addEventListener('mousemove', onDragMove);
  window.addEventListener('mouseup', onDragEnd);
};

const handleDragMove = (clientX) => {
  if (!trackRect || !duration.value) return;
  
  // Calculamos la posición exacta basándonos en el contenedor global de extremo a extremo
  const x = Math.max(0, Math.min(clientX - trackRect.left, trackRect.width));
  const percent = x / trackRect.width;
  
  currentTime.value = percent * duration.value;
};

const onDragMove = (e) => {
  if (!isDragging.value) return;
  handleDragMove(e.clientX);
};

const onDragEnd = () => {
  if (isDragging.value) {
    isDragging.value = false;
    isHovered.value = false;
    
    if (audioPlayer.value) {
      console.log("Intentando cambiar tiempo a:", currentTime.value);
      
      // Forzamos un pequeño delay para asegurar el ciclo de Vue
      setTimeout(() => {
        audioPlayer.value.currentTime = currentTime.value;
        console.log("Tiempo asignado en el player nativo:", audioPlayer.value.currentTime);
        
        if (isPlaying.value) {
          audioPlayer.value.play().catch((err) => console.log("Error al reproducir:", err));
        }
      }, 50);
    }
    
    window.removeEventListener('mousemove', onDragMove);
    window.removeEventListener('mouseup', onDragEnd);
  }
};

const shouldShow = computed(() => isPlaying.value || currentSong.value.url_audio);
const showTime = ref(true);

onMounted(() => {
  const saved = localStorage.getItem('appSettings');
  if (saved) {
    const s = JSON.parse(saved);
    showTime.value = s.showTime;
  }
  window.addEventListener('settings-updated', (e) => {
    showTime.value = e.detail.showTime;
  });
});

const togglePlay = () => { if (currentSong.value.url_audio) isPlaying.value = !isPlaying.value; };
const toggleLike = () => {
  isLiked.value = !isLiked.value;
  let library = JSON.parse(localStorage.getItem('library') || '[]');
  if (isLiked.value) {
    if (!library.some(s => s.id === currentSong.value.id)) {
      library.push(currentSong.value);
    }
  } else {
    library = library.filter(s => s.id !== currentSong.value.id);
  }
  localStorage.setItem('library', JSON.stringify(library));
  window.dispatchEvent(new CustomEvent('library-updated'));
};

const goToArtist = () => {
  if (currentSong.value.artist_id) router.push(`/artista/${currentSong.value.artist_id}`);
  else router.push('/perfil'); // Fallback to current profile for now
};

const handleEnded = () => {
  if (repeat.value) { 
    audioPlayer.value.currentTime = 0; 
    audioPlayer.value.play(); 
  } else {
    playNext(shuffle.value);
  }
};

watch(isPlaying, (val) => {
  if (!audioPlayer.value) return;
  if (val) audioPlayer.value.play().catch(() => {});
  else audioPlayer.value.pause();
});

watch(() => currentSong.value.id, () => {
  const library = JSON.parse(localStorage.getItem('library') || '[]');
  isLiked.value = library.some(s => s.id === currentSong.value.id);
  isPlaying.value = true;
  setTimeout(() => { if (audioPlayer.value) { audioPlayer.value.load(); audioPlayer.value.play().catch(() => {}); } }, 100);
});

const progress = computed(() => (duration.value ? (currentTime.value / duration.value) * 100 : 0));

watch(volume, (val) => { if (audioPlayer.value) audioPlayer.value.volume = val; });
const updateTime = () => { 
  if (audioPlayer.value && !isDragging.value) { 
    currentTime.value = audioPlayer.value.currentTime; 
  } 
};
const onLoadedMetadata = () => { if (audioPlayer.value) duration.value = audioPlayer.value.duration; };

const formatTime = (t) => { if (isNaN(t)) return '0:00'; const m = Math.floor(t/60); const s = Math.floor(t%60); return `${m}:${s < 10 ? '0' : ''}${s}`; };

// MODERN STYLES
const playerWrapperStyle = { position: 'fixed', bottom: '20px', left: '280px', right: '20px', zIndex: 5000, display: 'flex', justifyContent: 'center' };
const playerContainerStyle = {
  width: '100%', maxWidth: '1200px', backgroundColor: 'rgba(26, 22, 20, 0.9)', backdropFilter: 'blur(25px)',
  borderRadius: '24px', padding: '16px 24px', boxShadow: '0 20px 50px rgba(0,0,0,0.5)', border: '1px solid rgba(255,255,255,0.1)',
  position: 'relative', overflow: 'hidden'
};

const progressTrackStyle = computed(() => ({
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0,
  height: '16px',
  cursor: 'pointer',
  zIndex: 10,
  display: 'flex',
  alignItems: 'flex-start'
}));

const visibleTrackStyle = computed(() => ({
  width: '100%',
  height: showKnobAndGrownTrack.value ? '6px' : '4px',
  backgroundColor: 'rgba(255,255,255,0.08)',
  transition: 'height 0.2s ease',
  position: 'relative'
}));

const progressBarStyle = computed(() => ({
  width: progress.value + '%',
  height: '100%',
  backgroundColor: '#FF7A1A',
  boxShadow: '0 0 15px rgba(255,122,26,0.6)'
}));

const progressKnobStyle = computed(() => ({
  position: 'absolute',
  left: `calc(${progress.value}% - 6px)`,
  top: showKnobAndGrownTrack.value ? '-3px' : '-4px',
  width: showKnobAndGrownTrack.value ? '12px' : '0px',
  height: showKnobAndGrownTrack.value ? '12px' : '0px',
  backgroundColor: '#FFF',
  borderRadius: '50%',
  boxShadow: '0 0 10px rgba(0,0,0,0.5)',
  transition: isDragging.value ? 'width 0.15s ease, height 0.15s ease, opacity 0.15s ease' : 'width 0.15s ease, height 0.15s ease, opacity 0.15s ease, left 0.1s ease',
  opacity: showKnobAndGrownTrack.value ? 1 : 0,
  pointerEvents: 'none',
  zIndex: 11
}));

const contentGridStyle = { display: 'flex', alignItems: 'center', justifyContent: 'space-between' };
const thumbWrapperStyle = { width: '56px', height: '56px', borderRadius: '12px', overflow: 'hidden', position: 'relative', boxShadow: '0 8px 16px rgba(0,0,0,0.3)' };
const thumbStyle = { width: '100%', height: '100%', objectFit: 'cover' };
const songTitleStyle = { color: '#FFF', fontSize: '1rem', fontWeight: '800', margin: 0, whiteSpace: 'nowrap' };
const artistLinkStyle = { color: 'rgba(255,255,255,0.6)', fontSize: '0.85rem', fontWeight: '600', cursor: 'pointer', transition: '0.2s' };
const mainPlayButtonStyle = { width: '54px', height: '54px', backgroundColor: '#FFF', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', transition: '0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)' };
const controlButtonStyle = { background: 'none', border: 'none', color: '#FFF', cursor: 'pointer', transition: '0.2s', display: 'flex', alignItems: 'center', justifyContent: 'center' };
const iconButtonStyle = { ...controlButtonStyle, padding: '8px' };
const timeTextStyle = { color: 'rgba(255,255,255,0.5)', fontSize: '0.85rem', fontWeight: '700', fontVariantNumeric: 'tabular-nums' };
const volumeTrackStyle = { width: '100px', display: 'flex', alignItems: 'center' };
const playingIndicatorOverlay = { position: 'absolute', inset: 0, backgroundColor: 'rgba(255,122,26,0.3)', display: 'flex', alignItems: 'center', justifycontent: 'center', gap: '3px' };

const showPlaylistDropdown = ref(false);
const playlists = ref([]);
const loadingPlaylists = ref(false);

const togglePlaylistDropdown = async () => {
  showPlaylistDropdown.value = !showPlaylistDropdown.value;
  if (showPlaylistDropdown.value) {
    loadingPlaylists.value = true;
    try {
      const token = localStorage.getItem('token');
      console.log('Fetching playlists with token:', token);
      const response = await axios.get('/api/playlists', {
        headers: { Authorization: `Bearer ${token}` }
      });
      console.log('Playlists response data:', response.data);
      playlists.value = response.data;
    } catch (error) {
      console.error('Error fetching playlists:', error);
    } finally {
      loadingPlaylists.value = false;
    }
  }
};

const goToLibrary = () => {
  showPlaylistDropdown.value = false;
  router.push('/biblioteca');
};

const addCurrentSongToPlaylist = async (playlistId) => {
  const songId = currentSong.value.id || currentSong.value._id;
  if (!songId) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No hay ninguna canción en reproducción.',
      toast: true,
      position: 'top-end',
      timer: 3000,
      showConfirmButton: false
    });
    return;
  }

  try {
    const token = localStorage.getItem('token');
    await axios.post('/api/playlist/agregar-cancion', {
      playlist_id: playlistId,
      song_id: songId
    }, {
      headers: { Authorization: `Bearer ${token}` }
    });

    Swal.fire({
      icon: 'success',
      title: '¡Canción añadida a tu playlist!',
      toast: true,
      position: 'top-end',
      timer: 3000,
      showConfirmButton: false
    });
    showPlaylistDropdown.value = false;
  } catch (error) {
    console.error('Error adding song:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error al añadir',
      text: error.response?.data?.message || 'No se pudo añadir la canción.',
      toast: true,
      position: 'top-end',
      timer: 3000,
      showConfirmButton: false
    });
  }
};


const dropdownStyle = {
  position: 'absolute',
  bottom: '50px',
  right: '0',
  width: '240px',
  backgroundColor: '#1E1B18',
  border: '1px solid rgba(255, 255, 255, 0.1)',
  borderRadius: '12px',
  boxShadow: '0 10px 25px rgba(0,0,0,0.5)',
  zIndex: 9999,
  padding: '10px 0',
  textAlign: 'left'
};

const dropdownHeaderStyle = {
  padding: '8px 16px',
  fontSize: '0.9rem',
  fontWeight: '700',
  color: '#FF7A1A',
  borderBottom: '1px solid rgba(255,255,255,0.05)',
  marginBottom: '5px'
};

const dropdownItemStyle = {
  padding: '10px 16px',
  fontSize: '0.9rem',
  color: '#FFF',
  cursor: 'pointer',
  transition: 'background-color 0.2s, color 0.2s',
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis'
};

const dropdownEmptyStyle = {
  padding: '12px 16px',
  textAlign: 'center'
};

const goToLibraryButtonStyle = {
  width: '100%',
  padding: '8px',
  backgroundColor: '#FF7A1A',
  color: '#FFF',
  border: 'none',
  borderRadius: '8px',
  fontSize: '0.85rem',
  fontWeight: '700',
  cursor: 'pointer',
  transition: '0.2s',
  marginTop: '5px'
};
</script>

<style scoped>
.vol-range { width: 100%; cursor: pointer; accent-color: #FF7A1A; height: 4px; }
.artist-link:hover { color: #FF7A1A; text-decoration: underline; }
.main-play-button:hover { transform: scale(1.1); box-shadow: 0 0 20px rgba(255,255,255,0.2); }
.bar-anim { width: 3px; height: 12px; background-color: #FFF; animation: musicBars 0.8s ease-in-out infinite alternate; }
.bar-anim:nth-child(2) { animation-delay: 0.2s; }
.bar-anim:nth-child(3) { animation-delay: 0.4s; }
@keyframes musicBars { from { height: 4px; } to { height: 16px; } }
.playlist-item:hover { background-color: rgba(255, 122, 26, 0.1); color: #FF7A1A; }
</style>
