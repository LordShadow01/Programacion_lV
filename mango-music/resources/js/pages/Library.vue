<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
      <div :style="libraryContainerStyle">
        
        <!-- Header -->
        <div v-if="!selectedPlaylist" :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '45px' }">
          <div>
            <h2 :style="{ fontSize: '2rem', fontWeight: '900', color: '#1A1614', letterSpacing: '-0.5px' }">Biblioteca</h2>
            <div :style="{ display: 'flex', gap: '20px', marginTop: '10px' }">
              <span 
                v-for="tab in tabs" :key="tab" 
                @click="activeTab = tab"
                :style="activeTab === tab ? activeTabLinkStyle : inactiveTabLinkStyle"
              >
                {{ tab }}
              </span>
            </div>
          </div>
          
          <div :style="ytSearchBoxStyle">
            <Search :size="20" :style="{ color: '#FF7A1A' }" />
            <input v-model="searchQuery" type="text" placeholder="Buscar en tu biblioteca" :style="ytSearchInputStyle" />
          </div>
        </div>

        <!-- PLAYLIST DETAIL VIEW -->
        <div v-if="selectedPlaylist" :style="{ animation: 'fadeIn 0.4s ease' }">
          <button @click="selectedPlaylist = null" :style="backButtonStyle">
            <ArrowLeft :size="20" /> Volver a Biblioteca
          </button>

          <div :style="playlistHeaderStyle">
            <div :style="playlistImageLargeStyle">
              <img :src="selectedPlaylist.caratula || selectedPlaylist.image || 'https://images.unsplash.com/photo-1493225255756-d9584f8606e9?w=400&h=400&fit=crop'" :style="{ width: '100%', height: '100%', objectFit: 'cover' }" />
            </div>
            <div :style="{ flex: 1 }">
              <div :style="{ color: '#FF7A1A', fontWeight: '800', fontSize: '1.1rem', marginBottom: '10px', textTransform: 'uppercase' }">Playlist Personalizada</div>
              <h2 :style="{ fontSize: '3rem', fontWeight: '900', color: '#1A1614', marginBottom: '15px' }">{{ selectedPlaylist.titulo || selectedPlaylist.title }}</h2>
              <div :style="{ display: 'flex', alignItems: 'center', gap: '15px' }">
                <div :style="{ fontWeight: '700', color: '#666' }">{{ userName }} &bull; {{ playlistSongs.length }} canciones</div>
                <button :style="playAllBtnStyle" @click="playSong(playlistSongs[0], playlistSongs)"><Play :size="20" fill="#fff" /> Reproducir Todo</button>
                <button :style="editBtnStyle" @click="openEditModal"><Pencil :size="16" /> Editar</button>
              </div>
            </div>
          </div>

          <!-- Songs List Table -->
          <div :style="{ marginTop: '50px' }">
            <div :style="tableHeaderStyle">
              <div :style="{ flex: 0.5 }">#</div>
              <div :style="{ flex: 3 }">Título</div>
              <div :style="{ flex: 2 }">Artista</div>
              <div :style="{ flex: 1, textAlign: 'right' }"><Clock :size="18" /></div>
            </div>
            <div v-for="(song, idx) in playlistSongs" :key="song.id" @click="playSong(song, playlistSongs)" class="song-row" :style="songRowStyle">
              <div :style="{ flex: 0.5, color: '#A0A0A0', fontWeight: '600' }">{{ idx + 1 }}</div>
              <div :style="{ flex: 3, display: 'flex', alignItems: 'center', gap: '15px' }">
                <img :src="song.image || song.caratula" :style="{ width: '45px', height: '45px', borderRadius: '8px', objectFit: 'cover' }" />
                <div>
                  <div :style="{ fontWeight: '700', color: '#1A1614' }">{{ song.title || song.titulo }}</div>
                  <div :style="{ fontSize: '1rem', color: '#A0A0A0' }">{{ song.artist || song.artista }}</div>
                </div>
              </div>
              <div :style="{ flex: 2, color: '#666', fontSize: '1rem' }">{{ song.artist || song.artista }}</div>
              <div :style="{ flex: 1, textAlign: 'right', display: 'flex', justifyContent: 'flex-end', alignItems: 'center', gap: '15px', color: '#A0A0A0', fontSize: '1rem' }">
                <span>{{ song.duration || '3:30' }}</span>
                <Trash2 @click.stop="eliminarCancion(song)" :size="18" :style="{ color: '#FF7A1A', cursor: 'pointer' }" />
              </div>
            </div>
          </div>
        </div>

        <!-- GRID VIEW -->
        <div v-else>
          <!-- Grid Container -->
          <div :style="gridContainerStyle">
            <!-- CREATE PLAYLIST BUTTON -->
            <div v-if="activeTab === 'Playlist'" @click="showCreateModal = true" class="yt-card" :style="createPlaylistCardStyle">
              <div :style="{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '15px' }">
                <Plus :size="50" :style="{ color: '#FF7A1A' }" />
                <div :style="{ fontWeight: '800', color: '#1A1614', fontSize: '1.1rem' }">Nueva Playlist</div>
              </div>
            </div>

            <!-- LIKED SONGS GRID with Add-to-Playlist button -->
            <div v-for="item in filteredItems" :key="item.id" class="yt-card" :style="cardStyle">
              <div :style="imageContainerStyle" @click="handleItemClick(item)">
                <img :src="item.image || item.caratula || 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=400&h=400&fit=crop'" :style="{ width: '100%', height: '100%', objectFit: 'cover' }" />
                <div v-if="activeTab !== 'Playlist'" class="play-overlay" :style="overlayStyle"><Play :size="40" fill="#fff" :style="{ color: '#fff' }" /></div>
              </div>
              <div :style="{ marginTop: '15px', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }">
                <div @click="handleItemClick(item)" :style="{ cursor: 'pointer', flex: 1, minWidth: 0 }">
                  <div :style="{ fontWeight: '700', fontSize: '1.1rem', color: '#1A1614', marginBottom: '4px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }">{{ item.title || item.titulo }}</div>
                  <div :style="{ fontSize: '1rem', color: '#A0A0A0', fontWeight: '500' }">{{ item.artist || item.artista || formatPlaylistSubtitle(item) }}</div>
                </div>
                <!-- Add to Playlist button — only for Me gustas tab -->
                <button 
                  v-if="activeTab === 'Me gustas'"
                  @click.stop="openAddToPlaylistMenu($event, item)"
                  :style="{ background: 'none', border: 'none', cursor: 'pointer', color: '#A0A0A0', padding: '4px', marginLeft: '8px', flexShrink: 0 }"
                  title="Agregar a playlist"
                ><ListPlus :size="20" /></button>
              </div>
            </div>
          </div>
          
          <div v-if="filteredItems.length === 0" :style="{ textAlign: 'center', padding: '100px', color: '#A0A0A0' }">
            <Music :size="60" :style="{ marginBottom: '20px', opacity: 0.2 }" />
            <p :style="{ fontSize: '1.2rem', fontWeight: '600' }">No hay elementos en esta sección.</p>
          </div>
        </div>
      </div>

      <!-- ADD-TO-PLAYLIST DROPDOWN (floating, closes on outside click) -->
      <div v-if="addToPlaylistMenu.show" 
           @click.self="addToPlaylistMenu.show = false"
           :style="{ position: 'fixed', inset: 0, zIndex: 4000 }">
        <div :style="{
          position: 'fixed',
          top: addToPlaylistMenu.y + 'px',
          left: addToPlaylistMenu.x + 'px',
          backgroundColor: '#FFF',
          borderRadius: '16px',
          boxShadow: '0 20px 50px rgba(0,0,0,0.15)',
          border: '1px solid #F0F0F0',
          padding: '10px 0',
          minWidth: '220px',
          zIndex: 4001
        }">
          <div :style="{ padding: '8px 20px', fontSize: '0.85rem', fontWeight: '700', color: '#A0A0A0', textTransform: 'uppercase', letterSpacing: '1px' }">Agregar a Playlist</div>
          <div v-if="playlists.length === 0" :style="{ padding: '10px 20px', color: '#CCC', fontSize: '0.95rem' }">Sin playlists creadas</div>
          <div 
            v-for="pl in playlists" :key="pl._id || pl.id"
            @click="addSongToPlaylist(addToPlaylistMenu.song, pl)"
            :style="{ padding: '12px 20px', cursor: 'pointer', fontWeight: '600', color: '#1A1614', fontSize: '1rem', transition: '0.2s' }"
            class="add-to-pl-item"
          >{{ pl.titulo }}</div>
        </div>
      </div>

      <div v-if="showCreateModal" :style="modalOverlayStyle" @click.self="showCreateModal = false">
        <div :style="modalContentStyle">
          <h3 :style="{ fontSize: '1.8rem', fontWeight: '900', color: '#1A1614', marginBottom: '30px' }">Nueva Playlist</h3>
          
          <div :style="{ display: 'flex', flexDirection: 'column', gap: '20px' }">
            <div @click="triggerFileInput" :style="imageUploadBoxStyle">
              <img v-if="newPlaylistImagePreview" :src="newPlaylistImagePreview" :style="{ width: '100%', height: '100%', objectFit: 'cover', borderRadius: '25px' }" />
              <div v-else :style="{ textAlign: 'center', color: '#FF7A1A' }">
                <Camera :size="40" :style="{ marginBottom: '10px' }" />
                <div :style="{ fontWeight: '700' }">Portada</div>
              </div>
              <input type="file" ref="fileInput" @change="handleFileChange" accept="image/*" style="display: none" />
            </div>

            <input v-model="newPlaylistName" type="text" placeholder="Nombre de la playlist" :style="modalInputStyle" />
            <textarea v-model="newPlaylistDesc" placeholder="Descripción (opcional)" :style="{ ...modalInputStyle, height: '80px', resize: 'none' }"></textarea>
            
            <div :style="{ display: 'flex', gap: '15px', marginTop: '10px' }">
              <button @click="showCreateModal = false" :style="modalCancelBtnStyle">Cancelar</button>
              <button @click="handleCreatePlaylist" :style="modalCreateBtnStyle">Crear</button>
            </div>
          </div>
        </div>
      </div>

      <!-- EDIT PLAYLIST MODAL -->
      <div v-if="showEditModal" :style="modalOverlayStyle" @click.self="showEditModal = false">
        <div :style="modalContentStyle">
          <h3 :style="{ fontSize: '1.8rem', fontWeight: '900', color: '#1A1614', marginBottom: '30px' }">Editar Playlist</h3>
          
          <div :style="{ display: 'flex', flexDirection: 'column', gap: '20px' }">
            <div @click="triggerEditFileInput" :style="imageUploadBoxStyle">
              <img v-if="editImagePreview" :src="editImagePreview" :style="{ width: '100%', height: '100%', objectFit: 'cover', borderRadius: '25px' }" />
              <div v-else :style="{ textAlign: 'center', color: '#FF7A1A' }">
                <Camera :size="40" :style="{ marginBottom: '10px' }" />
                <div :style="{ fontWeight: '700' }">Cambiar Portada</div>
              </div>
              <input type="file" ref="editFileInput" @change="handleEditFileChange" accept="image/*" style="display: none" />
            </div>

            <input v-model="editPlaylistName" type="text" placeholder="Nombre de la playlist" :style="modalInputStyle" />
            <textarea v-model="editPlaylistDesc" placeholder="Descripción (opcional)" :style="{ ...modalInputStyle, height: '80px', resize: 'none' }"></textarea>
            
            <div :style="{ display: 'flex', gap: '15px', marginTop: '10px' }">
              <button @click="showEditModal = false" :style="modalCancelBtnStyle">Cancelar</button>
              <button @click="handleEditPlaylist" :style="modalCreateBtnStyle">Guardar Cambios</button>
            </div>
          </div>
        </div>
      </div>
    </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Search, Music, Play, Plus, Camera, ArrowLeft, Clock, Trash2, Pencil, ListPlus } from 'lucide-vue-next';
