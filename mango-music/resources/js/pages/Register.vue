<template>
  <div v-if="showTerms">
    <TermsPage @accept="handleAcceptTerms" @back="showTerms = false" />
  </div>

  <AuthLayout 
    v-else
    title="Tu música," 
    subtitle="Tu Comunidad." 
  >
    <form @submit.prevent="handleRegister" :style="{ display: 'flex', flexDirection: 'column', gap: '20px' }">
      <!-- Role Selection at the Top -->
      <div>
        <label :style="{ ...labelStyle, display: 'block', marginBottom: '15px' }">Selecciona tu perfil</label>
        <div :style="{ display: 'flex', gap: '10px', flexWrap: 'wrap' }">
          <button 
            v-for="type in accountTypes" 
            :key="type.key"
            type="button"
            @click="form.rol = type.key"
            :style="{
              padding: '10px 20px',
              borderRadius: '25px',
              fontSize: '1rem',
              fontWeight: '600',
              transition: '0.2s',
              backgroundColor: form.rol === type.key ? '#FF7A1A' : '#F5F5F5',
              color: form.rol === type.key ? '#fff' : '#666',
              border: 'none',
              cursor: 'pointer'
            }"
          >
            {{ type.label }}
          </button>
        </div>
      </div>

      <div :style="fieldGroupStyle">
        <label :style="labelStyle">Nombre Completo*</label>
        <input type="text" v-model="form.nombre" placeholder="Tu nombre completo" :style="inputStyle" autocomplete="off" required />
      </div>

      <!-- Dynamic Fields based on Role -->
      <div v-if="form.rol === 'artista'" :style="fieldGroupStyle">
        <label :style="labelStyle">Nombre Artístico*</label>
        <input type="text" v-model="form.nombre_artistico" placeholder="¿Cómo te conocen tus fans?" :style="inputStyle" required />
      </div>

      <div v-if="form.rol === 'productor'" :style="fieldGroupStyle">
        <label :style="labelStyle">Nombre del Productor*</label>
        <input type="text" v-model="form.nombre_productor" placeholder="Tu sello o nombre de productor" :style="inputStyle" required />
      </div>

      <div v-if="form.rol === 'entidad'" :style="fieldGroupStyle">
        <label :style="labelStyle">Nombre de la Entidad Cultural*</label>
        <input type="text" v-model="form.nombre_entidad" placeholder="Nombre de la institución" :style="inputStyle" required />
      </div>

      <div :style="fieldGroupStyle">
        <label :style="labelStyle">Correo Electrónico*</label>
        <input type="email" v-model="form.email" placeholder="tu@correo.com" :style="inputStyle" autocomplete="off" required />
      </div>

      <div v-if="form.rol === 'artista'" :style="{ display: 'flex', gap: '20px' }">
        <div :style="{ ...fieldGroupStyle, flex: 1 }">
          <label :style="labelStyle">Fecha Nacimiento*</label>
          <input type="date" v-model="form.fecha_nacimiento" :style="inputStyle" required />
        </div>
        <div :style="{ ...fieldGroupStyle, flex: 1 }">
          <label :style="labelStyle">Género Musical</label>
          <select v-model="form.genero_musical" :style="inputStyle">
            <option value="" disabled selected>Seleccionar...</option>
            <option value="Pop">Pop</option>
            <option value="Rock">Rock</option>
            <option value="Urbano">Urbano</option>
            <option value="Electrónica">Electrónica</option>
            <option value="Regional">Regional</option>
            <option value="Alternativo">Alternativo</option>
            <option value="Otro">Otro</option>
          </select>
        </div>
      </div>

      <div :style="fieldGroupStyle">
        <label :style="labelStyle">Contraseña*</label>
        <div :style="{ position: 'relative' }">
          <input 
            :type="showPassword ? 'text' : 'password'" 
            v-model="form.password" 
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
        <div :style="{ fontSize: '0.9rem', color: '#FF7A1A', fontWeight: '500', marginTop: '5px' }">
          La contraseña debe tener al menos 8 caracteres y uno de los siguientes: Una letra mayúscula o símbolo.
        </div>
      </div>

      <div :style="{ display: 'flex', alignItems: 'center', gap: '12px', marginTop: '10px' }">
        <input 
          type="checkbox" 
          id="terms" 
          v-model="termsAccepted"
          :style="{ width: '20px', height: '20px', accentColor: '#FF7A1A', cursor: 'pointer' }" 
        />
        <label for="terms" :style="{ fontSize: '1.1rem', color: '#666', cursor: 'pointer' }">
          Acepto los <span @click.stop="showTerms = true" :style="{ color: '#FF7A1A', fontWeight: '600', textDecoration: 'underline' }">Términos y Condiciones</span> de Mango Music
        </label>
      </div>

      <button 
        type="submit" 
        :disabled="!termsAccepted"
        :style="{ 
          backgroundColor: termsAccepted ? '#FF7A1A' : '#CCC', 
          color: '#fff', 
          padding: '18px', 
          borderRadius: '12px', 
          fontWeight: '700', 
          fontSize: '1.1rem', 
          marginTop: '10px',
          boxShadow: termsAccepted ? '0 4px 15px rgba(255, 122, 26, 0.2)' : 'none',
          cursor: termsAccepted ? 'pointer' : 'not-allowed',
          transition: '0.3s'
        }"
      >
        Crear Cuenta
      </button>

      <!-- Enlace para volver al login -->
      <div :style="{ textAlign: 'center', marginTop: '15px' }">
        <span :style="{ color: '#666', fontSize: '1rem' }">
          ¿Ya tienes una cuenta? <router-link to="/login" :style="{ color: '#FF7A1A', fontWeight: '800', textDecoration: 'none', cursor: 'pointer' }">Inicia sesión</router-link>
        </span>
      </div>


    </form>
  </AuthLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import AuthLayout from '@/components/AuthLayout.vue';
