<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">

    <!-- ────────────── BANNER ────────────── -->
    <div :style="profileBannerStyle">
      <!-- Avatar -->
      <div class="avatar-container" :style="largeAvatarContainerStyle" @click="triggerImageUpload">
        <img v-if="avatarImage" :src="avatarImage" :style="{ width: '100%', height: '100%', objectFit: 'cover' }" />
        <UserIcon v-else :size="64" :style="{ color: '#FF7A1A' }" />
        <div :style="avatarOverlayStyle" class="avatar-hover">
          <Camera :size="28" />
        </div>
        <input type="file" ref="fileInput" @change="handleImageChange" accept="image/*" style="display: none" />
      </div>

      <!-- Info -->
      <div :style="{ flex: 1, paddingLeft: '30px', display: 'flex', flexDirection: 'column', justifyContent: 'center', gap: '10px' }">
        <span v-if="userRole === 'artista'" :style="verifiedBadgeStyle">ARTISTA VERIFICADO</span>
        <span v-else :style="roleBadgeStyle">{{ userRoleLabel }}</span>

        <h1 :style="{ fontSize: '4rem', fontWeight: '900', color: '#fff', margin: '0', lineHeight: '1.05', letterSpacing: '-2px' }">
          {{ artistName }}
        </h1>

        <!-- Metrics row -->
        <div :style="{ display: 'flex', gap: '40px', marginTop: '6px' }">
          <div>
            <div :style="metricValueStyle">{{ followerCount }}</div>
            <div :style="metricLabelStyle">SEGUIDORES</div>
          </div>
          <div>
            <div :style="metricValueStyle">{{ monthlyListeners }}</div>
            <div :style="metricLabelStyle">OYENTES MES</div>
          </div>
          <div>
            <div :style="metricValueStyle">{{ songs.length }}</div>
            <div :style="metricLabelStyle">CANCIONES</div>
          </div>
        </div>
      </div>

      <!-- Edit Profile btn (bottom-right) -->
      <button @click="openProfileModal" :style="editProfileBtnStyle">
        <Settings :size="16" />
        Editar Perfil
      </button>
    </div>

    <!-- ────────────── BIO ────────────── -->
    <div :style="bioSectionStyle">
      <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }">
        <h3 :style="{ fontSize: '1.2rem', fontWeight: '800', color: '#1A1614', margin: 0 }">Biografía</h3>
        <button v-if="!isEditingBio" @click="startEditingBio" :style="{ background: 'none', border: 'none', color: '#FF7A1A', fontWeight: '800', cursor: 'pointer', fontSize: '0.9rem' }">Editar</button>
      </div>
      <p v-if="!isEditingBio" :style="{ fontSize: '1rem', lineHeight: '1.7', color: '#444' }">{{ bioText }}</p>
      <div v-else :style="{ display: 'flex', flexDirection: 'column', gap: '10px' }">
        <textarea v-model="editBioText" :style="{ width: '100%', minHeight: '100px', padding: '15px', borderRadius: '15px', border: '1px solid #E5E5E5', backgroundColor: '#FAFAFA', fontSize: '1rem', resize: 'vertical', boxSizing: 'border-box' }"></textarea>
        <div :style="{ display: 'flex', gap: '10px', justifyContent: 'flex-end' }">
          <button @click="isEditingBio = false" :style="{ padding: '8px 16px', background: 'none', border: 'none', color: '#666', fontWeight: '800', cursor: 'pointer' }">Cancelar</button>
          <button @click="saveBio" :style="{ padding: '8px 16px', backgroundColor: '#FF7A1A', color: '#fff', border: 'none', borderRadius: '10px', fontWeight: '800', cursor: 'pointer' }">Guardar</button>
        </div>
      </div>
    </div>

    <!-- ────────────── SONGS ────────────── -->
    <div :style="{ marginTop: '50px' }">
      <h2 :style="{ fontSize: '1.8rem', fontWeight: '800', color: '#1A1614', marginBottom: '20px' }">
        {{ userRole === 'artista' ? 'Tus Canciones' : 'Tu Biblioteca' }}
      </h2>

      <!-- Search bar -->
      <div :style="searchContainerStyle">
        <Search :size="18" :style="{ color: '#A0A0A0', flexShrink: 0 }" />
        <input
          v-model="searchQuery"
          placeholder="Buscar en tu biblioteca"
          :style="searchInputStyle"
        />
      </div>

      <!-- Song list -->
      <div :style="{ display: 'flex', flexDirection: 'column', gap: '10px', marginTop: '18px' }">
        <div
          v-for="(song, index) in filteredSongs"
          :key="song._id || song.id"
          :style="songCardStyle"
          class="song-card-hover"
        >
          <!-- Left: index + cover + info -->
          <div :style="{ display: 'flex', alignItems: 'center', gap: '16px', flex: 1, minWidth: 0 }">
            <!-- Pin indicator -->
            <div :style="{ width: '26px', textAlign: 'center', flexShrink: 0 }">
              <Pin v-if="song.is_pinned" :size="14" :style="{ color: '#FF7A1A', transform: 'rotate(45deg)' }" />
              <span v-else :style="{ color: '#BDBDBD', fontWeight: '700', fontSize: '1rem' }">{{ index + 1 }}</span>
            </div>

            <!-- Thumbnail (clickable to play) -->
            <div :style="songThumbnailStyle" @click="reproducir(song)" class="cursor-pointer">
              <img v-if="song.caratula || song.image" :src="song.caratula || song.image" :style="{ width: '100%', height: '100%', objectFit: 'cover', borderRadius: '10px' }" />
              <Music v-else :size="24" :style="{ color: '#FF7A1A' }" />
            </div>

            <!-- Title + meta -->
            <div :style="{ flex: 1, minWidth: 0 }" @click="reproducir(song)" class="cursor-pointer">
              <div :style="{ fontWeight: '800', fontSize: '1.05rem', color: '#1A1614', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }">
                {{ song.titulo || song.title }}
              </div>
              <div :style="{ color: '#A0A0A0', fontSize: '0.82rem', fontWeight: '600', marginTop: '2px' }">
                {{ song.artista || song.artist || 'Artista Desconocido' }} • {{ song.genero || '—' }}{{ song.duracion ? ' • ' + song.duracion : '' }}
              </div>
            </div>
          </div>

          <!-- Right: plays + 3-dot menu -->
          <div :style="{ display: 'flex', alignItems: 'center', gap: '20px', flexShrink: 0 }">
            <span v-if="song.reproducciones > 0" :style="{ color: '#FF7A1A', fontWeight: '700', fontSize: '0.88rem', whiteSpace: 'nowrap' }">
              {{ formatPlays(song.reproducciones) }} de oyentes
            </span>

            <!-- Three-dot button (artists only) -->
            <div v-if="userRole === 'artista'" :style="{ position: 'relative' }">
              <button
                @click.stop="toggleDropdown(song._id || song.id)"
                :style="dotsBtnStyle"
                class="dots-btn-hover"
              >···</button>

              <!-- Dropdown -->
              <div
                v-if="activeDropdown === (song._id || song.id)"
                :style="dropdownStyle"
                @click.stop
              >
                <button @click="openEditModal(song)" :style="dropdownItemStyle" class="dropdown-item-hover">
                  <Pencil :size="14" /> Editar
                </button>
                <button @click="handlePin(song)" :style="dropdownItemStyle" class="dropdown-item-hover">
                  <Pin :size="14" :style="{ transform: 'rotate(45deg)' }" />
                  {{ song.is_pinned ? 'Desfijar' : 'Fijar canción' }}
                </button>
                <div :style="{ height: '1px', backgroundColor: '#F0F0F0', margin: '4px 0' }"></div>
                <button @click="handleDelete(song)" :style="{ ...dropdownItemStyle, color: '#E53E3E' }" class="dropdown-item-hover">
                  <Trash2 :size="14" /> Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>

        <p v-if="filteredSongs.length === 0 && searchQuery" :style="{ color: '#A0A0A0', textAlign: 'center', marginTop: '30px' }">
          No se encontraron canciones para "{{ searchQuery }}"
        </p>
      </div>
    </div>

    <!-- ────────────── EDIT MODAL ────────────── -->
    <div v-if="editModal.open" :style="modalOverlayStyle" @click.self="closeEditModal">
      <div :style="modalBoxStyle">
        <h3 :style="{ fontSize: '1.4rem', fontWeight: '800', color: '#1A1614', marginBottom: '24px' }">Editar Canción</h3>

        <div :style="{ display: 'flex', flexDirection: 'column', gap: '16px' }">
          <div>
            <label :style="modalLabelStyle">Título *</label>
            <input v-model="editModal.titulo" :style="modalInputStyle" placeholder="Nombre de la canción" />
          </div>
          <div>
            <label :style="modalLabelStyle">Género</label>
            <input v-model="editModal.genero" :style="modalInputStyle" placeholder="Pop, Rock, Urbano..." />
          </div>
          <div>
            <label :style="modalLabelStyle">URL de Carátula</label>
            <input v-model="editModal.caratula" :style="modalInputStyle" placeholder="https://..." />
          </div>
        </div>

        <div :style="{ display: 'flex', gap: '12px', justifyContent: 'flex-end', marginTop: '28px' }">
          <button @click="closeEditModal" :style="{ padding: '12px 24px', background: 'none', border: '1px solid #E5E5E5', borderRadius: '12px', fontWeight: '700', cursor: 'pointer', color: '#666' }">
            Cancelar
          </button>
          <button @click="handleUpdate" :style="{ padding: '12px 24px', backgroundColor: '#FF7A1A', color: '#fff', border: 'none', borderRadius: '12px', fontWeight: '700', cursor: 'pointer' }">
            Guardar Cambios
          </button>
        </div>
      </div>
    </div>

    <!-- ────────────── PROFILE EDIT MODAL ────────────── -->
    <div v-if="profileModal.open" :style="modalOverlayStyle" @click.self="profileModal.open = false">
      <div :style="modalBoxStyle">
        <h3 :style="{ fontSize: '1.4rem', fontWeight: '800', color: '#1A1614', marginBottom: '8px' }">Editar Perfil</h3>
        <p :style="{ fontSize: '0.9rem', color: '#888', marginBottom: '24px' }">Los cambios se reflejarán en toda la plataforma.</p>

        <div :style="{ display: 'flex', flexDirection: 'column', gap: '16px' }">
          <div>
            <label :style="modalLabelStyle">{{ profileModal.nameLabel }} *</label>
            <input v-model="profileModal.nameValue" :style="modalInputStyle" :placeholder="profileModal.nameLabel" />
          </div>
        </div>

        <div :style="{ display: 'flex', gap: '12px', justifyContent: 'flex-end', marginTop: '28px' }">
          <button @click="profileModal.open = false" :style="{ padding: '12px 24px', background: 'none', border: '1px solid #E5E5E5', borderRadius: '12px', fontWeight: '700', cursor: 'pointer', color: '#666' }">
            Cancelar
          </button>
          <button @click="saveProfileName" :style="{ padding: '12px 24px', backgroundColor: '#FF7A1A', color: '#fff', border: 'none', borderRadius: '12px', fontWeight: '700', cursor: 'pointer' }">
            Guardar Cambios
          </button>
        </div>
      </div>
    </div>

  </main>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { User as UserIcon, Camera, Play, Search, Pin, Pencil, Trash2, Music, Settings } from 'lucide-vue-next';