import { playSong } from '@/playerState';
import axios from 'axios';
import Swal from 'sweetalert2';

// Helper para mostrar modales Mango Music sin congelar el hilo
const swalMango = (icon, title, text) => Swal.fire({
  icon,
  title,
  text,
  customClass: {
    popup: 'mango-swal2-popup',
    title: 'mango-swal2-title',
    htmlContainer: 'mango-swal2-text',
    actions: 'mango-swal2-actions'
  },
  didOpen: () => {
    const btn = Swal.getConfirmButton();
    if (btn) btn.classList.add('mango-swal2-confirm-btn');
  }
});

const activeTab = ref('Me gustas');
const searchQuery = ref('');
const selectedPlaylist = ref(null);
const tabs = ['Me gustas', 'Playlist', 'Álbumes'];

const showCreateModal = ref(false);
const newPlaylistName = ref('');
const newPlaylistDesc = ref('');
const newPlaylistImageFile = ref(null);
const newPlaylistImagePreview = ref(null);
const fileInput = ref(null);
const userName = ref('Usuario');

// Edit modal state
const showEditModal = ref(false);
const editPlaylistName = ref('');
const editPlaylistDesc = ref('');
const editImageFile = ref(null);
const editImagePreview = ref(null);
const editFileInput = ref(null);

