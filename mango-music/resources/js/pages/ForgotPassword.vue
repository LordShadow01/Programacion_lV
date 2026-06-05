<template>
  <div :style="{ backgroundColor: '#FFF5E9', minHeight: '100vh', padding: '40px', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }">
    <div :style="{ display: 'flex', justifyContent: 'center', alignItems: 'center', width: '100%' }">
      <!-- Step 1 -->
      <div v-if="step === 1" :style="cardStyle">
        <div :style="{ textAlign: 'center', marginBottom: '30px' }">
          <img src="@/assets/logo.png" alt="Logo" :style="{ width: '80px', display: 'block', margin: '0 auto 20px' }" />
          <h2 :style="titleStyle">Restablece tu Contraseña</h2>
          <p :style="subTitleStyle">Ingresa tu correo y te enviaremos un código de verificación.</p>
        </div>

        <div v-if="errorMessage" :style="errorBoxStyle">
          {{ errorMessage }}
        </div>
        
        <div :style="{ marginBottom: '25px' }">
          <label :style="labelStyle">Correo Electrónico</label>
          <input 
            type="email" 
            placeholder="tu@correo.com" 
            :style="inputStyle" 
            v-model="email"
            required
          />
        </div>

        <button @click="sendCode" :disabled="!isEmailValid || loading" :style="!isEmailValid ? disabledButtonStyle : buttonStyle">
          {{ loading ? 'Enviando...' : 'Enviar Código' }}
        </button>

        <div :style="noteStyle">
          <strong :style="{ color: '#FF7A1A' }">Nota:</strong><br />
          El código generado tiene validez de 10 minutos.
        </div>

        <div :style="{ textAlign: 'center', marginTop: '30px' }">
          <button @click="handleBackNavigation" :style="backLinkStyle">
            {{ isLoggedIn ? '← Volver a Configuración' : '← Regresar al inicio de sesión' }}
          </button>
        </div>
      </div>

      <!-- Step 2 -->
      <div v-if="step === 2" :style="cardStyle">
        <div :style="{ textAlign: 'center', marginBottom: '30px' }">
          <img src="@/assets/logo.png" alt="Logo" :style="{ width: '80px', display: 'block', margin: '0 auto 20px' }" />
          <h2 :style="titleStyle">Verificación</h2>
          <p :style="subTitleStyle">Por favor coloca el código de verificación enviado a tu correo electrónico.</p>
        </div>

        <div v-if="errorMessage" :style="errorBoxStyle">
          {{ errorMessage }}
        </div>

        <div :style="{ display: 'flex', gap: '10px', justifyContent: 'center', marginBottom: '30px' }">
          <input 
            v-for="(digit, idx) in code" 
            :key="idx"
            :id="`code-${idx}`"
            type="text"
            maxLength="1"
            v-model="code[idx]"
            @input="handleCodeInput(idx, $event)"
            @paste="idx === 0 ? handlePaste($event) : null"
            :style="codeInputStyle"
          />
        </div>

        <button @click="verifyCode" :disabled="!isCodeComplete || loading" :style="!isCodeComplete ? disabledButtonStyle : buttonStyle">
          {{ loading ? 'Verificando...' : 'Verificar' }}
        </button>

        <div :style="{ textAlign: 'center', marginTop: '20px', fontSize: '0.9rem', color: '#666' }">
          ¿No recibiste el código? 
          <span 
            @click="reenviarCodigo" 
            :style="{ color: loading ? '#ccc' : '#FF7A1A', fontWeight: '700', cursor: loading ? 'default' : 'pointer', marginLeft: '5px' }"
          >
            Reenviar código
          </span>
        </div>

        <div :style="{ textAlign: 'center', marginTop: '30px' }">
          <button @click="step = 1" :style="backLinkStyle">← Regresar</button>
        </div>
      </div>

      <!-- Step 3 -->
      <div v-if="step === 3" :style="cardStyle">
        <div :style="{ textAlign: 'center', marginBottom: '30px' }">
          <img src="@/assets/logo.png" alt="Logo" :style="{ width: '80px', display: 'block', margin: '0 auto 20px' }" />
          <h2 :style="titleStyle">Crear Nueva Contraseña</h2>
          <p :style="subTitleStyle">Tu nueva contraseña debe tener al menos 8 caracteres.</p>
        </div>

        <div v-if="errorMessage" :style="errorBoxStyle">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="handleSavePassword" :style="{ display: 'flex', flexDirection: 'column', gap: '20px' }">
          <div>
            <label :style="labelStyle">Nueva Contraseña</label>
            <input 
              :type="showPass ? 'text' : 'password'" 
              :style="inputStyle" 
              placeholder="••••••••"
              v-model="passwords.new"
              required
            />
          </div>
          <div>
            <label :style="labelStyle">Repite la Contraseña</label>
            <input 
              :type="showPass ? 'text' : 'password'" 
              :style="inputStyle" 
              placeholder="••••••••"
              v-model="passwords.repeat"
              required
            />
          </div>

          <div :style="{ display: 'flex', alignItems: 'center', gap: '10px' }">
            <input 
              type="checkbox" 
              v-model="showPass"
              :style="{ width: '18px', height: '18px', accentColor: '#FF7A1A' }"
            />
            <span :style="{ fontSize: '0.9rem', color: '#666' }">Mostrar contraseñas</span>
          </div>

          <div :style="{ marginTop: '10px' }">
            <div :style="{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px', fontSize: '0.85rem', color: '#666' }">
              <span>Seguridad de la contraseña:</span>
            </div>
            <div :style="{ height: '6px', background: '#eee', borderRadius: '3px', overflow: 'hidden' }">
              <div :style="{ width: passwordStrength + '%', height: '100%', background: passwordStrengthColor, transition: '0.3s' }"></div>
            </div>
          </div>

          <button type="submit" :disabled="!isPasswordValid || loading" :style="!isPasswordValid ? disabledButtonStyle : buttonStyle">
            Guardar Contraseña
          </button>

          <div v-if="loading" :style="{ textAlign: 'center', marginTop: '20px' }">
            <div class="spinner"></div>
            <p :style="{ fontSize: '0.9rem', color: '#666' }">Guardando y redirigiendo...</p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import Logo from '@/components/Logo.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const router = useRouter();
