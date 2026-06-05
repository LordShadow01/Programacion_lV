<template>
  <main :style="{ flex: 1, padding: '40px', paddingBottom: '120px' }">
      <div :style="monetizationContainerStyle">
        <!-- Header -->
        <div :style="{ marginBottom: '40px' }">
          <h2 :style="{ fontSize: '2.2rem', fontWeight: '900', color: '#1A1614', letterSpacing: '-0.5px' }">Monetización</h2>
          <p :style="{ color: '#A0A0A0', fontSize: '1.1rem', marginTop: '5px' }">Gestiona tus ingresos y el apoyo de tu comunidad.</p>
        </div>
        <!-- MAIN BALANCE CARD -->
        <div :style="mainBalanceCardStyle">
          <div :style="{ flex: 1 }">
            <div :style="{ color: 'rgba(255,255,255,0.7)', fontSize: '1rem', fontWeight: '700', textTransform: 'uppercase', marginBottom: '10px' }">Saldo Total Recaudado</div>
            <div :style="{ color: '#FFF', fontSize: '3.5rem', fontWeight: '900', letterSpacing: '-2px' }">${{ saldoTotalRecaudado.toFixed(2) }}</div>
          </div>
          <button @click="showWithdrawModal = true" :style="withdrawButtonStyle">Retirar Fondos</button>
        </div>

        <!-- REVENUE STREAMS -->
        <div :style="{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '30px', marginTop: '30px' }">
          <div :style="streamCardStyle">
            <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }">
              <div :style="iconBoxStyle('#E5F2FF')"><Users :size="24" :style="{ color: '#007AFF' }" /></div>
              <span :style="trendBadgeStyle(true)">+12%</span>
            </div>
            <div :style="{ color: '#A0A0A0', fontSize: '1rem', fontWeight: '700' }">SUSCRIPCIONES DE FANS</div>
            <div :style="{ color: '#1A1614', fontSize: '2rem', fontWeight: '900' }">${{ suscripciones.toFixed(2) }}</div>
          </div>
          <div :style="streamCardStyle">
            <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }">
              <div :style="iconBoxStyle('#FFF0E5')"><Heart :size="24" :style="{ color: '#FF7A1A' }" /></div>
              <span :style="trendBadgeStyle(true)">+5%</span>
            </div>
            <div :style="{ color: '#A0A0A0', fontSize: '1rem', fontWeight: '700' }">DONACIONES</div>
            <div :style="{ color: '#1A1614', fontSize: '2rem', fontWeight: '900' }">${{ donaciones.toFixed(2) }}</div>
          </div>
        </div>

        <!-- HISTORIAL DE APOYOS -->
        <div :style="{ marginTop: '50px' }">
          <h3 :style="{ fontSize: '1.5rem', fontWeight: '800', color: '#1A1614', marginBottom: '20px' }">Historial de Apoyos Recibidos</h3>
          
          <div v-if="transacciones.length === 0" :style="emptyStateStyle">
            <Heart :size="40" :style="{ color: '#FF7A1A', marginBottom: '15px' }" />
            <p :style="{ fontSize: '1.1rem', color: '#666', fontWeight: '600' }">Aún no has recibido apoyos, ¡comparte tu música para empezar!</p>
          </div>
          
          <div v-else :style="tableContainerStyle">
            <table :style="tableStyle">
              <thead>
                <tr>
                  <th :style="thStyle">Fecha</th>
                  <th :style="thStyle">Remitente</th>
                  <th :style="thStyle">Tipo</th>
                  <th :style="thStyle">Monto</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tx in transacciones" :key="tx.id" :style="trStyle">
                  <td :style="tdStyle">{{ tx.fecha }}</td>
                  <td :style="tdStyle">{{ tx.remitente }}</td>
                  <td :style="tdStyle">
                    <span :style="badgeStyle(tx.tipo)">{{ tx.tipo }}</span>
                  </td>
                  <td :style="{ ...tdStyle, fontWeight: '800', color: '#1A1614' }">${{ tx.monto.toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>>

      <!-- WITHDRAW FUNDS MODAL -->
      <div v-if="showWithdrawModal" :style="modalOverlayStyle" @click.self="showWithdrawModal = false">
        <div :style="modalContentStyle">
          <button @click="showWithdrawModal = false" :style="{ position: 'absolute', top: '25px', right: '25px', border: 'none', background: 'none', fontSize: '1.5rem', cursor: 'pointer', color: '#A0A0A0', fontWeight: '800' }">&times;</button>
          
          <h3 :style="{ fontSize: '1.8rem', fontWeight: '900', color: '#1A1614', marginBottom: '10px' }">Retirar Fondos</h3>
          <p :style="{ color: '#A0A0A0', fontSize: '1rem', marginBottom: '30px' }">Transfiere tus ingresos acumulados de forma segura.</p>

          <!-- Amount Selection Card -->
          <div :style="amountSelectionCardStyle">
            <div :style="{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '15px' }">
              <span :style="{ fontWeight: '800', color: '#666', fontSize: '0.95rem' }">MONTO A RETIRAR (USD)</span>
              <button @click="setAmountToMax" :style="maxAmountButtonStyle">Retirar Todo</button>
            </div>
            <div :style="{ display: 'flex', alignItems: 'center', gap: '10px' }">
              <span :style="{ fontSize: '2.5rem', fontWeight: '900', color: '#1A1614' }">$</span>
              <input 
                v-model="withdrawAmount" 
                type="number" 
                placeholder="0.00" 
                :max="totalBalance"
                step="0.01"
                min="1"
                :style="amountInputStyle" 
              />
            </div>
            <div :style="{ fontSize: '0.9rem', color: '#A0A0A0', fontWeight: '700', marginTop: '10px' }">
              Saldo Disponible: <span :style="{ color: '#FF7A1A' }">${{ totalBalance.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Transfer Method Selection -->
          <div :style="{ marginBottom: '25px' }">
            <label :style="{ ...fieldLabelStyle, marginBottom: '10px' }">MÉTODO DE TRANSFERENCIA</label>
            <div :style="{ display: 'flex', gap: '15px' }">
              <div 
                @click="withdrawMethod = 'card'"
                :style="{
                  flex: 1,
                  padding: '20px',
                  borderRadius: '16px',
                  border: withdrawMethod === 'card' ? '2.5px solid #FF7A1A' : '1.5px solid #EEE',
                  backgroundColor: withdrawMethod === 'card' ? '#FFF9F5' : '#FFF',
                  cursor: 'pointer',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '12px',
                  transition: '0.2s'
                }"
              >
                <div :style="withdrawMethod === 'card' ? radioCheckedStyle : radioUncheckedStyle"></div>
                <div>
                  <div :style="{ fontWeight: '800', color: '#1A1614', fontSize: '1rem' }">Cuenta Bancaria</div>
                  <div :style="{ fontSize: '0.85rem', color: '#A0A0A0', fontWeight: '600' }">2-3 días hábiles</div>
                </div>
              </div>

              <div 
                @click="withdrawMethod = 'wallet'"
                :style="{
                  flex: 1,
                  padding: '20px',
                  borderRadius: '16px',
                  border: withdrawMethod === 'wallet' ? '2.5px solid #FF7A1A' : '1.5px solid #EEE',
                  backgroundColor: withdrawMethod === 'wallet' ? '#FFF9F5' : '#FFF',
                  cursor: 'pointer',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '12px',
                  transition: '0.2s'
                }"
              >
                <div :style="withdrawMethod === 'wallet' ? radioCheckedStyle : radioUncheckedStyle"></div>
                <div>
                  <div :style="{ fontWeight: '800', color: '#1A1614', fontSize: '1rem' }">Billetera Digital</div>
                  <div :style="{ fontSize: '0.85rem', color: '#A0A0A0', fontWeight: '600' }">Inmediato</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card/Account details -->
          <div v-if="withdrawMethod === 'card'" :style="{ display: 'flex', flexDirection: 'column', gap: '15px', marginBottom: '10px' }">
            <div>
              <label :style="{ ...fieldLabelStyle, marginBottom: '8px' }">NÚMERO DE CUENTA / TARJETA</label>
              <div :style="inputIconWrapperStyle">
                <CreditCard :size="18" :style="{ color: '#FF7A1A' }" />
                <input v-model="cardNumber" type="text" placeholder="1234 5678 1234 5678" :style="transparentInputStyle" />
              </div>
            </div>

            <div :style="{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px' }">
              <div>
                <label :style="{ ...fieldLabelStyle, marginBottom: '8px' }">NOMBRE DEL BANCO</label>
                <input v-model="bankName" type="text" placeholder="Ej: Banco Agrícola" :style="basicInputStyle" />
              </div>
              <div :style="{ position: 'relative' }">
                <label :style="{ ...fieldLabelStyle, marginBottom: '8px', display: 'flex', alignItems: 'center', gap: '5px' }">
                  CVV / CÓDIGO
                  <HelpCircle :size="14" @mouseenter="showCvvTooltip = true" @mouseleave="showCvvTooltip = false" :style="{ cursor: 'pointer', color: '#FF7A1A' }" />
                </label>
                <input v-model="cardCvv" type="password" placeholder="•••" maxlength="4" :style="basicInputStyle" />
                <div v-if="showCvvTooltip" :style="tooltipBubbleStyle">
                  Los 3 o 4 dígitos de seguridad al reverso de tu tarjeta.
                </div>
              </div>
            </div>
          </div>

          <!-- Digital Wallet Details -->
          <div v-else :style="{ display: 'flex', flexDirection: 'column', gap: '15px', marginBottom: '10px' }">
            <div>
              <label :style="{ ...fieldLabelStyle, marginBottom: '8px' }">CORREO DE LA BILLETERA (PAYPAL / BILLETERA DIGITAL)</label>
              <div :style="inputIconWrapperStyle">
                <Mail :size="18" :style="{ color: '#FF7A1A' }" />
                <input v-model="cardNumber" type="email" placeholder="tu-correo@paypal.com" :style="transparentInputStyle" />
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <button @click="confirmWithdrawal" :disabled="isProcessing" :style="confirmWithdrawButtonStyle">
            <span v-if="!isProcessing">Confirmar Retiro</span>
            <div v-else :style="{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '10px' }">
              <div :style="{ width: '20px', height: '20px', border: '3px solid #FFF', borderTop: '3px solid transparent', borderRadius: '50%', animation: 'spin 1s linear infinite' }"></div>
              <span>Procesando...</span>
            </div>
          </button>
        </div>
      </div>
    </main>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Users, Heart, CreditCard, Lock, HelpCircle, Mail } from 'lucide-vue-next';
import axios from 'axios';
import Swal from 'sweetalert2';

const totalBalance = ref(0);
const saldoTotalRecaudado = ref(0);
const suscripciones = ref(0);
const donaciones = ref(0);
const transacciones = ref([]);

const showWithdrawModal = ref(false);
const showCvvTooltip = ref(false);
const isProcessing = ref(false);
const withdrawAmount = ref('');
const withdrawMethod = ref('card');

const cardNumber = ref('');
const bankName = ref('');
const cardExpiry = ref('');
const cardCvv = ref('');

const loadBalance = async () => {
  try {
    const res = await axios.get('/api/artista/monetizacion', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });
    totalBalance.value = parseFloat(res.data.saldo_disponible);
    saldoTotalRecaudado.value = parseFloat(res.data.saldo_total);
    suscripciones.value = parseFloat(res.data.suscripciones);
    donaciones.value = parseFloat(res.data.donaciones);
    transacciones.value = res.data.transacciones;
  } catch (error) {
    console.error('Error al cargar datos de monetización:', error);
  }
};

