<template>
  <div :style="pageContainerStyle">
    <div :style="blob1Style"></div>
    <div :style="blob2Style"></div>
    
    <nav :style="navStyle">
      <Logo size="medium" />
      <div :style="navLinksWrapperStyle">
        <a href="#inicio" 
           @click.prevent="scrollTo('inicio')" 
           class="nav-link-hover" 
           :style="navLinkStyle">Inicio</a>
        <a href="#eventos" 
           @click.prevent="scrollTo('eventos')" 
           class="nav-link-hover" 
           :style="navLinkStyle">Eventos</a>
        <a href="#sobre-nosotros" 
           @click.prevent="scrollTo('sobre-nosotros')" 
           class="nav-link-hover" 
           :style="navLinkStyle">Sobre Nosotros</a>
      </div>
      <div :style="navButtonContainerStyle">
        <router-link to="/login" class="nav-link-hover" :style="navLoginBtnStyle">
          Ingresar
        </router-link>
        <router-link to="/registro" class="nav-btn-hover" :style="navRegisterBtnStyle">
          Registrarse
        </router-link>
      </div>
    </nav>

    <section id="inicio" :style="heroSectionStyle">
      <div :style="heroContentWrapperStyle">
        <h1 :style="heroTitleStyle">
          Tu música,
          <br />
          <span :style="textGradientStyle">Tu Comunidad.</span>
        </h1>
        <p :style="heroSubTitleStyle">
          La plataforma para artistas independientes
          que quieren compartir su música con el mundo.
        </p>
        <!-- Botón Registrarse movido a la barra de navegación superior -->

        <div :style="statsWrapperStyle">
          <div v-for="stat in stats" :key="stat.label">
            <div :style="statValueStyle">{{ stat.value }}</div>
            <div :style="statLabelStyle">{{ stat.label }}</div>
          </div>
        </div>
      </div>
      <div :style="heroImageContainerStyle">
        <div :style="heroImageGlowStyle"></div>
        <div :style="carouselWrapperStyle" class="bg-transparent border-none shadow-none">
          <div :style="carouselSlidesContainerStyle" class="bg-transparent border-none shadow-none">
            <div v-for="(slide, index) in carouselSlides"
                 :key="slide"
                 :style="getSlideStyle(index)"
                 class="bg-transparent border-none shadow-none">
              <div class="overflow-hidden rounded-2xl border border-gray-300/40 bg-[#1A1614] p-1.5 shadow-2xl">
                <img :src="slide" 
                     alt="Mango Music Dashboard" 
                     :style="slideImageStyle"
                     class="w-full h-full object-contain rounded-xl" />
              </div>
            </div>
          </div>
          <!-- indicadores ocultos: se mantienen funcionales pero sin display -->
        </div>
      </div>
    </section>

    <section id="eventos" :style="sectionStyle">
      <div :style="sectionHeaderStyle">
        <h2 :style="sectionTitleStyle">
          Proximos <span :style="mangoColorStyle">Eventos</span>
        </h2>
        <div :style="sectionDividerStyle"></div>
      </div>
      
      <div :style="eventsGridStyle">
        <div v-for="event in events" 
             :key="event.id" 
             class="event-card-premium" 
             :style="eventCardStyle">
          <div :style="eventImageStyle(event.imagen || event.image || 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800&q=80')">
            <div :style="eventTagStyle">{{ event.fecha || event.date }}</div>
          </div>
          <div :style="eventBodyStyle">
            <h3 :style="eventTitleStyle">{{ event.nombre || event.title }}</h3>
            <p :style="eventDescStyle">{{ event.descripcion || event.desc }}</p>
            <div :style="eventLocationWrapperStyle">
              <div class="icon-circle"><MapPin :size="18" /></div>
              {{ event.lugar || event.location }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="sobre-nosotros" :style="aboutSectionWrapperStyle">
      <div :style="aboutContainerStyle">
        <div :style="aboutContentStyle">
          <h2 :style="aboutSectionTitleStyle">
            Revolucionando la <br />
            <span :style="textGradientStyle">Industria Musical</span>
          </h2>
          <p :style="aboutTextStyle">
            En Mango Music creemos que el talento no deberia tener barreras. 
            Creamos un hogar para la creatividad.
          </p>
          <div :style="featuresGridStyle">
            <div v-for="feature in features" 
                 :key="feature.title" 
                 class="feature-item" 
                 :style="featureItemStyle">
              <div class="feature-icon-box" :style="featureIconStyle">
                <component :is="feature.icon" :size="28" />
              </div>
              <div>
                <h4 :style="featureTitleStyle">{{ feature.title }}</h4>
                <p :style="featureDescStyle">{{ feature.desc }}</p>
              </div>
            </div>
          </div>
        </div>
        <div :style="aboutImageWrapperStyle">
          <div :style="aboutImageDecoStyle"></div>
          <img :src="aboutImageSrc" 
               alt="Sobre Nosotros" 
               :style="aboutImageStyle" />
        </div>
      </div>
    </section>

    <footer :style="footerStyle">
      <div :style="footerContainerStyle">
        <div>
          <Logo size="medium" />
          <p :style="footerDescStyle">
            La comunidad mas grande de artistas independientes.
          </p>
        </div>
        <div :style="footerLinksWrapperStyle">
          <div v-for="col in footerLinks" :key="col.title">
            <h5 :style="footerColTitleStyle">{{ col.title }}</h5>
            <div v-for="link in col.links" 
                 :key="link" 
                 :style="footerLinkItemStyle">{{ link }}</div>
          </div>
        </div>
      </div>
      <div :style="footerBottomStyle">
        © 2024 Mango Music. Disenado para artistas.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { 
  MapPin, Music, DollarSign, 
  Users, Zap, Heart, Globe 
} from 'lucide-vue-next';
import Logo from '@/components/Logo.vue';

const stats = ref([
  { value: '0', label: 'Artistas' },
  { value: '0', label: 'Canciones' },
  { value: '0', label: 'Oyentes' }
]);

const events = ref([]);

// Carrusel de imágenes
const carouselSlides = [
  '/storage/images/dash1artista.png',
  '/storage/images/dash2convocatoria.png',
  '/storage/images/dash3apoyoartista.png',
  '/storage/images/dash4monetizacion.png',
  '/storage/images/dash5perfilartista.png'
];

const currentSlide = ref(0);
const carouselInterval = ref(null);

const startCarousel = () => {
  stopCarousel();
  carouselInterval.value = setInterval(() => {
    nextSlide();
  }, 4000);
};

const stopCarousel = () => {
  if (carouselInterval.value) {
    clearInterval(carouselInterval.value);
    carouselInterval.value = null;
  }
};

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % carouselSlides.length;
};

