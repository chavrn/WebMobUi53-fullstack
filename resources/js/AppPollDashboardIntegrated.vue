<script setup>
import { watch } from 'vue';
import PollTable from './components/PollTable.vue';
import { useFetchApi } from './composables/useFetchApi';

const props = defineProps({
    loginUrl: { type: String, default: null },
});

const { fetchApiToRef } = useFetchApi();
const { data: polls, error, fetchNow } = fetchApiToRef({ url: '/polls' });

function handleError(err) {
    if (!err) return;
    if (err?.status === 401) window.location.href = props.loginUrl;
}

watch(error, handleError);
</script>

<template>
    <main class="p-6">
        <h1 class="mb-6 text-2xl font-bold">Mes sondages</h1>
        <PollTable :polls="polls ?? []" @deleted="fetchNow" />
    </main>
</template>