onMounted(() => {
  loadBalance();
});

const setAmountToMax = () => {
  withdrawAmount.value = totalBalance.value.toFixed(2);
};

const confirmWithdrawal = async () => {
  const amount = parseFloat(withdrawAmount.value);
  if (!amount || amount <= 0) {
    Swal.fire('Error', 'Ingresa un monto válido para retirar.', 'warning');
    return;
  }
  if (amount > totalBalance.value) {
    Swal.fire('Error', 'No tienes suficiente saldo para realizar este retiro.', 'error');
    return;
  }

  isProcessing.value = true;
  try {
    const response = await axios.post('/api/monetizacion/retirar', {
      monto: amount,
      metodo: withdrawMethod.value === 'card' ? 'Cuenta Bancaria' : 'Billetera Digital',
      detalles: {
        cuenta: cardNumber.value,
        banco: bankName.value
      }
    }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });

    totalBalance.value = parseFloat(response.data.nuevo_saldo);
    showWithdrawModal.value = false;
    withdrawAmount.value = '';
    cardNumber.value = '';
    bankName.value = '';
    cardCvv.value = '';
    
    Swal.fire({
      icon: 'success',
      title: '¡Retiro Exitoso!',
      text: `Se han retirado $${amount.toFixed(2)} con éxito.`,
      timer: 2000,
      showConfirmButton: false
    });
    
    // Recargar datos para actualizar saldo disponible y transacciones si hubiese cambios
    loadBalance();
  } catch (error) {
    console.error('Error al retirar fondos:', error);
    const msg = error.response?.data?.error || 'No se pudo procesar tu retiro.';
    Swal.fire('Error', msg, 'error');
  } finally {
    isProcessing.value = false;
  }
};

