<script setup>
import { usePollStore } from '@/stores/usePollStore';

const { polls, deletePoll, startPoll } = usePollStore();

async function delPoll(id) {
    await deletePoll(id);
}
async function startPollAction(id) {
    await startPoll(id);
}
</script>

<template>
    <p v-if="polls.length === 0">Aucun sondage.</p>

    <table v-else class="w-full border-collapse text-left">
        <thead>
        <tr>
            <th class="border px-3 py-2">Actions</th>
            <th class="border px-3 py-2">ID</th>
            <th class="border px-3 py-2">Titre</th>
            <th class="border px-3 py-2">Question</th>
            <th class="border px-3 py-2">Brouillon</th>
            <th class="border px-3 py-2">Debut</th>
            <th class="border px-3 py-2">Fin</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="poll in polls" :key="poll.id">
            <td class="border px-3 py-2"><button @click="delPoll(poll.id)" class="bg-red-600 text-white px-2 rounded">Supp.</button>
                <button v-if="poll.is_draft" @click="startPollAction(poll.id)" class="bg-green-600 text-white px-2 rounded"> Start </button>
            </td>
            <td class="border px-3 py-2">{{ poll.id }}</td>
            <td class="border px-3 py-2">{{ poll.title || '-' }}</td>
            <td class="border px-3 py-2">{{ poll.question }}</td>
            <td class="border px-3 py-2">{{ poll.is_draft ? 'Oui' : 'Non' }}</td>
            <td class="border px-3 py-2">{{ poll.started_at || '-' }}</td>
            <td class="border px-3 py-2">{{ poll.ends_at || '-' }}</td>
        </tr>
        </tbody>
    </table>
</template>

<style scoped>

</style>
