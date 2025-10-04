import axios from 'axios';

const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

apiClient.interceptors.response.use(
  response => response.data,
  error => {
    console.error('API Error:', error);

    if (error.response) {

      const message = error.response.data.message || 'Error en la solicitud';
      console.error('Server Error:', message);
    } else if (error.request) {

      console.error('Network Error: No se recibió respuesta del servidor');
    } else {
      console.error('Error:', error.message);
    }

    return Promise.reject(error);
  }
);

export const cryptoApi = {
  /**
   * Obtener criptomonedas trackeadas
   */
  getTrackedCryptos() {
    return apiClient.get('/cryptocurrencies');
  },

  /**
   * Buscar criptomonedas
   */
  searchCryptos(query) {
    return apiClient.get('/cryptocurrencies/search', {
      params: { q: query },
    });
  },

  /**
   * Agregar criptomoneda al seguimiento
   */
  trackCrypto(data) {
    return apiClient.post('/cryptocurrencies/track', data);
  },

  /**
   * Eliminar criptomoneda del seguimiento
   */
  removeCrypto(id) {
    return apiClient.delete(`/cryptocurrencies/${id}`);
  },

  /**
   * Obtener precios actuales
   */
  getPrices() {
    return apiClient.get('/cryptocurrencies/prices');
  },

  /**
   * Obtener historial de precios
   */
  getHistory(cryptoId) {
    return apiClient.get(`/history/${cryptoId}`);
  },

  /**
   * Obtener historial en rango de fechas
   */
  getHistoryRange(cryptoId, startDate, endDate) {
    return apiClient.get(`/history/${cryptoId}/range`, {
      params: {
        start_date: startDate,
        end_date: endDate,
      },
    });
  },
};

export default apiClient;
