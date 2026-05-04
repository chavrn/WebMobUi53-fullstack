import './bootstrap';
import { createApp } from 'vue';
import { Quasar } from 'quasar';
import '@quasar/extras/roboto-font/roboto-font.css';
import '@quasar/extras/material-icons/material-icons.css';
import 'quasar/dist/quasar.css';
import App from './AppPollDashboard.vue';

const el = document.getElementById('app');
const props = JSON.parse(el.dataset.props ?? '{}');

createApp(App, props)
    .use(Quasar)
    .mount(el);
