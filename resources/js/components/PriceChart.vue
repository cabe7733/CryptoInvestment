<template>
  <div v-if="isVisible" class="modal-overlay" @click="handleClose">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>
          <i class="fas fa-chart-line"></i>
          {{ crypto.symbol }} - Historial de Precios
        </h2>
        <button class="close-btn" @click="handleClose">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body">
        <div class="time-range-selector">
          <button
            v-for="range in timeRanges"
            :key="range.value"
            :class="['range-btn', { active: selectedRange === range.value }]"
            @click="selectRange(range.value)"
          >
            {{ range.label }}
          </button>
        </div>

        <div v-if="selectedRange === 'custom'" class="custom-date-range">
          <div class="date-input-group">
            <label>Fecha Inicio</label>
            <input type="date" v-model="startDate" @change="loadCustomRange" />
          </div>
          <div class="date-input-group">
            <label>Fecha Fin</label>
            <input type="date" v-model="endDate" @change="loadCustomRange" />
          </div>
        </div>

        <div class="chart-container">
          <canvas ref="chartCanvas"></canvas>
        </div>

        <div v-if="statistics" class="statistics">
          <div class="stat-item">
            <span class="stat-label">Precio Máximo</span>
            <span class="stat-value">${{ statistics.max.toFixed(2) }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Precio Mínimo</span>
            <span class="stat-value">${{ statistics.min.toFixed(2) }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Precio Promedio</span>
            <span class="stat-value">${{ statistics.avg.toFixed(2) }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Variación</span>
            <span class="stat-value" :class="getVariationClass(statistics.variation)">
              {{ statistics.variation > 0 ? '+' : '' }}{{ statistics.variation.toFixed(2) }}%
            </span>
          </div>
        </div>

        <div v-if="loading" class="loading-overlay">
          <i class="fas fa-spinner fa-spin fa-3x"></i>
          <p>Cargando datos históricos...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { cryptoApi } from '../services/api';
import Chart from 'chart.js/auto';

export default {
  name: 'PriceChart',
  props: {
    crypto: {
      type: Object,
      required: true,
    },
    isVisible: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['close'],
  setup(props, { emit }) {
    const chartCanvas = ref(null);
    const loading = ref(false);
    const selectedRange = ref('24h');
    const startDate = ref('');
    const endDate = ref('');
    const statistics = ref(null);
    let chartInstance = null;

    const timeRanges = [
      { label: '24h', value: '24h' },
      { label: '7d', value: '7d' },
      { label: '30d', value: '30d' },
      { label: 'Todo', value: 'all' },
      { label: 'Personalizado', value: 'custom' },
    ];

    const loadHistoryData = async () => {
      if (!props.crypto || !props.crypto.id) return;

      try {
        loading.value = true;
        const response = await cryptoApi.getHistory(props.crypto.id);

        if (response.success && response.data.history) {
          const filteredData = filterDataByRange(response.data.history);
          renderChart(filteredData);
          calculateStatistics(filteredData);
        }
      } catch (error) {
        console.error('Error loading history:', error);
      } finally {
        loading.value = false;
      }
    };

    const loadCustomRange = async () => {
      if (!startDate.value || !endDate.value) return;

      try {
        loading.value = true;
        const response = await cryptoApi.getHistoryRange(
          props.crypto.id,
          startDate.value,
          endDate.value
        );

        if (response.success && response.data.history) {
          renderChart(response.data.history);
          calculateStatistics(response.data.history);
        }
      } catch (error) {
        console.error('Error loading custom range:', error);
      } finally {
        loading.value = false;
      }
    };

    const filterDataByRange = (data) => {
      if (selectedRange.value === 'all') {
        return data;
      }

      const now = new Date();
      let cutoffDate;

      switch (selectedRange.value) {
        case '24h':
          cutoffDate = new Date(now - 24 * 60 * 60 * 1000);
          break;
        case '7d':
          cutoffDate = new Date(now - 7 * 24 * 60 * 60 * 1000);
          break;
        case '30d':
          cutoffDate = new Date(now - 30 * 24 * 60 * 60 * 1000);
          break;
        default:
          return data;
      }

      return data.filter(item => new Date(item.recorded_at) >= cutoffDate);
    };

    const renderChart = (data) => {
      if (!chartCanvas.value) return;

      if (chartInstance) {
        chartInstance.destroy();
      }

      const sortedData = [...data].sort(
        (a, b) => new Date(a.recorded_at) - new Date(b.recorded_at)
      );

      const labels = sortedData.map(item => {
        const date = new Date(item.recorded_at);
        return date.toLocaleString('es-ES', {
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
        });
      });

      const prices = sortedData.map(item => parseFloat(item.price_usd));

      const ctx = chartCanvas.value.getContext('2d');
      chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Precio (USD)',
            data: prices,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointHoverRadius: 6,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top',
            },
            tooltip: {
              mode: 'index',
              intersect: false,
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              padding: 12,
              titleFont: {
                size: 14,
                weight: 'bold',
              },
              bodyFont: {
                size: 13,
              },
              callbacks: {
                label: function(context) {
                  return `Precio: ${context.parsed.y.toFixed(2)}`;
                },
              },
            },
          },
          scales: {
            x: {
              display: true,
              grid: {
                display: false,
              },
              ticks: {
                maxRotation: 45,
                minRotation: 45,
              },
            },
            y: {
              display: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.05)',
              },
              ticks: {
                callback: function(value) {
                  return '' + value.toFixed(2);
                },
              },
            },
          },
          interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false,
          },
        },
      });
    };

    const calculateStatistics = (data) => {
      if (!data || data.length === 0) {
        statistics.value = null;
        return;
      }

      const prices = data.map(item => parseFloat(item.price_usd));
      const max = Math.max(...prices);
      const min = Math.min(...prices);
      const avg = prices.reduce((a, b) => a + b, 0) / prices.length;

      const firstPrice = prices[0];
      const lastPrice = prices[prices.length - 1];
      const variation = ((lastPrice - firstPrice) / firstPrice) * 100;

      statistics.value = { max, min, avg, variation };
    };

    const getVariationClass = (variation) => {
      return variation > 0 ? 'positive' : variation < 0 ? 'negative' : 'neutral';
    };

    const selectRange = (range) => {
      selectedRange.value = range;
      if (range !== 'custom') {
        loadHistoryData();
      }
    };

    const handleClose = () => {
      emit('close');
    };

    watch(() => props.isVisible, async (newVal) => {
      if (newVal) {
        await nextTick();
        loadHistoryData();
      }
    });

    watch(() => props.crypto, () => {
      if (props.isVisible) {
        loadHistoryData();
      }
    });

    onUnmounted(() => {
      if (chartInstance) {
        chartInstance.destroy();
      }
    });

    return {
      chartCanvas,
      loading,
      selectedRange,
      startDate,
      endDate,
      timeRanges,
      statistics,
      selectRange,
      loadCustomRange,
      handleClose,
      getVariationClass,
    };
  },
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.modal-header h2 {
  margin: 0;
  color: #333;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.modal-header i {
  color: #667eea;
}