// STYLES
const monetizationContainerStyle = { backgroundColor: '#FFF', padding: '60px', borderRadius: '50px', boxShadow: '0 20px 60px rgba(0,0,0,0.02)', minHeight: '85vh' };
const mainBalanceCardStyle = { background: 'linear-gradient(135deg, #1A1614 0%, #332D2A 100%)', padding: '50px', borderRadius: '40px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', color: '#FFF' };
const withdrawButtonStyle = { padding: '20px 40px', backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', borderRadius: '20px', fontSize: '1.1rem', fontWeight: '900', cursor: 'pointer' };
const streamCardStyle = { backgroundColor: '#FFF', padding: '35px', borderRadius: '35px', border: '1px solid #F0F0F0' };
const iconBoxStyle = (bg) => ({ width: '56px', height: '56px', backgroundColor: bg, borderRadius: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center' });
const trendBadgeStyle = (pos) => ({ padding: '6px 12px', backgroundColor: pos ? 'rgba(52,199,89,0.1)' : 'rgba(255,59,48,0.1)', color: pos ? '#34C759' : '#FF3B30', borderRadius: '10px', fontSize: '1rem', fontWeight: '800' });
const modalOverlayStyle = { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0,0,0,0.7)', backdropFilter: 'blur(10px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 5000 };
const modalContentStyle = { backgroundColor: '#FFF', padding: '50px', borderRadius: '30px', width: '680px', boxShadow: '0 30px 60px rgba(0,0,0,0.3)', position: 'relative' };
const amountSelectionCardStyle = { backgroundColor: '#F9F9F9', border: '1px solid #EEE', borderRadius: '20px', padding: '25px', marginBottom: '25px' };
const maxAmountButtonStyle = { backgroundColor: '#FF7A1A', color: '#FFF', border: 'none', padding: '6px 15px', borderRadius: '10px', fontSize: '1rem', fontWeight: '800', cursor: 'pointer' };
const amountInputStyle = { border: 'none', outline: 'none', backgroundColor: 'transparent', fontSize: '2.5rem', fontWeight: '900', color: '#1A1614', width: '100%' };
const paymentBoxStyle = { border: '1.5px solid #EEE', borderRadius: '15px', overflow: 'hidden' };
const logoBoxStyle = { width: '50px', height: '32px', border: '1px solid #E0E0E0', borderRadius: '6px', backgroundColor: '#F9F9F9', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '4px' };
const fieldLabelStyle = { display: 'block', fontSize: '1rem', fontWeight: '900', color: '#A0A0A0', letterSpacing: '0.5px' };
const inputIconWrapperStyle = { display: 'flex', alignItems: 'center', gap: '12px', border: '1.5px solid #BCBCBC', borderRadius: '10px', padding: '12px 15px' };
const transparentInputStyle = { border: 'none', outline: 'none', fontSize: '1rem', width: '100%', color: '#000', fontWeight: '600' };
const basicInputStyle = { width: '100%', padding: '12px 15px', borderRadius: '10px', border: '1.5px solid #BCBCBC', fontSize: '1rem', outline: 'none', fontWeight: '600' };
const radioCheckedStyle = { width: '22px', height: '22px', borderRadius: '50%', border: '6px solid #FF7A1A', backgroundColor: '#FFF' };
const radioUncheckedStyle = { width: '22px', height: '22px', borderRadius: '50%', border: '2px solid #BCBCBC' };
const confirmWithdrawButtonStyle = { width: '100%', padding: '20px', backgroundColor: '#1A1614', color: '#FFF', border: 'none', borderRadius: '15px', fontSize: '1.1rem', fontWeight: '900', cursor: 'pointer', marginTop: '20px' };
const tooltipBubbleStyle = { position: 'absolute', bottom: '120%', right: '0', width: '280px', backgroundColor: '#FFF', color: '#000', padding: '15px 20px', borderRadius: '15px', boxShadow: '0 10px 30px rgba(0,0,0,0.15)', fontSize: '1rem', fontWeight: '500', border: '1px solid #EEE', zIndex: 100 };

// Table specific styles
const emptyStateStyle = { display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '40px', borderRadius: '25px', border: '2px dashed #EAEAEA', backgroundColor: '#FAFAFA', textAlign: 'center' };
const tableContainerStyle = { overflowX: 'auto', borderRadius: '20px', border: '1px solid #F0F0F0', boxShadow: '0 4px 15px rgba(0,0,0,0.01)' };
const tableStyle = { width: '100%', borderCollapse: 'collapse', textAlign: 'left' };
const thStyle = { backgroundColor: '#FAF9F8', padding: '16px 24px', fontSize: '0.95rem', fontWeight: '800', color: '#A0A0A0', borderBottom: '1px solid #F0F0F0', textTransform: 'uppercase', letterSpacing: '0.5px' };
const trStyle = { transition: 'background-color 0.2s', borderBottom: '1px solid #F9F9F9' };
const tdStyle = { padding: '18px 24px', fontSize: '1rem', color: '#333', fontWeight: '500' };
const badgeStyle = (tipo) => {
  const isSuscripcion = tipo.toLowerCase() === 'suscripción' || tipo.toLowerCase() === 'suscripcion';
  return {
    padding: '6px 12px',
    borderRadius: '20px',
    fontSize: '0.85rem',
    fontWeight: '700',
    backgroundColor: isSuscripcion ? '#E5F2FF' : '#FFF0E5',
    color: isSuscripcion ? '#007AFF' : '#FF7A1A',
    display: 'inline-block'
  };
};
</script>

<style>
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
