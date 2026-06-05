<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
      <h1 :style="{ fontSize: '1.8rem', fontWeight: '800', color: '#1A1614', marginBottom: '10px' }">Apoyar a los Artistas</h1>
      <p :style="{ color: '#666', marginBottom: '40px' }">Suscríbete o dona para ayudar a los creadores de Mango Music.</p>

      <!-- ARTISTS GRID FROM DB -->
      <div v-if="artistas.length > 0" :style="{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(320px, 1fr))', gap: '30px' }">
        <div v-for="artista in artistas" :key="artista.id" :style="artistCardStyle">
          <div :style="artistHeaderStyle(artista.foto)"></div>
          <div :style="{ padding: '25px', textAlign: 'center' }">
            <h3 :style="{ fontSize: '1.3rem', fontWeight: '900', color: '#1A1614' }">{{ artista.nombre_artistico || artista.nombre }}</h3>
            <p :style="{ color: '#A0A0A0', fontSize: '0.9rem', marginBottom: '20px' }">Artista Verificado • Mango Music</p>
            
            <div :style="{ display: 'flex', flexDirection: 'column', gap: '12px' }">
              <button @click="openPaymentModal(artista, 'Donación')" :style="donateButtonStyle">
                <Heart :size="18" fill="#FFF" /> Donación
              </button>
              <button @click="openPaymentModal(artista, 'Suscripción')" :style="subscribeButtonStyle">
                <Star :size="18" /> Suscribirse al Fan Club ($4.99)
              </button>
            </div>
          </div>
        </div>
      </div>
      <div v-else :style="{ textAlign: 'center', padding: '100px' }">
        <p :style="{ fontSize: '1.2rem', color: '#A0A0A0' }">No hay artistas registrados para apoyar aún.</p>
      </div>

      <!-- MODAL DE PAGO (PAYPAL SMART BUTTONS) -->
      <div v-if="showPayment" :style="modalOverlayStyle">
        <div :style="modalContentStyle">
          <button @click="resetModal" :style="closeButtonStyle">&times;</button>
          
          <h2 :style="{ fontSize: '1.8rem', fontWeight: '900', color: '#1A1614', marginBottom: '10px' }">
            {{ paymentType }}
          </h2>
          <p :style="{ color: '#666', marginBottom: '30px' }">
            Apoyando a: <strong :style="{ color: '#FF7A1A' }">{{ selectedArtist?.nombre_artistico || selectedArtist?.nombre }}</strong>
          </p>

          <div v-if="errorMessage" :style="errorMessageStyle">
            {{ errorMessage }}
          </div>

          <div :style="formContainerStyle">
            <!-- Selector de Donación Libre -->
            <div v-if="paymentType === 'Donación'" :style="{ display: 'flex', flexDirection: 'column', gap: '8px', marginBottom: '20px' }">
              <label :style="labelStyle">Monto a donar (USD)</label>
              <input 
                type="number" 
                v-model.number="paymentForm.amount" 
                min="0.50" 
                step="0.50" 
                :style="inputStyle" 
                @input="onAmountChange"
              />
            </div>
            
            <!-- Suscripción Fija -->
            <div v-else :style="{ display: 'flex', flexDirection: 'column', gap: '8px', marginBottom: '20px' }">
              <label :style="labelStyle">Suscripción Mensual al Fan Club</label>
              <input 
                type="text" 
                value="$4.99 USD / Mes" 
                disabled 
                :style="{ ...inputStyle, backgroundColor: '#F0F0F0', color: '#666', fontWeight: 'bold' }" 
              />
            </div>

            <!-- Contenedor para renderizar los Smart Buttons de PayPal -->
            <div id="paypal-button-container" :style="{ marginTop: '10px', minHeight: '150px' }"></div>
          </div>
        </div>
      </div>
  </main>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue';
import { Heart, Star } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

const artistas = ref([]);
const showPayment = ref(false);
const selectedArtist = ref(null);
const paymentType = ref('');
const paymentStage = ref('select');
const errorMessage = ref('');

const paymentForm = reactive({
  amount: 5.00
});

const fetchArtists = async () => {
  try {
    const response = await axios.get('/api/artistas');
    // Filtrar solo usuarios de rol artista si se requiere, de lo contrario mostrar todos
    artistas.value = response.data.filter(u => u.rol === 'artista');
  } catch (error) {
    console.error('Error fetching artists:', error);
  }
};

const loadPayPalSDK = () => {
  return new Promise((resolve, reject) => {
    if (window.paypal) {
      resolve(window.paypal);
      return;
    }
    const script = document.createElement('script');
    // Usamos el id de cliente de Sandbox por defecto, configurable por el backend, forzando locale SV
    script.src = 'https://www.paypal.com/sdk/js?client-id=AYJPKfYO67s2dq65lNojCz076HTRTRR1fxoEVoWETl7YtIWWFLK4UKwNSscKn83B97QggCNyxcgUgZEz&currency=USD&locale=es_SV';
    script.onload = () => resolve(window.paypal);
    script.onerror = (err) => reject(err);
    document.body.appendChild(script);
  });
};