// Add-to-Playlist dropdown state
const addToPlaylistMenu = ref({ show: false, x: 0, y: 0, song: null });

const likedSongs = ref([]);
const playlists = ref([]);
const playlistSongs = ref([]);
const albums = ref([]);

const triggerFileInput = () => { fileInput.value.click(); };
const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    newPlaylistImageFile.value = file;
    newPlaylistImagePreview.value = URL.createObjectURL(file);
  }
};

const triggerEditFileInput = () => { editFileInput.value.click(); };
const handleEditFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    editImageFile.value = file;
    editImagePreview.value = URL.createObjectURL(file);
  }
};

const openEditModal = () => {
  editPlaylistName.value = selectedPlaylist.value?.titulo || '';
  editPlaylistDesc.value = selectedPlaylist.value?.descripcion || '';
  editImagePreview.value = selectedPlaylist.value?.caratula || null;
  editImageFile.value = null;
  showEditModal.value = true;
};

const openAddToPlaylistMenu = (event, song) => {
  const rect = event.currentTarget.getBoundingClientRect();
  addToPlaylistMenu.value = {
    show: true,
    x: Math.min(rect.left, window.innerWidth - 240),
    y: rect.bottom + 8,
    song
  };
};

const loadData = async () => {
  const lib = JSON.parse(localStorage.getItem('library') || '[]');
  likedSongs.value = lib;
  
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  userName.value = user.nombre || 'Usuario';

  try {
    const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
    const plRes = await axios.get('/api/playlists', { headers });
    playlists.value = plRes.data;

    const alRes = await axios.get('/api/albums/dinamicos', { headers });
    albums.value = alRes.data;
  } catch (error) {
    console.error('Error loading library data', error);
  }
};

