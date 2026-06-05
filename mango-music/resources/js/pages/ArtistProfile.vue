<template>
  <main v-if="artista" :style="{ flex: 1, padding: '40px' }">
      <!-- Artist Profile Header -->
      <div :style="profileBannerStyle">
        <div :style="{ display: 'flex', alignItems: 'flex-end', gap: '30px', height: '100%' }">
          <div :style="largeAvatarContainerStyle">
            <img :src="artista.foto || 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=300&fit=crop'" :style="{ width: '100%', height: '100%', objectFit: 'cover' }" />
          </div>

          <div :style="{ flex: 1, paddingBottom: '10px' }">
            <div :style="{ display: 'flex', alignItems: 'center', gap: '15px', marginBottom: '5px' }">
              <span :style="verifiedBadgeStyle">Artista Verificado</span>
            </div>
            <h1 :style="{ fontSize: '4.5rem', fontWeight: '900', color: '#fff', margin: '0', lineHeight: '1', letterSpacing: '-2px' }">{{ artista.nombre_artistico || artista.nombre }}</h1>
            <div :style="{ display: 'flex', gap: '30px', marginTop: '20px' }">
              <div :style="{ textAlign: 'left' }">
                <div :style="{ fontSize: '1.5rem', fontWeight: '900', color: '#fff' }">{{ followerCount }}</div>
                <div :style="{ fontSize: '0.75rem', color: 'rgba(255,255,255,0.7)', fontWeight: '700', textTransform: 'uppercase' }">Seguidores</div>
              </div>
            </div>
          </div>

          <div :style="{ display: 'flex', gap: '15px' }">
            <button @click="toggleFollow" :style="followButtonStyle">
              <UserPlus v-if="!isFollowing" :size="20" />
              <UserCheck v-else :size="20" />
              {{ isFollowing ? 'Siguiendo' : 'Seguir' }}
            </button>
            <router-link :to="{ path: '/apoyar-artistas', query: { artista_id: artista.id || artista._id } }" :style="{ ...donateButtonStyle, textDecoration: 'none' }">
              <DollarSign :size="20" /> Apoyar
            </router-link>
          </div>
        </div>
      </div>

      <!-- Biography Section -->
      <div :style="bioSectionStyle">
        <h3 :style="{ fontSize: '1.2rem', fontWeight: '800', marginBottom: '10px', color: '#1A1614' }">Sobre el artista</h3>
        <p :style="{ fontSize: '1rem', lineHeight: '1.7', color: '#444' }">
          {{ artista.biografia || 'Este artista aún no ha añadido una biografía.' }}
        </p>
      </div>

      <!-- Songs List Section -->
      <div :style="{ marginTop: '50px' }">
        <h2 :style="{ fontSize: '1.8rem', fontWeight: '800', color: '#1A1614', marginBottom: '30px' }">Canciones Populares</h2>
        
        <div v-if="canciones.length > 0" :style="{ display: 'flex', flexDirection: 'column', gap: '12px' }">
          <div v-for="(song, index) in canciones" :key="song.id" @click="reproducir(song)" :style="songRowStyle" class="song-row-hover">
            <div :style="{ width: '30px', color: '#A0A0A0', fontWeight: '800' }">{{ index + 1 }}</div>
            <div :style="songThumbnailStyle">
              <img :src="song.caratula || 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=100&h=100&fit=crop'" :style="{ width: '100%', height: '100%', objectFit: 'cover', borderRadius: '10px' }" />
              <div class="play-overlay" :style="playOverlayStyle">
                <Play :size="16" fill="#FF7A1A" color="#FF7A1A" />
              </div>
            </div>
            <div :style="{ flex: 1 }">
              <div :style="{ fontWeight: '800', fontSize: '1.1rem', color: '#1A1614' }">{{ song.titulo }}</div>
              <div :style="{ color: '#A0A0A0', fontSize: '0.85rem', fontWeight: '600' }">{{ song.genero }}</div>
            </div>
            <div :style="{ color: '#A0A0A0', fontSize: '0.9rem', fontWeight: '700' }">{{ song.duracion || '3:15' }}</div>
          </div>
        </div>
        
        <div v-else :style="{ padding: '30px', backgroundColor: '#F9F9F9', borderRadius: '20px', textAlign: 'center', color: '#666' }">
          <Music :size="40" :style="{ margin: '0 auto 10px', color: '#ccc' }" />
          <p :style="{ fontSize: '1.1rem', fontWeight: '600', margin: 0 }">Este artista aún no tiene canciones populares publicadas.</p>
        </div>
      </div>
    </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { UserPlus, UserCheck, DollarSign, Play, Music } from 'lucide-vue-next';
