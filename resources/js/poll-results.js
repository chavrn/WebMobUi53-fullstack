import { createApp } from 'vue';
import PollResultsApp from './polls/PollResultsApp.vue';

const mountPoint = document.getElementById('poll-results-app');

if (mountPoint) {
    createApp(PollResultsApp).mount(mountPoint);
}
