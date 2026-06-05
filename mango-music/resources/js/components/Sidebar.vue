<template>
  <div class="sidebar" :style="sidebarStyle">
    <!-- Branding -->
    <div :style="{ marginBottom: '40px', padding: '0 10px' }">
      <div :style="{ display: 'flex', alignItems: 'center', gap: '12px' }">
        <img src="@/assets/logo.png" alt="Logo" :style="{ width: '42px', height: '42px', objectFit: 'contain' }" />
        <div>
          <div :style="{ color: '#FFF', fontWeight: '800', fontSize: '1.2rem' }">Mango Music</div>
          <div :style="{ color: 'rgba(255,255,255,0.4)', fontSize: '0.65rem', marginTop: '-2px' }">Tu música, Tu Comunidad</div>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav :style="{ flex: 1, display: 'flex', flexDirection: 'column', gap: '8px' }">
      <div 
        v-for="(item, index) in menuItems" 
        :key="index" 
        @click="navigateTo(item.route)"
        :style="{
          display: 'flex',
          alignItems: 'center',
          padding: '12px 20px',
          borderRadius: '12px',
          cursor: 'pointer',
          backgroundColor: route.path === item.route ? '#FF7A1A' : 'transparent',
          color: route.path === item.route ? '#FFF' : '#A0A0A0',
          transition: 'all 0.3s ease'
        }"
      >
        <span :style="{ marginRight: '15px', display: 'flex' }">
          <component :is="item.icon" :size="20" />
        </span>
        <span :style="{ fontSize: '0.95rem', fontWeight: route.path === item.route ? '700' : '500' }">
          {{ item.label }}
        </span>
      </div>
    </nav>

    <!-- Logout button -->
    <div
      @click="handleLogout"
      :style="{
        display: 'flex',
        alignItems: 'center',
        padding: '12px 20px',
        borderRadius: '12px',
        cursor: 'pointer',
        color: '#A0A0A0',
        transition: 'all 0.3s ease',
        marginTop: '8px'
      }"
      class="logout-btn"
    >
      <span :style="{ marginRight: '15px', display: 'flex' }">
        <LogOut :size="20" />
      </span>
      <span :style="{ fontSize: '0.95rem', fontWeight: '500' }">Cerrar Sesión</span>
    </div>
  </div>
</template>

<script setup>
import { 
  LayoutDashboard, PlusCircle, Library, DollarSign, Settings as SettingsIcon, Music, Calendar, Heart, Briefcase, LogOut
} from 'lucide-vue-next';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const route = useRoute();

// Get user info
const userRaw = localStorage.getItem('user');
const user = userRaw ? JSON.parse(userRaw) : null;
const userRole = user ? user.rol : 'oyente';

const menuItems = [
  { icon: LayoutDashboard, label: 'Inicio', route: '/dashboard' },
  { icon: PlusCircle, label: 'Publicar', route: '/publicar', role: 'artista' },
  { icon: Briefcase, label: 'Convocatorias', route: '/productor/colaboraciones', role: 'productor' },
  { icon: Briefcase, label: 'Convocatorias', route: '/artista/colaboraciones', role: 'artista' },
  { icon: Heart, label: 'Apoyar Artistas', route: '/apoyar-artistas' },
  { icon: Calendar, label: 'Eventos', route: '/eventos' },
  { icon: Music, label: 'Biblioteca', route: '/biblioteca' },
  { icon: DollarSign, label: 'Monetización', route: '/monetizacion', role: 'artista' },
  { icon: SettingsIcon, label: 'Configuración', route: '/configuracion' },
].filter(item => {
  if (item.role && item.role !== userRole) return false;
  return true;
});

const navigateTo = (path) => { router.push(path); };

const handleLogout = async () => {
  const result = await Swal.fire({
    title: '¿Estás seguro?',
    text: '¿Realmente deseas cerrar sesión de tu cuenta en Mango Music?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#FF7A1A',
    cancelButtonColor: '#555',
    confirmButtonText: 'Sí, salir',
    cancelButtonText: 'Cancelar',
    borderRadius: '20px',
  });

  if (!result.isConfirmed) return;

  // Invalidate session on backend
  try {
    const token = localStorage.getItem('token');
    if (token) {
      await axios.post('/api/logout', {}, {
        headers: { Authorization: `Bearer ${token}` }
      });
    }
  } catch (_) { /* session may already be invalid, continue */ }

  // Clear all local data
  localStorage.removeItem('user');
  localStorage.removeItem('token');
  localStorage.removeItem('library');

  // Redirect to landing page
  router.push('/');
};

const sidebarStyle = {
  width: '260px',
  height: '100vh',
  background: '#1A1614',
  padding: '30px 20px',
  display: 'flex',
  flexDirection: 'column',
  position: 'fixed',
  left: 0,
  top: 0,
  boxSizing: 'border-box',
  zIndex: 3000
};
</script>

<style scoped>
.logout-btn:hover {
  background-color: rgba(248, 113, 113, 0.12) !important;
  color: #F87171 !important;
}
</style>
