<template>
  <AuthLayout 
    title="Bienvenido" 
    subtitle="de vuelta" 
  >
    <form @submit.prevent="handleLogin" :style="{ display: 'flex', flexDirection: 'column', gap: '25px' }">
      <div :style="fieldGroupStyle">
        <label :style="labelStyle">Correo Electrónico</label>
        <input type="email" v-model="email" placeholder="tu@correo.com" :style="inputStyle" autocomplete="off" required />
      </div>

      <div :style="fieldGroupStyle">
        <label :style="labelStyle">Contraseña</label>
        <div :style="{ position: 'relative' }">
          <input 
            :type="showPassword ? 'text' : 'password'" 
            v-model="password" 
            placeholder="••••••••" 
            :style="inputStyle" 
            autocomplete="new-password"
            required 
          />
          <div 
            @click="showPassword = !showPassword" 
            :style="{ position: 'absolute', right: '15px', top: '50%', transform: 'translateY(-50%)', cursor: 'pointer', color: '#666' }"
          >
            <Eye v-if="showPassword" :size="20" />
            <EyeOff v-else :size="20" />
          </div>
        </div>
        <!-- Advertencia de requisitos de contraseña (BUG #12) -->
        <div :style="{ fontSize: '0.9rem', color: '#FF7A1A', fontWeight: '500', marginTop: '5px' }">
          ⚠️ Mínimo 8 caracteres
        </div>
      </div>

      <div :style="{ textAlign: 'right' }">
        <span 
          @click="router.push('/forgot-password')" 
          :style="{ color: '#FF7A1A', fontSize: '1.1rem', fontWeight: '600', textDecoration: 'none', cursor: 'pointer' }"
        >
          ¿Olvidaste tu contraseña?
        </span>
      </div>



      <button type="submit" :style="{ 
        backgroundColor: '#FF7A1A', 
        color: '#fff', 
        padding: '18px', 
        borderRadius: '12px', 
        fontWeight: '700', 
        fontSize: '1.1rem', 
        marginTop: '10px',
        boxShadow: '0 4px 15px rgba(255, 122, 26, 0.2)',
        cursor: 'pointer'
      }">
        Ingresar
      </button>

      <!-- Redirection Link -->
      <div :style="{ textAlign: 'center', marginTop: '15px' }">
        <span @click="router.push('/registro')" :style="{ color: '#666', fontSize: '1rem', cursor: 'pointer' }">
          ¿No tienes cuenta? <span :style="{ color: '#FF7A1A', fontWeight: '800' }">Regístrate gratis</span>
        </span>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AuthLayout from '@/components/AuthLayout.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

import { Eye, EyeOff } from 'lucide-vue-next';

const router = useRouter();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const isLoading = ref(false);

onMounted(() => {
  localStorage.removeItem('user');
  localStorage.removeItem('token');
  // Clear fields
  email.value = '';
  password.value = '';
});

const handleLogin = async () => {
  if (isLoading.value) return;

  if (!email.value || !password.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Campos vacíos',
      text: 'Por favor, ingresa tu correo y contraseña.'
    });
    return;
  }

  try {
    isLoading.value = true;
    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value
    });

    localStorage.setItem('user', JSON.stringify(response.data.user));
    localStorage.setItem('token', response.data.token);

    Swal.fire({
      icon: 'success',
      title: '¡Bienvenido!',
      text: `Hola ${response.data.user.nombre}, has iniciado sesión correctamente.`,
      timer: 1500,
      showConfirmButton: false,
      allowOutsideClick: false,
      timerProgressBar: true
    }).then(() => {
      isLoading.value = false;
      router.push('/dashboard');
    });

  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error de inicio de sesión',
      text: error.response?.data?.message || 'Correo o contraseña incorrectos.',
      allowEscapeKey: true,
      confirmButtonText: 'OK'
    }).then(() => {
      password.value = '';
      isLoading.value = false;
    });
  }
};

const fieldGroupStyle = { display: 'flex', flexDirection: 'column', gap: '10px' };
const labelStyle = { fontSize: '1.1rem', fontWeight: '500', color: '#444' };
const inputStyle = { padding: '15px 20px', borderRadius: '12px', border: '1px solid #F0F0F0', backgroundColor: '#FAFAFA', fontSize: '1.1rem', color: '#333', width: '100%' };
</script>
