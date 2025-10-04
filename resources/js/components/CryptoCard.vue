<template>
  <div class="crypto-card">
    <div class="card-header">
      <div class="crypto-title">
        <h3 class="crypto-symbol">{{ crypto.symbol }}</h3>
        <p class="crypto-name">{{ crypto.name }}</p>
      </div>
      <button class="remove-btn" @click="handleRemove" title="Eliminar">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="card-body">
      <div class="price-section">
        <span class="price-label">Precio</span>
        <h2 class="price">{{ formatPrice(currentPrice) }}</h2>
      </div>

      <div class="changes-grid">
        <div class="change-item">
          <span class="change-label">1h</span>
          <span class="change-value" :class="getChangeClass(percentChange1h)">
            <i :class="getChangeIcon(percentChange1h)"></i>
            {{ formatPercent(percentChange1h) }}
          </span>
        </div>
        <div class="change-item">
          <span class="change-label">24h</span>
          <span class="change-value" :class="getChangeClass(percentChange24h)">
            <i :class="getChangeIcon(percentChange24h)"></i>
            {{ formatPercent(percentChange24h) }}
          </span>
        </div>
        <div class="change-item">
          <span class="change-label">7d</span>
          <span class="change-value" :class="getChangeClass(percentChange7d)">
            <i :class="getChangeIcon(percentChange7d)"></i>
            {{ formatPercent(percentChange7d) }}
          </span>
        </div>
      </div>

      <div class="info-section">
        <div class="info-item">
          <span class="info-label">
            <i class="fas fa-chart-pie"></i>
            Market Cap
          </span>
          <span class="info-value">{{ formatLargeNumber(marketCap) }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">
            <i class="fas fa-exchange-alt"></i>
            Volumen 24h
          </span>
          <span class="info-value">{{ formatLargeNumber(volume24h) }}</span>
        </div>
      </div>

      <button class="chart-btn" @click="handleShowChart">
        <i class="fas fa-chart-line"></i>
        Ver Historial
      </button>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'CryptoCard',
  props: {
    crypto: {
      type: Object,
      required: true,
    },
  },
  emits: ['remove', 'show-chart'],
  setup(props, { emit }) {
    const currentPrice = computed(() => props.crypto.price || 0);
    const percentChange1h = computed(() => props.crypto.percent_change_1h || 0);
    const percentChange24h = computed(() => props.crypto.percent_change_24h || 0);
    const percentChange7d = computed(() => props.crypto.percent_change_7d || 0);
    const marketCap = computed(() => props.crypto.market_cap || 0);
    const volume24h = computed(() => props.crypto.volume_24h || 0);

    const formatPrice = (price) => {
      if (price === 0) return '$0.00';
      if (price < 0.01) {
        return `$${price.toFixed(8)}`;
      }
      if (price < 1) {
        return `$${price.toFixed(4)}`;
      }
      return `$${price.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })}`;
    };

    const formatPercent = (percent) => {
      const absPercent = Math.abs(percent);
      return `${absPercent.toFixed(2)}%`;
    };

    const formatLargeNumber = (num) => {
      if (num === 0) return '$0';
      if (num >= 1e12) {
        return `$${(num / 1e12).toFixed(2)}T`;
      }
      if (num >= 1e9) {
        return `$${(num / 1e9).toFixed(2)}B`;
      }
      if (num >= 1e6) {
        return `$${(num / 1e6).toFixed(2)}M`;
      }
      if (num >= 1e3) {
        return `$${(num / 1e3).toFixed(2)}K`;
      }
      return `$${num.toFixed(2)}`;
    };

    const getChangeClass = (percent) => {
      if (percent > 0) return 'positive';
      if (percent < 0) return 'negative';
      return 'neutral';
    };

    const getChangeIcon = (percent) => {
      if (percent > 0) return 'fas fa-arrow-up';
      if (percent < 0) return 'fas fa-arrow-down';
      return 'fas fa-minus';
    };

    const handleRemove = () => {
      emit('remove', props.crypto.id);
    };

    const handleShowChart = () => {
      emit('show-chart', props.crypto);
    };

    return {
      currentPrice,
      percentChange1h,
      percentChange24h,
      percentChange7d,
      marketCap,
      volume24h,
      formatPrice,
      formatPercent,
      formatLargeNumber,
      getChangeClass,
      getChangeIcon,
      handleRemove,
      handleShowChart,
    };
  },
};
</script>

<style scoped>
.crypto-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.crypto-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f0f0f0;
}

.crypto-title h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #667eea;
  margin: 0;
}

.crypto-name {
  color: #666;
  font-size: 0.9rem;
  margin: 0.25rem 0 0 0;
}

.remove-btn {
  background: #ff4757;
  color: white;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.remove-btn:hover {
  background: #ff3838;
  transform: scale(1.1);
}

.price-section {
  margin-bottom: 1.5rem;
}

.price-label {
  font-size: 0.85rem;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.price {
  font-size: 2rem;
  font-weight: 700;
  color: #333;
  margin: 0.25rem 0 0 0;
}

.changes-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.change-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.change-label {
  font-size: 0.75rem;
  color: #999;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
}

.change-value {
  font-weight: 700;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.change-value.positive {
  color: #2ecc71;
}

.change-value.negative {
  color: #e74c3c;
}

.change-value.neutral {
  color: #95a5a6;
}

.info-section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.info-label {
  font-size: 0.85rem;
  color: #666;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.info-label i {
  color: #667eea;
}

.info-value {
  font-weight: 600;
  color: #333;
  font-size: 0.9rem;
}

.chart-btn {
  width: 100%;
  padding: 0.875rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.chart-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

@media (max-width: 768px) {
  .crypto-card {
    padding: 1.25rem;
  }

  .price {
    font-size: 1.75rem;
  }

  .changes-grid {
    gap: 0.75rem;
  }
}
</style>