import axios from 'axios';
import Swal from 'sweetalert2';
import { playSong } from '@/playerState';

const router = useRouter();

// ── State ──────────────────────────────────────────
const songs = ref([]);
const artistName = ref('');
const bioText = ref('');
const editBioText = ref('');
const isEditingBio = ref(false);
const avatarImage = ref(null);
const userRole = ref('oyente');
const fileInput = ref(null);
const searchQuery = ref('');
const activeDropdown = ref(null);
const followerCount = ref('0');
const monthlyListeners = ref('0');

const editModal = reactive({
  open: false,
  id: null,
  titulo: '',
  genero: '',
  caratula: '',
});

const profileModal = reactive({
  open: false,
  nameLabel: 'Nombre Artístico',
  nameValue: '',
  field: 'nombre_artistico',
});

// ── Computed ───────────────────────────────────────
const userRoleLabel = computed(() => {
  const roles = { artista: 'Artista', oyente: 'Público', productor: 'Productor', entidad: 'Entidad Cultural' };
  return roles[userRole.value] || 'Usuario';
});

const filteredSongs = computed(() => {
  const q = searchQuery.value.toLowerCase();
  const list = q
    ? songs.value.filter(s => (s.titulo || s.title || '').toLowerCase().includes(q) || (s.genero || '').toLowerCase().includes(q))
    : songs.value;
  // Pinned songs first
  return [...list].sort((a, b) => (b.is_pinned ? 1 : 0) - (a.is_pinned ? 1 : 0));
});