const formatPlaylistSubtitle = (item) => {
  const count = (item.canciones && Array.isArray(item.canciones) ? item.canciones.length : (item.canciones?.length || item.songsCount || 0));
  return `${count} canciones`;
};

onMounted(() => {
  loadData();
  window.addEventListener('library-updated', loadData);
});

const filteredItems = computed(() => {
  let source = [];
  if (activeTab.value === 'Me gustas') source = likedSongs.value;
  else if (activeTab.value === 'Playlist') source = playlists.value;
  else if (activeTab.value === 'Álbumes') source = albums.value;

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    source = source.filter(i => 
      (i.title || i.titulo || '').toLowerCase().includes(q) || 
      (i.artist || i.artista || '').toLowerCase().includes(q)
    );
  }
  return source;
});

const handleItemClick = async (item) => {
  if (activeTab.value === 'Playlist') {
    const playlistId = item._id || item.id;
    if (!playlistId) {
      console.warn('Playlist sin ID válido, omitiendo petición.', item);
      return;
    }
    selectedPlaylist.value = item;
    try {
      const res = await axios.get(`/api/playlists/${playlistId}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      });
      playlistSongs.value = res.data.canciones ?? [];
    } catch(e) {
      console.error('Error cargando playlist:', e);
    }
  } else if (activeTab.value === 'Álbumes') {
    if (item.canciones && item.canciones.length > 0) {
      playSong(item.canciones[0], item.canciones);
    }
  } else {
    playSong(item, likedSongs.value);
  }
};

const handleCreatePlaylist = async () => {
  if (!newPlaylistName.value) {
    await swalMango('warning', 'Campo requerido', 'El nombre de la playlist es obligatorio.');
    return;
  }
  
  let coverUrl = null;
  if (newPlaylistImageFile.value) {
    const token = localStorage.getItem('token');
    if (!token) {
      await swalMango('error', 'Sesión no encontrada', 'No se encontró la sesión activa. Por favor, vuelve a iniciar sesión.');
      return;
    }

    const fd = new FormData();
    fd.append('file', newPlaylistImageFile.value); // Backend expects 'file'
    try {
      const upRes = await axios.post('/api/upload', fd, {
        headers: { 
          'Content-Type': 'multipart/form-data',
          Authorization: `Bearer ${token}` 
        }
      });
      coverUrl = upRes.data.url;
    } catch (e) {
      console.error('Error subiendo imagen', e.response?.data || e);
      const serverErrorMsg = e.response?.data?.message || e.response?.data?.error || '';
      const errMsg = serverErrorMsg
        ? `${serverErrorMsg} (Código: ${e.response?.status})`
        : e.message;
      await swalMango('error', 'Error al subir portada', `Error subiendo la portada: ${errMsg}. Intenta con otra imagen.`);
      return;
    }
  }

  try {
    await axios.post('/api/playlists', {
      titulo: newPlaylistName.value,
      descripcion: newPlaylistDesc.value,
      caratula: coverUrl
    }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });
    
    newPlaylistName.value = '';
    newPlaylistDesc.value = '';
    newPlaylistImageFile.value = null;
    newPlaylistImagePreview.value = null;
    showCreateModal.value = false;
    loadData(); // reload
  } catch(e) {
    console.error('Error creando playlist', e);
    await swalMango('error', 'Error al crear playlist', 'Hubo un error al crear la playlist. Intenta de nuevo.');
  }
};

const eliminarCancion = async (song) => {
  const songId = song._id || song.id;
  if (!songId) {
    console.warn("Intento de eliminar canción sin ID válido.");
    return;
  }
  if (!selectedPlaylist.value) return;

  const artistName = song.artista || song.artist || 'Artista Desconocido';
  const songTitle = song.titulo || song.title || 'Canción';

  const result = await Swal.fire({
    title: '¿Remover de la playlist?',
    text: `¿Deseas remover "${songTitle}" de ${artistName}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#E53E3E',
    cancelButtonColor: '#aaa',
    confirmButtonText: 'Sí, remover',
    cancelButtonText: 'Cancelar',
    customClass: {
      popup: 'mango-swal2-popup',
      title: 'mango-swal2-title',
      htmlContainer: 'mango-swal2-text',
      actions: 'mango-swal2-actions',
      confirmButton: 'mango-swal2-confirm-btn'
    }
  });

  if (!result.isConfirmed) return;

  const plId = selectedPlaylist.value._id || selectedPlaylist.value.id;
  
  // Actualización reactiva instantánea en el frontend
  playlistSongs.value = playlistSongs.value.filter(s => (s._id || s.id) !== songId);

  try {
    await axios.delete(`/api/playlists/${plId}/songs/${songId}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });
    // Opcional: recargar el estado para asegurar sincronización perfecta con la BD
    const res = await axios.get(`/api/playlists/${plId}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });
    playlistSongs.value = res.data.canciones ?? [];
  } catch(e) {
    console.error("Error al eliminar la canción de la playlist:", e);
    swalMango('error', 'Error al eliminar', 'Hubo un error al eliminar la canción. Restableciendo la lista...');
    loadData();
  }
};