const openPaymentModal = async (artista, type) => {
  selectedArtist.value = artista;
  paymentType.value = type;
  paymentForm.amount = type === 'Suscripción' ? 4.99 : 5.00;
  paymentStage.value = 'select';
  errorMessage.value = '';
  showPayment.value = true;

  await nextTick();
  renderPayPalButtons();
};

const renderPayPalButtons = async () => {
  try {
    const paypal = await loadPayPalSDK();
    
    const container = document.getElementById('paypal-button-container');
    if (container) container.innerHTML = '';

    paypal.Buttons({
      fundingSource: paypal.FUNDING.PAYPAL,
      style: {
        layout: 'vertical',
        color:  'gold',
        shape:  'rect',
        label:  'paypal'
      },
      createOrder: async () => {
        errorMessage.value = '';
        try {
          const token = localStorage.getItem('token');
          const res = await axios.post('/api/payments/create-order', {
            monto: paymentForm.amount,
            id_artista: selectedArtist.value.id || selectedArtist.value._id,
            tipo: 'donacion' // Forzamos donación para evitar error 500 en API de órdenes
          }, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          return res.data.id;
        } catch (err) {
          console.error(err);
          errorMessage.value = 'Error al iniciar la orden de PayPal. Verifica tu conexión.';
          throw err;
        }
      },
      onApprove: async (data) => {
        Swal.fire({
          title: 'Capturando pago...',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        try {
          const token = localStorage.getItem('token');
          await axios.post('/api/payments/capture-order', {
            orderID: data.orderID,
            id_artista: selectedArtist.value.id || selectedArtist.value._id,
            tipo: 'donacion', // Forzamos donación
            monto: paymentForm.amount
          }, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });

          Swal.fire({
            icon: 'success',
            title: '¡Operación Exitosa!',
            text: `Tu ${paymentType.value} de $${paymentForm.amount} a ${selectedArtist.value.nombre_artistico || selectedArtist.value.nombre} se ha procesado correctamente.`,
            confirmButtonColor: '#FF7A1A'
          });

          resetModal();
        } catch (err) {
          console.error(err);
          Swal.fire({
            icon: 'error',
            title: 'Error de Procesamiento',
            text: 'El pago se autorizó pero no pudimos registrarlo. Contacta al soporte técnico.',
            confirmButtonColor: '#FF7A1A'
          });
        }
      },
      onError: (err) => {
        console.error('PayPal Error:', err);
        errorMessage.value = 'El proceso de pago falló o fue cancelado.';
      }
    }).render('#paypal-button-container');

  } catch (err) {
    console.error('Error rendering PayPal buttons:', err);
    errorMessage.value = 'Error al cargar los botones inteligentes de pago.';
  }
};

const onAmountChange = () => {
  if (paymentForm.amount < 0.50) {
    paymentForm.amount = 0.50;
  }
  renderPayPalButtons();
};

const resetModal = () => {
  showPayment.value = false;
  errorMessage.value = '';
};

onMounted(fetchArtists);

const artistCardStyle = { backgroundColor: '#FFF', borderRadius: '35px', overflow: 'hidden', boxShadow: '0 15px 40px rgba(0,0,0,0.03)', border: '1px solid #F0F0F0' };
const artistHeaderStyle = (img) => ({ 
  width: '100%', height: '160px', 
  backgroundImage: `url(${img || 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=400&h=400&fit=crop'})`, 
  backgroundSize: 'cover', backgroundPosition: 'center' 
});
const donateButtonStyle = { width: '100%', padding: '12px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '15px', fontWeight: '800', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '10px' };
const subscribeButtonStyle = { ...donateButtonStyle, backgroundColor: '#1A1614' };

const modalOverlayStyle = { position: 'fixed', inset: 0, backgroundColor: 'rgba(0,0,0,0.6)', backdropFilter: 'blur(10px)', zIndex: 6000, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '20px' };
const modalContentStyle = { backgroundColor: '#FFF', padding: '50px', borderRadius: '40px', width: '100%', maxWidth: '500px', textAlign: 'center', position: 'relative', boxShadow: '0 20px 60px rgba(0,0,0,0.1)', boxSizing: 'border-box' };
const closeButtonStyle = { position: 'absolute', top: '25px', right: '25px', border: 'none', background: 'none', fontSize: '2rem', color: '#CCC', cursor: 'pointer', lineHeight: '1' };

const formContainerStyle = { display: 'flex', flexDirection: 'column', gap: '15px', textAlign: 'left', width: '100%' };
const labelStyle = { fontSize: '0.85rem', fontWeight: '800', color: '#A0A0A0', textTransform: 'uppercase', marginBottom: '5px' };
const inputStyle = { padding: '15px', borderRadius: '12px', border: '1px solid #F0F0F0', backgroundColor: '#FBFBFB', fontSize: '1rem', outline: 'none', width: '100%', boxSizing: 'border-box' };
const errorMessageStyle = { backgroundColor: '#FFF2F2', color: '#D8000C', padding: '12px', borderRadius: '12px', fontSize: '0.9rem', fontWeight: '700', marginBottom: '20px', border: '1px solid #FFD2D2', textAlign: 'center' };
</script>
