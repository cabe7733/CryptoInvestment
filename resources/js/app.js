import './bootstrap';
import { createApp } from 'vue';
import CryptoTracker from './components/CryptoTracker.vue';

const app = createApp({
  components: {
    CryptoTracker,
  },
});

app.mount('#app');