// ─── EDIT PLAYLIST ──────────────────────────────────────────────────────────
const handleEditPlaylist = async () => {
  if (!editPlaylistName.value.trim()) {
    await swalMango('warning', 'Campo requerido', 'El nombre de la playlist no puede estar vacío.');
    return;
  }
  const plId = selectedPlaylist.value._id || selectedPlaylist.value.id;
  const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };

  let coverUrl = editImagePreview.value; // keep current if no new image

  if (editImageFile.value) {
    const fd = new FormData();
    fd.append('file', editImageFile.value);
    try {
      const upRes = await axios.post('/api/upload', fd, {
        headers: {
          'Content-Type': 'multipart/form-data',
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });
      coverUrl = upRes.data.url;
    } catch (e) {
      await swalMango('error', 'Error al subir portada', 'No se pudo subir la nueva portada. Intenta con otra imagen.');
      return;
    }
  }

  try {
    const updated = await axios.put(`/api/playlists/${plId}`, {
      titulo: editPlaylistName.value,
      descripcion: editPlaylistDesc.value,
      caratula: coverUrl
    }, { headers });

    // Update local state immediately
    selectedPlaylist.value = { ...selectedPlaylist.value, ...updated.data };
    showEditModal.value = false;
    loadData();
  } catch (e) {
    console.error('Error editando playlist', e);
    await swalMango('error', 'Error al guardar', 'No se pudieron guardar los cambios. Intenta de nuevo.');
  }
};

