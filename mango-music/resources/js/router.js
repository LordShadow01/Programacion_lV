import { createRouter, createWebHistory } from 'vue-router';
import LandingPage from './pages/LandingPage.vue';
import Register from './pages/Register.vue';
import Login from './pages/Login.vue';
import ForgotPassword from './pages/ForgotPassword.vue';
import Dashboard from './pages/Dashboard.vue';
import Profile from './pages/Profile.vue';
import Publish from './pages/Publish.vue';
import Library from './pages/Library.vue';
import Monetization from './pages/Monetization.vue';
import Settings from './pages/Settings.vue';
import Eventos from './pages/Eventos.vue';
import MonetizacionPublico from './pages/MonetizacionPublico.vue';
import ArtistProfile from './pages/ArtistProfile.vue';
import GlobalTrends from './pages/GlobalTrends.vue';
import AllEvents from './pages/AllEvents.vue';
import ColaboracionesProductor from './pages/ColaboracionesProductor.vue';
import ConvocatoriasDisponibles from './pages/ConvocatoriasDisponibles.vue';

const routes = [
  { path: '/', component: LandingPage },
  { path: '/registro', component: Register },
  { path: '/login', component: Login },
  { path: '/forgot-password', component: ForgotPassword },
  { path: '/dashboard', component: Dashboard },
  { path: '/perfil', component: Profile },
  { path: '/artista/:id', component: ArtistProfile },
  { path: '/publicar', component: Publish },
  { path: '/biblioteca', component: Library },
  { path: '/monetizacion', component: Monetization },
  { path: '/configuracion', component: Settings },
  { path: '/eventos', component: Eventos },
  { path: '/eventos/todos', component: AllEvents },
  { path: '/apoyar-artistas', component: MonetizacionPublico },
  { path: '/tendencias', component: GlobalTrends },
  { path: '/productor/colaboraciones', component: ColaboracionesProductor },
  { path: '/artista/colaboraciones', component: ConvocatoriasDisponibles },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
