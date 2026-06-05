<template>
  <main :style="{ display: 'flex', width: '100vw', height: '100vh', overflow: 'hidden', position: 'relative' }">
    <!-- Back Button -->
    <button 
      type="button"
      @click="router.push('/')"
      :style="{
        position: 'absolute',
        top: '30px',
        left: '30px',
        zIndex: 10,
        backgroundColor: 'rgba(255, 255, 255, 0.1)',
        backdropFilter: 'blur(10px)',
        color: '#fff',
        padding: '10px 20px',
        borderRadius: '30px',
        border: '1px solid rgba(255, 255, 255, 0.2)',
        fontWeight: '600',
        fontSize: '0.9rem',
        display: 'flex',
        alignItems: 'center',
        gap: '8px'
      }"
    >
      ← Inicio
    </button>

    <!-- Left Section - Dark -->
    <div :style="{ 
      flex: '0 0 40%', 
      backgroundImage: `linear-gradient(rgba(26, 22, 20, 0.7), rgba(26, 22, 20, 0.7)), url(${authBg})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      color: '#fff', 
      padding: '60px',
      display: 'flex',
      flexDirection: 'column',
      justifyContent: 'center',
      position: 'relative'
    }">
      <div :style="{ marginBottom: '40px' }">
        <img src="@/assets/logo.png" alt="Logo" :style="{ width: '100px', marginBottom: '20px' }" />
        <h1 :style="{ fontSize: '2.5rem', fontWeight: '800', marginBottom: '10px' }">Mango Music</h1>
        <div :style="{ fontSize: '2rem', fontWeight: '600', lineHeight: '1.2', color: '#FF7A1A' }">
          {{ title }} <br />
          <span>{{ subtitle }}</span>
        </div>
      </div>

    </div>

    <!-- Right Section - White -->
    <div :style="{ flex: 1, backgroundColor: '#fff', display: 'flex', flexDirection: 'column', overflowY: 'auto' }">
      <div :style="{ width: '100%', maxWidth: '650px', margin: 'auto', padding: '60px 40px' }">
        
        <!-- Consistent Header Toggle -->
        <div :style="{ textAlign: 'center', marginBottom: '40px' }">
          <h2 :style="{ fontSize: '2.2rem', fontWeight: '700', color: '#1A1614' }">
            {{ isLogin ? 'Iniciar Sesión' : 'Crear tu cuenta' }}
          </h2>
        </div>

        <slot></slot>
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import authBg from '@/assets/auth-bg.jpg';

const props = defineProps({
  title: String,
  subtitle: String,
  description: String
});

const router = useRouter();
const route = useRoute();

const isLogin = computed(() => route.path === '/login');
</script>