// ─── ADD SONG TO PLAYLIST ────────────────────────────────────────────────────
const addSongToPlaylist = async (song, playlist) => {
  addToPlaylistMenu.value.show = false;
  const songId = song._id || song.id;
  const plId = playlist._id || playlist.id;
  if (!songId || !plId) return;

  try {
    await axios.post(`/api/playlists/${plId}/songs`, 
      { cancion_id: songId },
      { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
    );
    // Confirmación visual sin bloquear el hilo con SweetAlert2 Toast
    Swal.fire({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      icon: 'success',
      title: '¡Agregada!',
      text: `"${song.title || song.titulo}" fue añadida a "${playlist.titulo}"`,
      customClass: {
        popup: 'mango-toast-popup',
        title: 'mango-toast-title',
        htmlContainer: 'mango-toast-text'
      },
      showClass: {
        popup: 'animate__animated animate__fadeInRight'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutRight'
      }
    });
  } catch (e) {
    console.error('Error agregando canción', e);
    await swalMango('error', 'Error al agregar', 'No se pudo agregar la canción a la playlist.');
  }
};

// STYLES (Keep existing ones)
const libraryContainerStyle = { backgroundColor: '#FFF', padding: '60px', borderRadius: '50px', boxShadow: '0 20px 60px rgba(0,0,0,0.02)', minHeight: '85vh' };
const activeTabLinkStyle = { fontSize: '1.1rem', fontWeight: '800', color: '#FF7A1A', cursor: 'pointer', borderBottom: '3px solid #FF7A1A', paddingBottom: '5px' };
const inactiveTabLinkStyle = { ...activeTabLinkStyle, color: '#A0A0A0', fontWeight: '600', borderBottom: '3px solid transparent' };
const ytSearchBoxStyle = { display: 'flex', alignItems: 'center', gap: '15px', backgroundColor: '#F9F9F9', padding: '12px 25px', borderRadius: '30px', width: '400px', border: '1px solid #EEE' };
const ytSearchInputStyle = { border: 'none', outline: 'none', backgroundColor: 'transparent', fontSize: '1.1rem', fontWeight: '600', width: '100%' };
const activePillStyle = { padding: '10px 25px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '20px', fontSize: '1.1rem', fontWeight: '800', cursor: 'pointer' };
const inactivePillStyle = { ...activePillStyle, backgroundColor: '#F5F5F5', color: '#666', fontWeight: '600' };
const gridContainerStyle = { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: '40px 30px' };
const cardStyle = { cursor: 'pointer', transition: '0.3s' };
const imageContainerStyle = { position: 'relative', width: '100%', aspectRatio: '1', borderRadius: '15px', overflow: 'hidden' };
const overlayStyle = { position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', backgroundColor: 'rgba(0,0,0,0.3)', display: 'flex', alignItems: 'center', justifyContent: 'center', opacity: 0, transition: '0.3s' };
const createPlaylistCardStyle = { aspectRatio: '1', backgroundColor: '#FDFCFB', border: '2px dashed #EEE', borderRadius: '15px', display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', transition: '0.3s' };
const backButtonStyle = { display: 'flex', alignItems: 'center', gap: '10px', backgroundColor: 'transparent', border: 'none', color: '#666', fontWeight: '700', cursor: 'pointer', marginBottom: '30px', fontSize: '1rem' };
const playlistHeaderStyle = { display: 'flex', alignItems: 'flex-end', gap: '40px' };
const playlistImageLargeStyle = { width: '250px', height: '250px', borderRadius: '25px', overflow: 'hidden', boxShadow: '0 20px 40px rgba(0,0,0,0.1)' };
const playAllBtnStyle = { padding: '12px 30px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '30px', fontWeight: '800', display: 'flex', alignItems: 'center', gap: '10px', cursor: 'pointer', marginLeft: '20px' };
const tableHeaderStyle = { display: 'flex', padding: '0 25px 15px', borderBottom: '1px solid #F0F0F0', color: '#A0A0A0', fontWeight: '700', fontSize: '1rem', textTransform: 'uppercase', letterSpacing: '1px' };
const songRowStyle = { display: 'flex', alignItems: 'center', padding: '15px 25px', borderRadius: '20px', cursor: 'pointer', transition: '0.2s' };
const modalOverlayStyle = { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0,0,0,0.5)', backdropFilter: 'blur(10px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 5000 };
const modalContentStyle = { backgroundColor: '#FFF', padding: '45px', borderRadius: '40px', width: '480px', boxShadow: '0 30px 60px rgba(0,0,0,0.1)' };
const imageUploadBoxStyle = { width: '150px', height: '150px', backgroundColor: '#FFF9F5', borderRadius: '25px', border: '2px dashed #FFDAB9', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', margin: '0 auto', cursor: 'pointer' };
const modalInputStyle = { padding: '15px 20px', borderRadius: '12px', border: '1px solid #F0F0F0', backgroundColor: '#FBFBFB', fontSize: '1rem', outline: 'none', width: '100%' };
const modalCancelBtnStyle = { flex: 1, padding: '14px', backgroundColor: '#F5F5F5', color: '#666', border: 'none', borderRadius: '15px', fontWeight: '800', cursor: 'pointer' };
const modalCreateBtnStyle = { flex: 1, padding: '14px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '15px', fontWeight: '800', cursor: 'pointer' };
const editBtnStyle = { padding: '10px 22px', backgroundColor: '#F5F5F5', color: '#1A1614', border: 'none', borderRadius: '20px', fontWeight: '700', display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer', fontSize: '0.95rem' };
</script>

<style scoped>
.yt-card:hover { transform: translateY(-8px); }
.yt-card:hover .play-overlay { opacity: 1 !important; }
.song-row:hover { background-color: #F9F9F9; transform: scale(1.01); }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.add-to-pl-item:hover { background-color: #FFF5EE; }
</style>