// ── Lifecycle ──────────────────────────────────────
onMounted(async () => {
  const userRaw = localStorage.getItem('user');
  if (userRaw) {
    const user = JSON.parse(userRaw);
    userRole.value = user.rol;
    artistName.value = user.nombre_artistico || user.nombre;
    bioText.value = user.biografia || 'Amante de la música en Mango Music.';
    editBioText.value = user.biografia || '';
    avatarImage.value = user.foto;

    // Fetch follower count
    try {
      const res = await axios.get(`/api/seguidores/count/${user.id || user._id}`);
      const count = res.data.count || 0;
      followerCount.value = formatPlays(count);
    } catch (_) { followerCount.value = '0'; }
  }

  if (userRole.value === 'artista') {
    await fetchUserSongs();
    // Monthly listeners = sum of reproducciones
    const total = songs.value.reduce((acc, s) => acc + (s.reproducciones || 0), 0);
    monthlyListeners.value = formatPlays(total);
  } else {
    const lib = JSON.parse(localStorage.getItem('library') || '[]');
    songs.value = lib;
  }

  // Close dropdown on outside click
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});

// ── Helpers ────────────────────────────────────────
const formatPlays = (n) => {
  if (!n) return '0';
  if (n >= 1_000_000) return (n / 1_000_000).toFixed(1).replace('.0', '') + ' M';
  if (n >= 1_000) return (n / 1_000).toFixed(0) + ' mil';
  return String(n);
};