.close-btn {
  background: #f0f0f0;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #e0e0e0;
  transform: scale(1.1);
}

.modal-body {
  padding: 1.5rem;
  position: relative;
}

.time-range-selector {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.range-btn {
  padding: 0.75rem 1.5rem;
  border: 2px solid #e0e0e0;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
  color: #666;
}

.range-btn:hover {
  border-color: #667eea;
  color: #667eea;
}

.range-btn.active {
  background: #667eea;
  border-color: #667eea;
  color: white;
}

.custom-date-range {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.date-input-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.date-input-group label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #666;
}

.date-input-group input {
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: border-color 0.2s ease;
}

.date-input-group input:focus {
  outline: none;
  border-color: #667eea;
}

.chart-container {
  height: 400px;
  margin-bottom: 1.5rem;
  position: relative;
}

.statistics {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-label {
  font-size: 0.85rem;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #333;
}

.stat-value.positive {
  color: #2ecc71;
}

.stat-value.negative {
  color: #e74c3c;
}

.stat-value.neutral {
  color: #95a5a6;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  border-radius: 12px;
}

.loading-overlay i {
  color: #667eea;
}

.loading-overlay p {
  color: #666;
  font-weight: 600;
}

@media (max-width: 768px) {
  .modal-content {
    max-height: 95vh;
  }

  .modal-header h2 {
    font-size: 1.2rem;
  }

  .time-range-selector {
    justify-content: center;
  }

  .range-btn {
    padding: 0.625rem 1rem;
    font-size: 0.9rem;
  }

  .chart-container {
    height: 300px;
  }

  .statistics {
    grid-template-columns: repeat(2, 1fr);
  }

  .custom-date-range {
    flex-direction: column;
  }
}
</style>