import axios from 'axios';
import Swal from 'sweetalert2';
import { playSong } from '@/playerState';

const route = useRoute();
const artista = ref(null);
const canciones = ref([]);
const isFollowing = ref(false);

const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
const followerCount = ref(0);

const fetchArtist = async () => {
  try {
    const response = await axios.get(`/api/artistas/${route.params.id}`);
    artista.value = response.data.artista;
    canciones.value = response.data.canciones;
    
    if (artista.value) {
      // Fetch real followers count
      const countRes = await axios.get(`/api/seguidores/count/${artista.value.id || artista.value._id}`);
      followerCount.value = countRes.data.count;

      // Check if current user is following
      if (currentUser.id) {
        const statusRes = await axios.get(`/api/seguidores/status/${currentUser.id}/${artista.value.id || artista.value._id}`);
        isFollowing.value = statusRes.data.following;
      }
    }
  } catch (error) {
    console.error('Error fetching artist:', error);
  }
};

const toggleFollow = async () => {
  if (!currentUser.id) {
    Swal.fire('Inicia sesión', 'Debes estar conectado para seguir a este artista.', 'info');
    return;
  }
  if (currentUser.id === artista.value.id) { // BUG #20 - === en vez de ==
    Swal.fire('Aviso', 'No puedes seguirte a ti mismo.', 'info');
    return;
  }

  try {
    if (isFollowing.value) {
      await axios.post('/api/seguidores/unfollow', {
        seguidor_id: currentUser.id,
        artista_id: artista.value.id
      });
      isFollowing.value = false;
      followerCount.value--;
    } else {
      await axios.post('/api/seguidores/follow', {
        seguidor_id: currentUser.id,
        artista_id: artista.value.id
      });
      isFollowing.value = true;
      followerCount.value++;
    }
  } catch (e) {
    console.error('Follow error:', e);
  }
};

const donate = () => {
  // Ahora el botón es un router-link, así que esta función queda como fallback si es necesaria.
};

const reproducir = (song) => {
  const queue = canciones.value.map(c => ({
    id: c.id,
    title: c.titulo,
    artist: artista.value.nombre,
    artist_id: artista.value.id,
    image: c.caratula || artista.value.foto,
    url_audio: c.url_audio
  }));
  const targetSong = queue.find(s => s.id === song.id) || queue[0];
  playSong(targetSong, queue);
};

onMounted(fetchArtist);

// STYLES
const profileBannerStyle = {
  background: 'linear-gradient(135deg, #FF7A1A, #A8E063)',
  height: '380px', borderRadius: '40px', padding: '50px', display: 'flex', flexDirection: 'column', justifyContent: 'flex-end'
};
const largeAvatarContainerStyle = { width: '200px', height: '200px', borderRadius: '50%', backgroundColor: '#fff', border: '6px solid rgba(255,255,255,0.1)', overflow: 'hidden', boxShadow: '0 20px 40px rgba(0,0,0,0.3)' };
const verifiedBadgeStyle = { backgroundColor: '#FF7A1A', color: '#fff', padding: '6px 12px', borderRadius: '8px', fontSize: '0.75rem', fontWeight: '800', textTransform: 'uppercase' };
const followButtonStyle = { backgroundColor: 'rgba(255,255,255,0.15)', color: '#fff', border: '1px solid rgba(255,255,255,0.3)', padding: '12px 25px', borderRadius: '15px', fontWeight: '700', cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '10px', backdropFilter: 'blur(10px)' };
const donateButtonStyle = { ...followButtonStyle, backgroundColor: '#FF7A1A', border: 'none' };
const bioSectionStyle = { marginTop: '40px', backgroundColor: '#fff', padding: '30px 40px', borderRadius: '30px', boxShadow: '0 10px 30px rgba(0,0,0,0.02)' };
const songRowStyle = { backgroundColor: '#fff', padding: '15px 25px', borderRadius: '20px', display: 'flex', alignItems: 'center', gap: '20px', cursor: 'pointer', transition: '0.2s' };
const songThumbnailStyle = { width: '50px', height: '50px', backgroundColor: '#F0F0F0', borderRadius: '10px', position: 'relative' };
const playOverlayStyle = { position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', backgroundColor: 'rgba(0,0,0,0.3)', borderRadius: '10px', opacity: 0, transition: '0.2s' };
</script>

<style scoped>
.song-row-hover:hover { background-color: #FDFCFB !important; transform: translateX(5px); }
.song-row-hover:hover .play-overlay { opacity: 1 !important; }
</style>
