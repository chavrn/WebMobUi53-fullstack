import { createApp } from 'vue';
import App from './App.vue';
const mountPoint = document.getElementById('app');
if (mountPoint) createApp(App).mount('#app');