const step = ref(1);
const email = ref('');
const code = reactive(['', '', '', '', '', '']);
const passwords = reactive({ new: '', repeat: '' });
const showPass = ref(false);
const loading = ref(false);
const errorMessage = ref('');

const isLoggedIn = computed(() => {
  return !!localStorage.getItem('token');
});

const isEmailValid = computed(() => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email.value);
});

const isCodeComplete = computed(() => {
  return code.every(digit => digit.length === 1 && /[0-9]/.test(digit));
});

const isPasswordValid = computed(() => {
  return passwords.new.length >= 8 && passwords.new === passwords.repeat;
});

const passwordStrength = computed(() => {
  const pass = passwords.new;
  if (!pass) return 0;
  let strength = 0;
  if (pass.length >= 8) strength += 30;
  if (/[a-z]/.test(pass)) strength += 20;
  if (/[A-Z]/.test(pass)) strength += 20;
  if (/[0-9]/.test(pass)) strength += 30;
  return strength;
});

const passwordStrengthColor = computed(() => {
  const str = passwordStrength.value;
  if (str < 50) return '#EF4444'; // Red
  if (str < 80) return '#F59E0B'; // Orange
  return '#10B981'; // Green
});

const handleBackNavigation = () => {
  if (isLoggedIn.value) {
    router.push('/configuracion');
  } else {
    router.push('/login');
  }
};

const handleCodeInput = (index, event) => {
  const value = event.target.value;
  // Solo permitir numeros
  if (value && !/[0-9]/.test(value)) {
    code[index] = '';
    return;
  }
  if (value && index < 5) {
    document.getElementById(`code-${index + 1}`).focus();
  }
};

const handlePaste = (event) => {
  event.preventDefault();
  const pasteData = (event.clipboardData || window.clipboardData).getData('text').trim();
  // Verificar si es un codigo de 6 digitos numericos
  if (/^\d{6}$/.test(pasteData)) {
    for (let i = 0; i < 6; i++) {
      code[i] = pasteData[i];
    }
    // Enfocar el ultimo input de manera segura
    setTimeout(() => {
      const el = document.getElementById('code-5');
      if (el) el.focus();
    }, 50);
  }
};

const sendCode = async () => {
  errorMessage.value = '';
  loading.value = true;
  try {
    await axios.post('/api/forgot-password/send', { email: email.value });
    step.value = 2;
  } catch (error) {
    console.error(error);
    errorMessage.value = error.response?.data?.message || 'No se pudo enviar el código. Verifica el correo.';
  } finally {
    loading.value = false;
  }
};

