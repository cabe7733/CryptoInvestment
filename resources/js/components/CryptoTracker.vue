<template>
  <div class="crypto-tracker">
    <header class="header">
      <div class="container">
        <h1 class="title">
          <i class="fas fa-chart-line"></i>
          CryptoInvestment Tracker
        </h1>
        <p class="subtitle">Seguimiento en tiempo real de criptomonedas</p>
      </div>
    </header>

    <div class="container">

      <CryptoSearch
        @crypto-selected="handleCryptoSelected"
        :is-loading="isLoading"
      />


      <div v-if="trackedCryptos.length === 0" class="empty-state">
        <i class="fas fa-search fa-3x"></i>
        <h3>No hay criptomonedas en seguimiento</h3>
        <p>Busca y agrega criptomonedas para comenzar a rastrear sus precios</p>
      </div>


      <div v-else class="crypto-grid">
        <CryptoCard
          v-for="crypto in trackedCryptos"
          :key="crypto.id"
          :crypto="crypto"
          @remove="removeCrypto"
          @show-chart="showChart"
        />
      </div>


      <PriceChart
        v-if="selectedCrypto"
        :crypto="selectedCrypto"
        :is-visible="showChartModal"
        @close="closeChart"
      />


      <div class="update-indicator" :class="{ active: isUpdating }">
        <i class="fas fa-sync-alt" :class="{ spinning: isUpdating }"></i>
        <span v-if="lastUpdate">
          Última actualización: {{ formatTime(lastUpdate) }}
        </span>
        <span v-else>Actualizando...</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import CryptoSearch from './CryptoSearch.vue';
import CryptoCard from './CryptoCard.vue';
import PriceChart from './PriceChart.vue';
import { cryptoApi } from '../services/api';

export default {
  name: 'CryptoTracker',
  components: {
    CryptoSearch,
    CryptoCard,
    PriceChart,
  },
  setup() {
    const trackedCryptos = ref([]);
    const selectedCrypto = ref(null);
    const showChartModal = ref(false);
    const isLoading = ref(false);
    const isUpdating = ref(false);
    const lastUpdate = ref(null);
    const updateInterval = ref(null);

    const loadTrackedCryptos = async () => {
      try {
        isLoading.value = true;
        const response = await cryptoApi.getTrackedCryptos();
        trackedCryptos.value = response.data;
      } catch (error) {
        console.error('Error loading tracked cryptos:', error);
      } finally {
        isLoading.value = false;
      }
    };

    const updatePrices = async () => {
      if (trackedCryptos.value.length === 0) return;

      try {
        isUpdating.value = true;
        const response = await cryptoApi.getPrices();

        response.data.forEach(priceData => {
          const crypto = trackedCryptos.value.find(c => c.id === priceData.id);
          if (crypto) {
            Object.assign(crypto, priceData);
          }
        });

        lastUpdate.value = new Date();
      } catch (error) {
        console.error('Error updating prices:', error);
      } finally {
        isUpdating.value = false;
      }
    };

    const handleCryptoSelected = async (crypto) => {
      try {
        isLoading.value = true;
        await cryptoApi.trackCrypto({
          coin_id: crypto.id.toString(),
          symbol: crypto.symbol,
          name: crypto.name,
        });

        await loadTrackedCryptos();
        await updatePrices();
      } catch (error) {
        console.error('Error tracking crypto:', error);
        alert('Error al agregar la criptomoneda');
      } finally {
        isLoading.value = false;
      }
    };

    const removeCrypto = async (cryptoId) => {
      if (!confirm('¿Deseas eliminar esta criptomoneda del seguimiento?')) {
        return;
      }

      try {
        await cryptoApi.removeCrypto(cryptoId);
        trackedCryptos.value = trackedCryptos.value.filter(c => c.id !== cryptoId);
      } catch (error) {
        console.error('Error removing crypto:', error);
        alert('Error al eliminar la criptomoneda');
      }
    };

    const showChart = (crypto) => {
      selectedCrypto.value = crypto;
      showChartModal.value = true;
    };

    const closeChart = () => {
      showChartModal.value = false;
      selectedCrypto.value = null;
    };

    const formatTime = (date) => {
      return new Date(date).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
      });
    };

    const startAutoUpdate = () => {
      updateInterval.value = setInterval(() => {
        updatePrices();
      }, 30000);
    };

    const stopAutoUpdate = () => {
      if (updateInterval.value) {
        clearInterval(updateInterval.value);
        updateInterval.value = null;
      }
    };

    onMounted(async () => {
      await loadTrackedCryptos();
      await updatePrices();
      startAutoUpdate();
    });

    onUnmounted(() => {
      stopAutoUpdate();
    });

    return {
      trackedCryptos,
      selectedCrypto,
      showChartModal,
      isLoading,
      isUpdating,
      lastUpdate,
      handleCryptoSelected,
      removeCrypto,
      showChart,
      closeChart,
      formatTime,
    };
  },
};
</script>

<style scoped>
.crypto-tracker {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding-bottom: 2rem;
}

.header {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  padding: 2rem 0;
  margin-bottom: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.title {
  color: white;
  font-size: 2.5rem;
  margin: 0;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1.1rem;
  margin: 0.5rem 0 0 0;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.empty-state i {
  color: #667eea;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #333;
  margin: 1rem 0;
}

.empty-state p {
  color: #666;
}

.crypto-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.update-indicator {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: white;
  padding: 1rem 1.5rem;
  border-radius: 50px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9rem;
  color: #666;
  transition: all 0.3s ease;
}

.update-indicator.active {
  background: #667eea;
  color: white;
}

.update-indicator i {
  font-size: 1.2rem;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .title {
    font-size: 1.8rem;
  }

  .crypto-grid {
    grid-template-columns: 1fr;
  }

  .update-indicator {
    bottom: 1rem;
    right: 1rem;
    padding: 0.75rem 1rem;
    font-size: 0.8rem;
  }
}
</style>