import TermsPage from './TermsPage.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useRouter } from 'vue-router';
import { Eye, EyeOff } from 'lucide-vue-next';

const router = useRouter();
const showTerms = ref(false);
const termsAccepted = ref(false);
const showPassword = ref(false);

const form = reactive({
  nombre: '',
  email: '',
  password: '',
  rol: 'oyente',
  nombre_artistico: '',
  nombre_productor: '',
  nombre_entidad: '',
  fecha_nacimiento: '',
  genero_musical: ''
});

const accountTypes = [
  { label: 'Público', key: 'oyente' },
  { label: 'Artista', key: 'artista' },
  { label: 'Productor', key: 'productor' },
  { label: 'Entidad Cultural', key: 'entidad' }
];

const handleAcceptTerms = () => {
  termsAccepted.value = true;
  showTerms.value = false;
};

onMounted(() => {
  // Clear state on entry
  localStorage.removeItem('user');
  localStorage.removeItem('token');
  
  // Clear form fields
  form.nombre = '';
  form.email = '';
  form.password = '';
  form.nombre_artistico = '';
  form.nombre_productor = '';
  form.nombre_entidad = '';
  form.fecha_nacimiento = '';
  form.genero_musical = '';
});

const handleRegister = async () => {
  try {
    const response = await axios.post('/api/registro', form);
    localStorage.setItem('user', JSON.stringify(response.data.user));
    localStorage.setItem('token', response.data.token);

    Swal.fire({
      icon: 'success',
      title: '¡Registro exitoso!',
      text: 'Tu cuenta ha sido creada.',
      timer: 1500,
      showConfirmButton: false
    });
    router.push('/dashboard');
  } catch (error) {
    form.password = '';
    Swal.fire({
      icon: 'error',
      title: 'Error al registrar',
      text: error.response?.data?.message || 'Ocurrió un problema.',
    });
  }
};

const fieldGroupStyle = { display: 'flex', flexDirection: 'column', gap: '10px' };
const labelStyle = { fontSize: '1.1rem', fontWeight: '500', color: '#444' };
const inputStyle = { padding: '15px 20px', borderRadius: '12px', border: '1px solid #F0F0F0', backgroundColor: '#FAFAFA', fontSize: '1.1rem', color: '#333', width: '100%' };
</script>
