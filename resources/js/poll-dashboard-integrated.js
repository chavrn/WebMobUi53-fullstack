import './bootstrap';
import { createApp } from 'vue';
import App from './AppPollDashboardIntegrated.vue';
import PublicPollView from './components/PublicPollView.vue';


const el = document.getElementById('app');
const props = JSON.parse(el.dataset.props)

createApp(App, props).mount(el);