const prevSlide = () => {
  currentSlide.value = (currentSlide.value - 1 + carouselSlides.length) % carouselSlides.length;
};

const goToSlide = (index) => {
  currentSlide.value = index;
  startCarousel();
};

onMounted(async () => {
  startCarousel();
  try {
    const statsRes = await axios.get('/api/stats');
    stats.value[0].value = statsRes.data.artistas + '+';
    stats.value[1].value = statsRes.data.canciones + '+';
    stats.value[2].value = statsRes.data.oyentes + '+';

    // Fetch real events — /api/eventos returns a Laravel paginator object
    const eventsRes = await axios.get('/api/eventos');
    const eventsArray = Array.isArray(eventsRes.data) 
      ? eventsRes.data 
      : (eventsRes.data?.data ?? []);
    // Sort by date (closest first)
    events.value = eventsArray
      .sort((a, b) => new Date(a.fecha) - new Date(b.fecha))
      .slice(0, 3);
  } catch (error) {
    console.error('Error fetching data:', error);
  }
});

onUnmounted(() => {
  stopCarousel();
});

const scrollTo = (id) => {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: 'smooth' });
};

const aboutImageSrc = "https://images.unsplash.com/photo-1516280440614-37939bbacd81?w=1200&q=80";

const pageContainerStyle = { 
  background: 'linear-gradient(135deg, #FFF5E9 0%, #FFEFD5 100%)', 
  minHeight: '100vh', 
  color: '#1A1614', 
  fontFamily: 'Outfit, sans-serif', 
  scrollBehavior: 'smooth',
  position: 'relative',
  overflowX: 'hidden'
};

const blob1Style = { 
  position: 'absolute', top: '-100px', right: '-100px', 
  width: '600px', height: '600px', zIndex: 0,
  background: 'radial-gradient(circle, rgba(255,122,26,0.15) 0%, rgba(255,122,26,0) 70%)' 
};

const blob2Style = { 
  position: 'absolute', top: '40%', left: '-200px', 
  width: '800px', height: '800px', zIndex: 0,
  background: 'radial-gradient(circle, rgba(255,105,180,0.1) 0%, rgba(255,105,180,0) 70%)' 
};

