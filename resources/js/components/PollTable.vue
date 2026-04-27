<script setup>
import { usePollStore } from '../stores/usePollStore';

const { polls, deletePoll } = usePollStore();

async function handleDelete(id) {
    if (!confirm('Supprimer ce sondage ?')) return;
    await deletePoll(id);
}
</script>

<template>
    <p v-if="polls.length === 0" class="text-gray-500">Aucun sondage pour l'instant.</p>

    <ul v-else class="flex flex-col gap-4">
        <li
            v-for="poll in polls"
            :key="poll.id"
            class="rounded border p-4 shadow-sm"
        >
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-lg">{{ poll.title || poll.question }}</p>
                    <p v-if="poll.title" class="text-sm text-gray-500">{{ poll.question }}</p>
                    <p class="mt-1 text-sm">
                        <span v-if="poll.is_draft" class="text-amber-600">Brouillon</span>
                        <span v-else class="text-green-600">Actif</span>
                    </p>
                </div>
                <div class="text-sm text-gray-400">
                    {{ poll.options?.length ?? 0 }} option(s)
                </div>
            </div>

            <button @click="handleDelete(poll.id) "class="mt-3 text-sm text-red-600 hover:underline">
                Supprimer
            </button>
        </li>
    </ul>
</template>
