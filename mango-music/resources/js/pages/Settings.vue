<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
      <div :style="{ marginBottom: '35px' }">
        <h2 :style="{ fontSize: '2rem', fontWeight: '800', color: '#1A1614' }">Configuración General</h2>
      </div>

      <div :style="{ display: 'grid', gridTemplateColumns: '1.2fr 0.8fr', gap: '30px' }">
        
        <!-- Left Column: Playback Preferences -->
        <div :style="settingsCardStyle">
          <h3 :style="sectionTitleStyle">Preferencias de Reproducción</h3>
          
          <!-- Language Selector -->
          <div :style="settingRowStyle">
            <div>
              <div :style="settingLabelStyle">Idioma</div>
              <div :style="settingSubLabelStyle">Selecciona el idioma de la app</div>
            </div>
            <select v-model="settings.language" @change="saveSettings" :style="selectStyle">
              <option value="es">Español</option>
              <option value="en">English</option>
            </select>
          </div>

          <!-- Toggles -->
          <div :style="settingRowStyle">
            <div>
              <div :style="settingLabelStyle">Reproduce junto con otras apps</div>
              <div :style="settingSubLabelStyle">Permite reproducción simultánea</div>
            </div>
            <div @click="toggleSetting('playbackOther')" :style="settings.playbackOther ? switchActiveStyle : switchInactiveStyle">
              <div :style="settings.playbackOther ? knobActiveStyle : knobInactiveStyle"></div>
            </div>
          </div>

          <div :style="settingRowStyle">
            <div>
              <div :style="settingLabelStyle">Reproducción sin pausas</div>
              <div :style="settingSubLabelStyle">Encadena canciones automáticamente</div>
            </div>
            <div @click="toggleSetting('gapless')" :style="settings.gapless ? switchActiveStyle : switchInactiveStyle">
              <div :style="settings.gapless ? knobActiveStyle : knobInactiveStyle"></div>
            </div>
          </div>

          <div :style="settingRowStyle">
            <div>
              <div :style="settingLabelStyle">Tiempo de Reproducción</div>
              <div :style="settingSubLabelStyle">Muestra el tiempo al reproducir</div>
            </div>
            <div @click="toggleSetting('showTime')" :style="settings.showTime ? switchActiveStyle : switchInactiveStyle">
              <div :style="settings.showTime ? knobActiveStyle : knobInactiveStyle"></div>
            </div>
          </div>

          <div :style="settingRowStyle">
            <div>
              <div :style="settingLabelStyle">Notificaciones</div>
              <div :style="settingSubLabelStyle">Recibe alertas de novedades y lanzamientos</div>
            </div>
            <div @click="toggleSetting('notifications')" :style="settings.notifications ? switchActiveStyle : switchInactiveStyle">
              <div :style="settings.notifications ? knobActiveStyle : knobInactiveStyle"></div>
            </div>
          </div>
        </div>

        <!-- Right Column: Account & Security -->
        <div :style="{ display: 'flex', flexDirection: 'column', gap: '30px' }">
          <div :style="settingsCardStyle">
            <h3 :style="sectionTitleStyle">Cuenta y Seguridad</h3>
            <div :style="actionRowStyle" @click="openPasswordModal">
              <div>
                <div :style="settingLabelStyle">Cambiar Contraseña</div>
                <div :style="settingSubLabelStyle">Actualiza tu clave de acceso</div>
              </div>
              <ChevronRight :size="20" :style="{ color: '#BCBCBC' }" />
            </div>
          </div>
        </div>
      </div>

      <!-- PASSWORD CHANGE MODAL -->
      <div v-if="showPasswordModal" :style="modalOverlayStyle" @click.self="showPasswordModal = false">
        <div :style="modalContentStyle">
          <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '15px' }">
            <div>
              <h3 :style="{ fontSize: '1.5rem', fontWeight: '900', color: '#1A1614' }">Cambiar Contraseña</h3>
              <p :style="{ fontSize: '0.85rem', color: '#888', marginTop: '3px' }">Asegura tu cuenta con una nueva clave</p>
            </div>
            <button @click="showPasswordModal = false" :style="{ background: 'none', border: 'none', cursor: 'pointer', color: '#999' }">
              <X :size="24" />
            </button>
          </div>

          <form @submit.prevent="savePassword" :style="{ display: 'flex', flexDirection: 'column', gap: '16px', marginTop: '10px' }">
            <div v-if="errorMessage" :style="{ color: '#EF4444', backgroundColor: '#FEE2E2', padding: '12px 16px', borderRadius: '12px', fontSize: '0.9rem', fontWeight: '700' }">
              {{ errorMessage }}
            </div>

            <!-- Current Password -->
            <div :style="fieldGroupStyle">
              <label :style="labelStyle">Contraseña Actual</label>
              <input 
                v-model="passwordForm.current_password" 
                type="password" 
                placeholder="••••••••" 
                :style="modalInputStyle" 
                required 
              />
            </div>

            <!-- New Password -->
            <div :style="fieldGroupStyle">
              <label :style="labelStyle">Nueva Contraseña</label>
              <input 
                v-model="passwordForm.new_password" 
                type="password" 
                placeholder="Mínimo 8 caracteres" 
                :style="modalInputStyle" 
                required 
              />
            </div>

            <!-- Confirm New Password -->
            <div :style="fieldGroupStyle">
              <label :style="labelStyle">Repetir Nueva Contraseña</label>
              <input 
                v-model="passwordForm.new_password_confirmation" 
                type="password" 
                placeholder="Confirmar contraseña" 
                :style="modalInputStyle" 
                required 
              />
            </div>

            <div :style="{ textAlign: 'left', margin: '5px 0 10px 0' }">
              <router-link to="/forgot-password" :style="{ color: '#FF7A1A', textDecoration: 'none', fontWeight: '800', fontSize: '0.9rem' }">
                ¿Se te olvidó tu contraseña?
              </router-link>
            </div>

            <!-- Action buttons -->
            <div :style="{ display: 'flex', gap: '12px' }">
              <button type="button" @click="showPasswordModal = false" :style="cancelButtonStyle">
                Cancelar
              </button>
              <button type="submit" :disabled="savingPassword" :style="savePasswordBtnStyle">
                {{ savingPassword ? 'Guardando...' : 'Guardar Cambios' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { ChevronRight, X } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const showPasswordModal = ref(false);
const savingPassword = ref(false);
const errorMessage = ref('');

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const settings = reactive({
  language: 'es',
  playbackOther: true,
  gapless: false,
  showTime: true,
  notifications: true
});

onMounted(() => {
  const saved = localStorage.getItem('appSettings');
  if (saved) {
    Object.assign(settings, JSON.parse(saved));
  }
});

const openPasswordModal = () => {
  passwordForm.current_password = '';
  passwordForm.new_password = '';
  passwordForm.new_password_confirmation = '';
  errorMessage.value = '';
  showPasswordModal.value = true;
};

const saveSettings = () => {
  localStorage.setItem('appSettings', JSON.stringify(settings));
};

const toggleSetting = (key) => {
  settings[key] = !settings[key];
  saveSettings();
  if (key === 'showTime') {
    window.dispatchEvent(new CustomEvent('settings-updated', { detail: settings }));
  }
};

const savePassword = async () => {
  errorMessage.value = '';
  
  if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
    errorMessage.value = 'Las contraseñas no coinciden.';
    return;
  }

  savingPassword.value = true;
  try {
    const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
    await axios.post('/api/usuario/change-password', passwordForm, { headers });
    
    // Toast confirmation
    Swal.fire({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      icon: 'success',
      title: '¡Contraseña actualizada!',
      text: 'Tu clave de acceso ha sido cambiada correctamente.',
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

    showPasswordModal.value = false;
  } catch (error) {
    console.error('Error al cambiar contraseña:', error);
    errorMessage.value = error.response?.data?.message || 'Error al actualizar contraseña. Por favor intenta de nuevo.';
  } finally {
    savingPassword.value = false;
  }
};



const settingsCardStyle = { backgroundColor: '#FFF', padding: '40px', borderRadius: '35px', boxShadow: '0 10px 40px rgba(0,0,0,0.02)' };
const sectionTitleStyle = { fontSize: '1.2rem', fontWeight: '900', color: '#1A1614', marginBottom: '35px' };
const settingRowStyle = { display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '20px 30px', backgroundColor: '#FDFCFB', borderRadius: '25px', marginBottom: '15px', border: '1px solid #FAF7F5' };
const actionRowStyle = { ...settingRowStyle, cursor: 'pointer' };
const settingLabelStyle = { fontSize: '1.05rem', fontWeight: '800', color: '#1A1614' };
const settingSubLabelStyle = { fontSize: '1rem', color: '#A0A0A0', marginTop: '4px', fontWeight: '500' };
const selectStyle = { padding: '10px 20px', borderRadius: '15px', border: '1.5px solid #EEE', backgroundColor: '#FFF', fontSize: '1rem', fontWeight: '700', outline: 'none', cursor: 'pointer' };

const switchInactiveStyle = { width: '55px', height: '30px', backgroundColor: '#DCDFE6', borderRadius: '20px', position: 'relative', cursor: 'pointer', transition: '0.3s' };
const switchActiveStyle = { ...switchInactiveStyle, backgroundColor: '#FF7A1A' };
const knobInactiveStyle = { width: '22px', height: '22px', backgroundColor: '#FFF', borderRadius: '50%', position: 'absolute', top: '4px', left: '4px', transition: '0.3s' };
const knobActiveStyle = { ...knobInactiveStyle, left: '29px' };

const modalOverlayStyle = { position: 'fixed', inset: 0, backgroundColor: 'rgba(0,0,0,0.4)', backdropFilter: 'blur(8px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 5000 };
const modalContentStyle = { backgroundColor: '#FFF', padding: '45px', borderRadius: '40px', width: '450px', boxShadow: '0 25px 50px rgba(0,0,0,0.1)' };
const modalInputStyle = { padding: '15px 20px', borderRadius: '15px', border: '1px solid #F0F0F0', backgroundColor: '#FBFBFB', fontSize: '1.1rem', outline: 'none', width: '100%', transition: '0.2s' };
const cancelButtonStyle = { flex: 1, padding: '14px 25px', backgroundColor: '#F5F5F5', color: '#666', border: 'none', borderRadius: '15px', fontWeight: '800', cursor: 'pointer' };
const savePasswordBtnStyle = { flex: 1, padding: '14px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '15px', fontWeight: '800', cursor: 'pointer', transition: '0.2s' };
const fieldGroupStyle = { display: 'flex', flexDirection: 'column', gap: '8px' };
const labelStyle = { fontSize: '0.9rem', fontWeight: '700', color: '#1A1614' };
</script>
