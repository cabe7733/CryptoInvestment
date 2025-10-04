<template>
  <div class="crypto-search">
    <div class="search-container">
      <div class="search-input-wrapper">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          v-model="searchQuery"
          @input="handleSearch"
          placeholder="Buscar criptomoneda (ej: Bitcoin, BTC, Ethereum...)"
          class="search-input"
          :disabled="isLoading"
        />
        <i
          v-if="searchQuery"
          class="fas fa-times clear-icon"
          @click="clearSearch"
        ></i>
      </div>

      <div v-if="showResults && searchResults.length > 0" class="search-results">
        <div
          v-for="crypto in searchResults"
          :key="crypto.id"
          class="search-result-item"
          @click="selectCrypto(crypto)"
        >
          <div class="crypto-info">
            <span class="crypto-symbol">{{ crypto.symbol }}</span>
            <span class="crypto-name">{{ crypto.name }}</span>
          </div>
          <i class="fas fa-plus add-icon"></i>
        </div>
      </div>

      <div v-if="showResults && searchQuery && searchResults.length === 0" class="no-results">
        <i class="fas fa-info-circle"></i>
        <span>No se encontraron criptomonedas</span>
      </div>

      <div v-if="searching" class="searching-indicator">
        <i class="fas fa-spinner fa-spin"></i>
        <span>Buscando...</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import { cryptoApi } from '../services/api';

export default {
  name: 'CryptoSearch',
  props: {
    isLoading: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['crypto-selected'],
  setup(props, { emit }) {
    const searchQuery = ref('');
    const searchResults = ref([]);
    const showResults = ref(false);
    const searching = ref(false);
    let searchTimeout = null;

    const handleSearch = () => {
      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }

      if (searchQuery.value.length < 2) {
        searchResults.value = [];
        showResults.value = false;
        return;
      }

      searching.value = true;
      showResults.value = true;

      searchTimeout = setTimeout(async () => {
        try {
          const response = await cryptoApi.searchCryptos(searchQuery.value);
          searchResults.value = response.data;
        } catch (error) {
          console.error('Error searching cryptos:', error);
          searchResults.value = [];
        } finally {
          searching.value = false;
        }
      }, 500);
    };

    const selectCrypto = (crypto) => {
      emit('crypto-selected', crypto);
      clearSearch();
    };

    const clearSearch = () => {
      searchQuery.value = '';
      searchResults.value = [];
      showResults.value = false;
    };

    const handleClickOutside = (event) => {
      if (!event.target.closest('.crypto-search')) {
        showResults.value = false;
      }
    };

    document.addEventListener('click', handleClickOutside);

    return {
      searchQuery,
      searchResults,
      showResults,
      searching,
      handleSearch,
      selectCrypto,
      clearSearch,
    };
  },
};
</script>

<style scoped>
.crypto-search {
  margin-bottom: 2rem;
}

.search-container {
  position: relative;
  max-width: 600px;
  margin: 0 auto;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: white;
  border-radius: 50px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: box-shadow 0.3s ease;
}

.search-input-wrapper:focus-within {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.search-icon {
  position: absolute;
  left: 1.5rem;
  color: #667eea;
  font-size: 1.2rem;
}

.search-input {
  flex: 1;
  border: none;
  padding: 1rem 3rem 1rem 3.5rem;
  font-size: 1rem;
  border-radius: 50px;
  outline: none;
  width: 100%;
}

.search-input::placeholder {
  color: #999;
}

.clear-icon {
  position: absolute;
  right: 1.5rem;
  color: #999;
  cursor: pointer;
  transition: color 0.2s ease;
}

.clear-icon:hover {
  color: #667eea;
}

.search-results {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  max-height: 400px;
  overflow-y: auto;
  z-index: 1000;
}

.search-result-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  cursor: pointer;
  transition: background 0.2s ease;
  border-bottom: 1px solid #f0f0f0;
}

.search-result-item:last-child {
  border-bottom: none;
}

.search-result-item:hover {
  background: #f8f9ff;
}

.crypto-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.crypto-symbol {
  font-weight: 700;
  color: #667eea;
  font-size: 1rem;
  min-width: 60px;
}

.crypto-name {
  color: #666;
  font-size: 0.95rem;
}

.add-icon {
  color: #667eea;
  font-size: 1.2rem;
}

.no-results,
.searching-indicator {
  padding: 1.5rem;
  text-align: center;
  color: #666;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.searching-indicator i {
  color: #667eea;
}

@media (max-width: 768px) {
  .search-input {
    padding: 0.875rem 3rem 0.875rem 3rem;
    font-size: 0.9rem;
  }

  .search-icon {
    left: 1rem;
  }

  .clear-icon {
    right: 1rem;
  }
}
</style>
