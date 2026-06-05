<template>
  <!-- Auth routes (Login, Register, Landing) - no Sidebar/Player -->
  <template v-if="isAuthRoute">
    <router-view></router-view>
  </template>

  <!-- Main App Layout with persistent Sidebar + Player -->
  <div v-else :style="{ display: 'flex', backgroundColor: '#F8F5F2', minHeight: '100vh', fontFamily: 'Poppins, sans-serif' }">
    <!-- Sidebar persiste y nunca se desmonta -->
    <Sidebar />
    
    <!-- Content area -->
    <div :style="{ marginLeft: '260px', flex: 1, minHeight: '100vh' }">
      <router-view></router-view>
    </div>

    <!-- Player global - persiste y nunca se desmonta al navegar -->
    <Player />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Sidebar from '@/components/Sidebar.vue';
import Player from '@/components/Player.vue';

const route = useRoute();

// Rutas sin layout principal (sin sidebar)
const isAuthRoute = computed(() => {
  return ['/', '/login', '/registro', '/forgot-password'].includes(route.path);
});
</script>

<style>
* {
  box-sizing: border-box;
}
body {
  margin: 0;
  padding: 0;
  font-family: 'Poppins', sans-serif;
}
</style>