const closeDropdown = () => { activeDropdown.value = null; };
const toggleDropdown = (id) => {
  activeDropdown.value = activeDropdown.value === id ? null : id;
};

const openProfileModal = () => {
  const roleFieldMap = {
    artista: { label: 'Nombre Artístico', field: 'nombre_artistico' },
    oyente:  { label: 'Nombre Completo',   field: 'nombre' },
    productor: { label: 'Nombre Completo', field: 'nombre' },
    entidad: { label: 'Nombre de la Entidad', field: 'nombre_entidad' },
  };
  const map = roleFieldMap[userRole.value] || roleFieldMap.oyente;
  profileModal.nameLabel = map.label;
  profileModal.field = map.field;
  profileModal.nameValue = artistName.value;
  profileModal.open = true;
};

const saveProfileName = async () => {
  if (!profileModal.nameValue.trim()) {
    Swal.fire('Atención', `El campo "${profileModal.nameLabel}" es requerido.`, 'warning');
    return;
  }
  try {
    const token = localStorage.getItem('token');
    const payload = { [profileModal.field]: profileModal.nameValue.trim() };
    const res = await axios.post('/api/usuario/update', payload, {
      headers: { Authorization: `Bearer ${token}` }
    });
    const updatedUser = res.data.user;
    localStorage.setItem('user', JSON.stringify(updatedUser));
    // Update reactive name so all views using localStorage see the new name
    artistName.value = updatedUser.nombre_artistico || updatedUser.nombre_entidad || updatedUser.nombre;
    profileModal.open = false;
    Swal.fire({ icon: 'success', title: '¡Perfil actualizado!', text: 'El cambio ya es visible en toda la plataforma.', timer: 1800, showConfirmButton: false });
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'No se pudo actualizar el perfil.', 'error');
  }
};

