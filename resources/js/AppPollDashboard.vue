<script setup>
import PollTable from './components/PollTable.vue';
import { usePollStore } from './stores/usePollStore';
import PollCreateForm from '@/components/PollCreateForm.vue'


const props = defineProps({
    polls: { type: Array, default: () => [] },
    loginUrl: { type: String, default: null },
});

const { setPolls } = usePollStore();
setPolls(props.polls);
</script>

<template>
    <main class="p-6">
        <h1 class="mb-6 text-2xl font-bold">Mes sondages</h1>
        <PollTable />
        <PollCreateForm />
    </main>
</template>
