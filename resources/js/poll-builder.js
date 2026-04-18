import { createApp } from 'vue';
import PollBuilderApp from './polls/PollBuilderApp.vue';

const mountPoint = document.getElementById('poll-builder-app');

if (mountPoint) {
    createApp(PollBuilderApp).mount(mountPoint);
}
