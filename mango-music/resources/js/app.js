import './bootstrap';
import { createApp } from 'vue';
import ArtistasComponent from './components/ArtistasComponent.vue';
import CancionesComponent from './components/CancionesComponent.vue';

const app = createApp({});

app.component('artistas-component', ArtistasComponent);
app.component('canciones-component', CancionesComponent);

app.mount('#app');
