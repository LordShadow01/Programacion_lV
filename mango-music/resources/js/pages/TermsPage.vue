<template>
  <div :style="{ backgroundColor: '#FFF5E9', minHeight: '100vh', padding: '40px' }">
    <header :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '40px' }">
      <Logo />
      <button @click="$emit('back')" :style="{ color: '#FF7A1A', background: '#FFF0E0', padding: '10px 25px', borderRadius: '10px', fontWeight: '700' }">
        ← Regresar
      </button>
    </header>
    <div :style="{ display: 'flex', justifyContent: 'center' }">
      <div :style="{ backgroundColor: '#fff', padding: '60px', borderRadius: '30px', maxWidth: '900px', width: '100%', boxShadow: '0 10px 40px rgba(0,0,0,0.03)' }">
        <div :style="{ textAlign: 'center', marginBottom: '50px' }">
          <img src="@/assets/logo.png" alt="Logo" :style="{ width: '80px', marginBottom: '20px' }" />
          <h2 :style="{ fontSize: '2.5rem', fontWeight: '800', color: '#1A1614' }">Términos y Condiciones</h2>
          <p :style="{ color: '#666', marginTop: '15px', fontSize: '1.1rem' }">Bienvenido a Mango Music. Al usar nuestros servicios aceptas estos términos.</p>
        </div>
        
        <div :style="{ display: 'flex', flexDirection: 'column', gap: '25px' }">
          <div v-for="term in terms" :key="term.number" :style="termItemStyle">
            <h3 :style="{ fontSize: '1.1rem', fontWeight: '700', color: '#FF7A1A', marginBottom: '10px' }">{{ term.number }}. {{ term.title }}</h3>
            <p :style="{ fontSize: '1rem', color: '#666', lineHeight: '1.6' }">{{ term.content }}</p>
          </div>
        </div>

        <div :style="{ marginTop: '50px', textAlign: 'center' }">
          <div :style="{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '15px', marginBottom: '30px' }">
            <input 
              type="checkbox" 
              v-model="accepted"
              :style="{ width: '22px', height: '22px', accentColor: '#FF7A1A', cursor: 'pointer' }" 
            />
            <span :style="{ fontSize: '1rem', color: '#666' }">Por favor, lee y acepta los Términos para continuar.</span>
          </div>
          <button 
            @click="$emit('accept')" 
            :disabled="!accepted"
            :style="{ 
              backgroundColor: accepted ? '#FF7A1A' : '#CCC', 
              color: '#fff', 
              padding: '20px', 
              borderRadius: '12px', 
              fontWeight: '700', 
              width: '100%', 
              fontSize: '1.1rem',
              cursor: accepted ? 'pointer' : 'not-allowed',
              transition: '0.3s'
            }"
          >
            Acepto y Continuar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Logo from '@/components/Logo.vue';

const accepted = ref(false);

const terms = [
  { number: '1', title: 'Licencia de Usuario', content: 'Mango Music te otorga una licencia personal, no exclusiva, para acceder y usar la plataforma con fines no comerciales.' },
  { number: '2', title: 'Contenido', content: 'Eres responsable del contenido que publicas. Debes tener los derechos o licencias necesarias para cualquier material que subas.' },
  { number: '3', title: 'Donaciones y Suscripciones', content: 'Los pagos realizados a artistas o suscripciones son definitivos y no reembolsables salvo error técnico comprobado.' },
  { number: '4', title: 'Privacidad y Datos', content: 'Recopilamos datos necesarios para el funcionamiento. No vendemos tu información a terceros.' }
];

const termItemStyle = {
  backgroundColor: '#FFFBF5',
  padding: '25px',
  borderRadius: '15px',
  borderLeft: '5px solid #FF7A1A'
};

defineEmits(['accept', 'back']);
</script>