const reenviarCodigo = async () => {
  if (loading.value) return;
  errorMessage.value = '';
  loading.value = true;
  try {
    // Limpiamos los casilleros de verificación
    for (let i = 0; i < 6; i++) code[i] = '';
    await axios.post('/api/forgot-password/send', { email: email.value });
    Swal.fire('Reenviado', 'Se ha reenviado un nuevo código a tu correo electrónico.', 'success');
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Error al reenviar código.';
  } finally {
    loading.value = false;
  }
};

const verifyCode = async () => {
  errorMessage.value = '';
  loading.value = true;
  const verificationCode = code.join('');
  try {
    await axios.post('/api/forgot-password/verify', {
      email: email.value,
      code: verificationCode
    });
    step.value = 3;
  } catch (error) {
    console.error(error);
    errorMessage.value = error.response?.data?.message || 'Código incorrecto o vencido.';
  } finally {
    loading.value = false;
  }
};

const handleSavePassword = async () => {
  errorMessage.value = '';
  loading.value = true;
  const verificationCode = code.join('');
  try {
    await axios.post('/api/forgot-password/reset', {
      email: email.value,
      code: verificationCode,
      new_password: passwords.new,
      new_password_confirmation: passwords.repeat
    });

    Swal.fire({
      icon: 'success',
      title: 'Contraseña Actualizada',
      text: 'Tu contraseña ha sido restablecida exitosamente.',
      timer: 3000,
      showConfirmButton: false
    });

    setTimeout(() => {
      if (isLoggedIn.value) {
        router.push('/configuracion');
      } else {
        router.push('/login');
      }
    }, 2000);
  } catch (error) {
    console.error(error);
    errorMessage.value = error.response?.data?.message || 'No se pudo guardar la contraseña.';
    loading.value = false;
  }
};

const cardStyle = {
  backgroundColor: '#fff',
  padding: '60px',
  borderRadius: '30px',
  maxWidth: '650px',
  width: '100%',
  boxShadow: '0 10px 40px rgba(0,0,0,0.03)'
};

const titleStyle = {
  fontSize: '2.2rem',
  fontWeight: '800',
  color: '#1A1614',
  marginBottom: '10px'
};

const subTitleStyle = {
  color: '#666',
  fontSize: '1rem',
  lineHeight: '1.5'
};

const labelStyle = {
  display: 'block',
  fontSize: '0.9rem',
  fontWeight: '500',
  color: '#666',
  marginBottom: '10px'
};

const inputStyle = {
  width: '100%',
  padding: '15px 20px',
  borderRadius: '12px',
  border: '1px solid #F0F0F0',
  backgroundColor: '#FAFAFA',
  fontSize: '1rem',
  outline: 'none'
};

const errorBoxStyle = {
  color: '#EF4444',
  backgroundColor: '#FEE2E2',
  padding: '15px',
  borderRadius: '12px',
  fontSize: '0.95rem',
  fontWeight: '700',
  marginBottom: '20px',
  textAlign: 'center'
};

const codeInputStyle = {
  width: '60px',
  height: '70px',
  borderRadius: '12px',
  border: '2px solid #FF7A1A',
  fontSize: '1.5rem',
  fontWeight: '700',
  textAlign: 'center',
  color: '#FF7A1A',
  outline: 'none'
};

const buttonStyle = {
  width: '100%',
  padding: '18px',
  backgroundColor: '#FF7A1A',
  color: '#fff',
  borderRadius: '12px',
  fontWeight: '700',
  fontSize: '1.1rem',
  border: 'none',
  cursor: 'pointer',
  transition: '0.3s',
  boxShadow: '0 4px 15px rgba(255, 122, 26, 0.2)'
};

const disabledButtonStyle = {
  ...buttonStyle,
  backgroundColor: '#E5E7EB',
  color: '#9CA3AF',
  cursor: 'not-allowed',
  boxShadow: 'none'
};

const noteStyle = {
  marginTop: '25px',
  padding: '20px',
  backgroundColor: '#FFFBF5',
  border: '1px solid #FFE6CC',
  borderRadius: '12px',
  fontSize: '0.9rem',
  color: '#666',
  lineHeight: '1.6'
};

const backLinkStyle = {
  background: 'none',
  border: 'none',
  color: '#FF7A1A',
  fontWeight: '600',
  cursor: 'pointer',
  fontSize: '1rem'
};
</script>

<style scoped>
.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid #FF7A1A;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 10px;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