// ── Songs CRUD ────────────────────────────────────
const fetchUserSongs = async () => {
  try {
    const user = JSON.parse(localStorage.getItem('user'));
    const response = await axios.get('/api/canciones');
    songs.value = (response.data.data || response.data)
      .filter(s => s.user_id == (user.id || user._id))
      .map(s => ({ ...s, title: s.titulo, artist: s.artista, image: s.caratula }));
  } catch (error) { console.error(error); }
};

const reproducir = (song) => playSong(song, songs.value);

const handleDelete = async (song) => {
  activeDropdown.value = null;
  const id = song._id || song.id;
  const artistName = song.artista || song.artist || 'Artista Desconocido';
  const songTitle = song.titulo || song.title || 'Canción';

  const result = await Swal.fire({
    title: '¿Eliminar canción?',
    text: `¿Seguro que deseas eliminar "${songTitle}" de ${artistName}? Esta acción no se puede deshacer.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#E53E3E',
    cancelButtonColor: '#aaa',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  });
  if (!result.isConfirmed) return;

  try {
    const token = localStorage.getItem('token');
    await axios.delete(`/api/canciones/${id}`, { headers: { Authorization: `Bearer ${token}` } });
    songs.value = songs.value.filter(s => (s._id || s.id) !== id);
    Swal.fire({ icon: 'success', title: 'Canción eliminada', timer: 1500, showConfirmButton: false });
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'No se pudo eliminar la canción.', 'error');
  }
};

const handlePin = async (song) => {
  activeDropdown.value = null;
  try {
    const token = localStorage.getItem('token');
    const id = song._id || song.id;
    const res = await axios.patch(`/api/canciones/${id}/pin`, {}, { headers: { Authorization: `Bearer ${token}` } });
    const idx = songs.value.findIndex(s => (s._id || s.id) === id);
    if (idx !== -1) songs.value[idx].is_pinned = res.data.is_pinned;
    Swal.fire({ icon: 'success', title: res.data.message, timer: 1200, showConfirmButton: false });
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'No se pudo fijar la canción.', 'error');
  }
};

const openEditModal = (song) => {
  activeDropdown.value = null;
  editModal.open = true;
  editModal.id = song._id || song.id;
  editModal.titulo = song.titulo || song.title || '';
  editModal.genero = song.genero || '';
  editModal.caratula = song.caratula || song.image || '';
};

const closeEditModal = () => { editModal.open = false; };

const handleUpdate = async () => {
  if (!editModal.titulo.trim()) {
    Swal.fire('Atención', 'El título es requerido.', 'warning');
    return;
  }
  try {
    const token = localStorage.getItem('token');
    const res = await axios.put(`/api/canciones/${editModal.id}`, {
      titulo: editModal.titulo,
      genero: editModal.genero,
      caratula: editModal.caratula,
    }, { headers: { Authorization: `Bearer ${token}` } });

    const idx = songs.value.findIndex(s => (s._id || s.id) === editModal.id);
    if (idx !== -1) {
      songs.value[idx] = { ...songs.value[idx], ...res.data, title: res.data.titulo, image: res.data.caratula };
    }
    closeEditModal();
    Swal.fire({ icon: 'success', title: 'Canción actualizada', timer: 1500, showConfirmButton: false });
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'No se pudo actualizar la canción.', 'error');
  }
};

// ── Profile (avatar + bio) ────────────────────────
const triggerImageUpload = () => fileInput.value.click();

const handleImageChange = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  const formData = new FormData();
  formData.append('file', file);
  try {
    const token = localStorage.getItem('token');
    const res = await axios.post('/api/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data', Authorization: `Bearer ${token}` }
    });
    avatarImage.value = res.data.url;
    const userRes = await axios.post('/api/usuario/update', { foto: res.data.url }, {
      headers: { Authorization: `Bearer ${token}` }
    });
    localStorage.setItem('user', JSON.stringify(userRes.data.user));
    Swal.fire({ icon: 'success', title: 'Foto actualizada', timer: 1500, showConfirmButton: false });
    window.location.reload();
  } catch (_) { Swal.fire('Error', 'No se pudo subir la imagen', 'error'); }
};

const startEditingBio = () => {
  const userRaw = localStorage.getItem('user');
  if (userRaw) editBioText.value = JSON.parse(userRaw).biografia || '';
  isEditingBio.value = true;
};

const saveBio = async () => {
  try {
    const token = localStorage.getItem('token');
    const userRes = await axios.post('/api/usuario/update', { biografia: editBioText.value }, {
      headers: { Authorization: `Bearer ${token}` }
    });
    localStorage.setItem('user', JSON.stringify(userRes.data.user));
    bioText.value = editBioText.value || 'Amante de la música en Mango Music.';
    isEditingBio.value = false;
    Swal.fire({ icon: 'success', title: 'Biografía actualizada', timer: 1500, showConfirmButton: false });
  } catch (_) { Swal.fire('Error', 'No se pudo actualizar la biografía', 'error'); }
};

// ── Styles ─────────────────────────────────────────
const profileBannerStyle = {
  background: 'linear-gradient(135deg, #7B3F00 0%, #C96A1A 50%, #FF9F5A 100%)',
  borderRadius: '32px',
  padding: '40px 50px',
  display: 'flex',
  alignItems: 'center',
  gap: '0',
  position: 'relative',
  minHeight: '220px',
  overflow: 'hidden',
};
const largeAvatarContainerStyle = {
  width: '160px', height: '160px', borderRadius: '28px',
  backgroundColor: 'rgba(255,255,255,0.15)', border: '3px solid rgba(255,255,255,0.3)',
  flexShrink: 0, position: 'relative', overflow: 'hidden',
  cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
  boxShadow: '0 12px 30px rgba(0,0,0,0.25)',
};
const avatarOverlayStyle = {
  position: 'absolute', inset: 0, backgroundColor: 'rgba(0,0,0,0.45)',
  display: 'flex', alignItems: 'center', justifyContent: 'center',
  color: '#fff', opacity: 0, transition: '0.3s',
};
const verifiedBadgeStyle = {
  backgroundColor: 'rgba(255,255,255,0.2)', color: '#fff',
  padding: '5px 14px', borderRadius: '8px', fontSize: '0.72rem',
  fontWeight: '800', letterSpacing: '1px',
  display: 'inline-block', alignSelf: 'flex-start',
  backdropFilter: 'blur(4px)', border: '1px solid rgba(255,255,255,0.3)',
  width: 'fit-content',
};
const roleBadgeStyle = { ...verifiedBadgeStyle, backgroundColor: 'rgba(26,22,20,0.6)' };
const metricValueStyle = {
  fontSize: '1.6rem', fontWeight: '900', color: '#fff', lineHeight: '1',
};
const metricLabelStyle = {
  fontSize: '0.68rem', fontWeight: '700', color: 'rgba(255,255,255,0.65)',
  letterSpacing: '1.5px', marginTop: '2px',
};
const editProfileBtnStyle = {
  position: 'absolute', bottom: '28px', right: '36px',
  backgroundColor: 'rgba(255,255,255,0.15)', backdropFilter: 'blur(8px)',
  border: '1px solid rgba(255,255,255,0.3)', color: '#fff',
  padding: '10px 20px', borderRadius: '20px', fontWeight: '700', fontSize: '0.9rem',
  cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '8px',
  transition: '0.2s',
};
const bioSectionStyle = {
  marginTop: '30px', backgroundColor: '#fff', padding: '28px 36px',
  borderRadius: '24px', border: '1px solid #eee',
};
const searchContainerStyle = {
  display: 'flex', alignItems: 'center', gap: '12px',
  backgroundColor: '#F5F5F5', borderRadius: '30px',
  padding: '12px 20px', maxWidth: '400px',
};
const searchInputStyle = {
  background: 'none', border: 'none', outline: 'none',
  fontSize: '0.95rem', color: '#1A1614', width: '100%',
};
const songCardStyle = {
  backgroundColor: '#fff', padding: '14px 20px', borderRadius: '18px',
  display: 'flex', alignItems: 'center', gap: '0',
  border: '1px solid #F0F0F0', transition: '0.2s',
  boxShadow: '0 1px 4px rgba(0,0,0,0.04)',
};
const songThumbnailStyle = {
  width: '52px', height: '52px', backgroundColor: '#FFF9F5',
  borderRadius: '12px', flexShrink: 0,
  display: 'flex', alignItems: 'center', justifyContent: 'center',
  overflow: 'hidden', cursor: 'pointer',
};
const dotsBtnStyle = {
  background: 'none', border: 'none', cursor: 'pointer',
  fontSize: '1.4rem', color: '#BDBDBD', padding: '4px 8px',
  borderRadius: '8px', lineHeight: '1', transition: '0.2s',
  display: 'flex', alignItems: 'center',
};
const dropdownStyle = {
  position: 'absolute', right: 0, top: 'calc(100% + 6px)',
  backgroundColor: '#fff', borderRadius: '14px',
  boxShadow: '0 8px 30px rgba(0,0,0,0.12)', border: '1px solid #F0F0F0',
  zIndex: 100, minWidth: '170px', padding: '6px',
};
const dropdownItemStyle = {
  display: 'flex', alignItems: 'center', gap: '10px',
  width: '100%', padding: '10px 14px', background: 'none',
  border: 'none', cursor: 'pointer', borderRadius: '10px',
  fontSize: '0.92rem', fontWeight: '600', color: '#1A1614',
  textAlign: 'left', transition: '0.15s',
};
const modalOverlayStyle = {
  position: 'fixed', inset: 0, backgroundColor: 'rgba(0,0,0,0.45)',
  zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center',
  backdropFilter: 'blur(4px)',
};
const modalBoxStyle = {
  backgroundColor: '#fff', borderRadius: '24px', padding: '40px',
  width: '100%', maxWidth: '480px', boxShadow: '0 20px 60px rgba(0,0,0,0.2)',
};
const modalLabelStyle = {
  display: 'block', fontSize: '0.9rem', fontWeight: '700',
  color: '#555', marginBottom: '8px',
};
const modalInputStyle = {
  width: '100%', padding: '13px 18px', borderRadius: '12px',
  border: '1px solid #E5E5E5', backgroundColor: '#FAFAFA',
  fontSize: '1rem', color: '#333', boxSizing: 'border-box', outline: 'none',
};
</script>

<style scoped>
.avatar-container:hover .avatar-hover { opacity: 1 !important; }
.song-card-hover:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-1px); }
.dots-btn-hover:hover { background-color: #F5F5F5 !important; color: #1A1614 !important; }
.dropdown-item-hover:hover { background-color: #FFF9F5 !important; color: #FF7A1A !important; }
.cursor-pointer { cursor: pointer; }
</style>
