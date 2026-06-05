<template>
  <div :style="{ display: 'flex', alignItems: 'center', gap: '10px' }">
    <img 
      src="@/assets/logo.png" 
      alt="Mango Music" 
      :style="{ width: imgSize, height: 'auto' }" 
    />
    <span :style="{ 
      fontWeight: '800', 
      fontSize: fontSize, 
      color: '#1A1614',
      fontFamily: 'Poppins, sans-serif'
    }">
      Mango Music
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'medium'
  }
});

const imgSize = computed(() => {
  return props.size === 'small' ? '30px' : props.size === 'large' ? '80px' : '45px';
});

const fontSize = computed(() => {
  return props.size === 'small' ? '0.9rem' : props.size === 'large' ? '2.5rem' : '1.25rem';
});
</script>