const navStyle = { 
  display: 'grid', 
  gridTemplateColumns: '1fr auto 1fr', 
  alignItems: 'center', 
  padding: '20px 80px', 
  background: 'rgba(255,255,255,0.85)', 
  backdropFilter: 'blur(20px)', 
  borderBottom: '1px solid rgba(255,255,255,0.4)', 
  position: 'sticky', 
  top: 0, 
  zIndex: 1000 
};
const navLinksWrapperStyle = { 
  display: 'flex', 
  gap: '45px', 
  fontWeight: '700',
  justifyContent: 'center' 
};
const navLinkStyle = { 
  color: '#1A1614', textDecoration: 'none', 
  transition: '0.3s', cursor: 'pointer', fontSize: '1.1rem' 
};

const navButtonContainerStyle = {
  display: 'flex',
  justifyContent: 'flex-end',
  alignItems: 'center',
  gap: '20px'
};

const navLoginBtnStyle = {
  color: '#1A1614',
  textDecoration: 'none',
  fontWeight: '700',
  fontSize: '1.1rem',
  cursor: 'pointer',
  transition: '0.3s'
};

const navRegisterBtnStyle = {
  background: 'linear-gradient(to right, #FF7A1A, #FF4B4B)', 
  color: '#fff', 
  padding: '12px 30px', 
  borderRadius: '30px', 
  textDecoration: 'none', 
  fontWeight: '800', 
  fontSize: '1rem',
  boxShadow: '0 8px 20px rgba(255,122,26,0.25)',
  transition: 'all 0.3s ease',
  cursor: 'pointer'
};

const registerBtnStyle = { 
  background: 'linear-gradient(to right, #FF7A1A, #FF9F5A)', 
  color: '#fff', padding: '14px 35px', borderRadius: '15px', 
  textDecoration: 'none', fontWeight: '900', 
  boxShadow: '0 10px 25px rgba(255,122,26,0.3)'
};

const heroSectionStyle = { 
  display: 'grid', 
  gridTemplateColumns: '40% 60%',
  gap: '0px', 
  alignItems: 'center', 
  minHeight: '600px',
  padding: '20px 0 20px 0',
  margin: '0',
  position: 'relative',
  width: '100%',
  boxSizing: 'border-box'
};

const heroContentWrapperStyle = { 
  width: '100%',
  position: 'relative', 
  zIndex: 10,
  paddingLeft: '100px',
  paddingRight: '40px'
};
const heroTitleStyle = { 
  fontSize: '4.8rem',
  fontWeight: '900',
  lineHeight: '1.15', 
  marginBottom: '24px',
  letterSpacing: '-2px',
  color: '#1A1614',
  whiteSpace: 'nowrap'
};
const heroSubTitleStyle = { 
  fontSize: '1.15rem',
  color: '#555',
  lineHeight: '1.65',
  fontWeight: '500',
  marginBottom: '0',
  maxWidth: '480px'
};
const flexGap20Style = { display: 'flex', gap: '20px' };

const textGradientStyle = { 
  background: 'linear-gradient(to right, #FF7A1A, #FF4B4B)', 
  WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' 
};

const ctaBtnStyle = { 
  background: 'linear-gradient(to right, #FF7A1A, #FF4B4B)', 
  color: '#fff', padding: '20px 50px', borderRadius: '20px', 
  textDecoration: 'none', fontWeight: '900', fontSize: '1.2rem', 
  boxShadow: '0 15px 35px rgba(255, 122, 26, 0.4)' 
};

const loginBtnStyle = { 
  backgroundColor: '#FFF', color: '#1A1614', padding: '20px 50px', 
  borderRadius: '20px', textDecoration: 'none', fontWeight: '900', 
  fontSize: '1.2rem', boxShadow: '0 10px 30px rgba(0,0,0,0.05)', 
  border: '1px solid #EEE' 
};

const statsWrapperStyle = { 
  display: 'flex', 
  gap: '48px',
  marginTop: '64px'
};
const statValueStyle = { 
  fontSize: '2.6rem', 
  fontWeight: '800', 
  color: '#FF7A1A', 
  marginBottom: '4px',
  lineHeight: '1'
};
const statLabelStyle = { 
  color: '#999', 
  fontSize: '0.8rem', 
  fontWeight: '700', 
  textTransform: 'uppercase', 
  letterSpacing: '2px' 
};

const heroImageContainerStyle = { 
  position: 'relative', 
  display: 'flex', 
  justifyContent: 'center',
  alignItems: 'center',
  width: '100%',
  paddingRight: '40px',
  paddingLeft: '20px',
  backgroundColor: 'transparent'
};
const heroImageStyle = { 
  width: '90%', height: 'auto', position: 'relative', 
  zIndex: 2, animation: 'float 6s ease-in-out infinite' 
};
const heroImageGlowStyle = { 
  position: 'absolute', width: '500px', height: '500px', 
  background: '#FF7A1A', filter: 'blur(120px)', opacity: 0.18, 
  top: '50%', left: '50%', transform: 'translate(-50%, -50%)', zIndex: 1 
};

const carouselWrapperStyle = {
  position: 'relative',
  width: '100%',
  height: '800px',
  maxWidth: '960px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: 'transparent'
};

const carouselSlidesContainerStyle = {
  position: 'relative',
  width: '100%',
  height: '100%',
  overflow: 'hidden',
  backgroundColor: 'transparent'
};

const getSlideStyle = (index) => {
  const isActive = index === currentSlide.value;
  return {
    position: 'absolute',
    top: 0,
    left: 0,
    width: '100%',
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    transition: 'opacity 0.7s ease',
    opacity: isActive ? 1 : 0,
    zIndex: isActive ? 10 : 1,
    pointerEvents: isActive ? 'auto' : 'none'
  };
};

const slideImageStyle = {
  width: '100%',
  height: 'auto',
  objectFit: 'contain',
  background: 'transparent',
  border: 'none'
};

const indicatorsStyle = {
  position: 'absolute',
  bottom: '-45px',
  left: '50%',
  transform: 'translateX(-50%)',
  display: 'flex',
  gap: '8px',
  zIndex: 15
};

const indicatorDotStyle = (isActive) => ({
  width: isActive ? '24px' : '8px',
  height: '8px',
  borderRadius: '4px',
  backgroundColor: isActive ? '#FF7A1A' : '#CCC',
  cursor: 'pointer',
  transition: 'all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1)',
  opacity: isActive ? 1 : 0.6
});

const sectionStyle = { 
  padding: '20px 80px 150px 80px', maxWidth: '1400px', 
  margin: '0 auto', position: 'relative', zIndex: 2 
};
const sectionHeaderStyle = { textAlign: 'center', marginBottom: '80px' };
const mangoColorStyle = { color: '#FF7A1A' };
const sectionTitleStyle = { 
  fontSize: '4rem', fontWeight: '900', marginBottom: '15px', 
  textAlign: 'center', letterSpacing: '-1.5px' 
};
const sectionDividerStyle = { 
  width: '100px', height: '6px', margin: '0 auto', borderRadius: '10px',
  background: 'linear-gradient(to right, #FF7A1A, #FF4B4B)' 
};

const eventsGridStyle = { 
  display: 'grid', gap: '40px', marginTop: '80px',
  gridTemplateColumns: 'repeat(auto-fit, minmax(380px, 1fr))'
};

const eventCardStyle = { 
  backgroundColor: 'rgba(255,255,255,0.8)', backdropFilter: 'blur(10px)', 
  borderRadius: '40px', overflow: 'hidden', transition: '0.4s', 
  boxShadow: '0 25px 60px rgba(0,0,0,0.05)', 
  border: '1px solid rgba(255,255,255,0.5)' 
};

const eventImageStyle = (img) => ({ 
  width: '100%', height: '280px', backgroundImage: `url(${img})`, 
  backgroundSize: 'cover', backgroundPosition: 'center', position: 'relative' 
});

const eventTagStyle = { 
  position: 'absolute', top: '25px', right: '25px', 
  backgroundColor: '#FFF', padding: '10px 20px', borderRadius: '15px', 
  fontWeight: '900', color: '#FF7A1A', fontSize: '0.9rem', 
  boxShadow: '0 10px 20px rgba(0,0,0,0.1)' 
};

const eventBodyStyle = { padding: '35px' };
const eventTitleStyle = { 
  fontSize: '1.6rem', fontWeight: '900', 
  marginBottom: '12px', color: '#1A1614' 
};
const eventDescStyle = { 
  color: '#666', fontSize: '1rem', 
  lineHeight: '1.6', marginBottom: '25px' 
};
const eventLocationWrapperStyle = { 
  fontWeight: '800', color: '#FF7A1A', 
  display: 'flex', alignItems: 'center', gap: '10px' 
};

const aboutSectionWrapperStyle = { 
  background: '#FFF', padding: '150px 0', position: 'relative', 
  zIndex: 2, borderRadius: '100px 100px 0 0' 
};
const aboutContainerStyle = { 
  display: 'flex', alignItems: 'center', gap: '100px', 
  maxWidth: '1200px', margin: '0 auto', padding: '0 40px' 
};
const aboutContentStyle = { flex: 1.2 };
const aboutSectionTitleStyle = { 
  fontSize: '3.5rem', fontWeight: '900', marginBottom: '15px', 
  textAlign: 'left', letterSpacing: '-1.5px' 
};
const aboutTextStyle = { 
  fontSize: '1.3rem', color: '#555', lineHeight: '1.8', 
  marginBottom: '40px', fontWeight: '500' 
};
const featuresGridStyle = { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '35px' };
const featureItemStyle = { display: 'flex', gap: '20px', alignItems: 'flex-start' };
const featureIconStyle = { 
  minWidth: '55px', height: '55px', backgroundColor: '#FFF5E9', 
  borderRadius: '18px', display: 'flex', alignItems: 'center', 
  justifyContent: 'center', color: '#FF7A1A' 
};
const featureTitleStyle = { fontWeight: '900', fontSize: '1.1rem', marginBottom: '5px' };
const featureDescStyle = { fontSize: '0.9rem', color: '#666', lineHeight: '1.4' };

const aboutImageWrapperStyle = { flex: 1, position: 'relative' };
const aboutImageStyle = { 
  width: '100%', borderRadius: '60px', position: 'relative', zIndex: 2,
  boxShadow: '0 40px 80px rgba(0,0,0,0.2)' 
};
const aboutImageDecoStyle = { 
  position: 'absolute', top: '-40px', right: '-40px', 
  width: '100%', height: '100%', borderRadius: '60px', opacity: 0.15,
  background: 'linear-gradient(135deg, #FF7A1A 0%, #FF4B4B 100%)' 
};

const footerStyle = { 
  padding: '120px 80px 60px', 
  backgroundColor: '#FDFCFB', borderTop: '1px solid #EEE' 
};
const footerContainerStyle = { 
  display: 'flex', justifyContent: 'space-between', 
  maxWidth: '1200px', margin: '0 auto' 
};
const footerDescStyle = { 
  color: '#888', marginTop: '20px', 
  maxWidth: '300px', lineHeight: '1.6', fontWeight: '500' 
};
const footerLinksWrapperStyle = { display: 'flex', gap: '80px' };
const footerColTitleStyle = { fontWeight: '900', marginBottom: '25px', fontSize: '1.1rem' };
const footerLinkItemStyle = { 
  color: '#666', marginBottom: '12px', 
  cursor: 'pointer', fontWeight: '600' 
};
const footerBottomStyle = { 
  marginTop: '80px', textAlign: 'center', borderTop: '1px solid #EEE', 
  paddingTop: '40px', color: '#AAA', fontSize: '0.9rem', fontWeight: '600' 
};

// Real events are now fetched in onMounted


const features = [
  { icon: Zap, title: 'Lanzamiento Flash', desc: 'Tu musica online pronto.' },
  { icon: DollarSign, title: 'Pagos Justos', desc: 'Recibe tus regalias.' },
  { icon: Heart, title: 'Fans Reales', desc: 'Conecta sin algoritmos.' },
  { icon: Globe, title: 'Alcance Global', desc: 'Tu voz en el mundo.' }
];

const footerLinks = [
  { title: 'Plataforma', links: ['Artistas', 'Eventos'] },
  { title: 'Compañia', links: ['Sobre Nosotros', 'Blog'] },
  { title: 'Soporte', links: ['Ayuda', 'Contacto'] }
];
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap');

.nav-link-hover:hover { color: #FF7A1A !important; }
.event-card-premium:hover { transform: translateY(-20px) scale(1.02); }
.icon-circle { width: 35px; height: 35px; background-color: rgba(255,122,26,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.feature-item:hover .feature-icon-box { background-color: #FF7A1A !important; color: #FFF !important; }

.carousel-arrow:hover {
  background-color: #FF7A1A !important;
  color: #FFF !important;
  transform: translateY(-50%) scale(1.1) !important;
}

.nav-btn-hover:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(255, 122, 26, 0.45) !important;
}

.indicator-dot:hover {
  background-color: #FF7A1A !important;
  opacity: 1 !important;
}

@keyframes float { 
  0%, 100% { transform: translateY(0) rotate(0deg); } 
  50% { transform: translateY(-30px) rotate(2deg); } 
}
</style>
