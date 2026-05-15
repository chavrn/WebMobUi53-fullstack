<script setup>
import { ref } from 'vue'
import { usePollStore } from '@/stores/usePollStore'
import PollEditForm from '@/components/PollEditForm.vue'

const { polls, deletePoll, startPoll } = usePollStore()

const editingPollId = ref(null)
const copiedPollId = ref(null)

async function delPoll(id) {
    try { await deletePoll(id) } catch {}
}

async function startPollAction(id) {
    try { await startPoll(id) } catch {}
}

function shareUrl(token) {
    return `${window.location.origin}/polls/${token}`
}

async function copyLink(poll) {
    await navigator.clipboard.writeText(shareUrl(poll.secret_token))
    copiedPollId.value = poll.id
    setTimeout(() => { copiedPollId.value = null }, 2000)
}
</script>

<template>
    <PollEditForm
        v-if="editingPollId"
        :poll-id="editingPollId"
        @updated="editingPollId = null"
        @cancel="editingPollId = null"
    />

    <p v-if="polls.length === 0">Aucun sondage.</p>

    <table v-else class="w-full border-collapse text-left">
        <thead>
        <tr>
            <th class="border px-3 py-2">Actions</th>
            <th class="border px-3 py-2">ID</th>
            <th class="border px-3 py-2">Titre</th>
            <th class="border px-3 py-2">Question</th>
            <th class="border px-3 py-2">Brouillon</th>
            <th class="border px-3 py-2">Début</th>
            <th class="border px-3 py-2">Fin</th>
        </tr>
        </thead>

        <tbody>
        <tr v-for="poll in polls" :key="poll.id">
            <td class="border px-3 py-2 space-x-2">
                <button
                    @click="delPoll(poll.id)"
                    class="bg-red-600 text-white px-2 py-1 rounded"
                >
                    Supp.
                </button>

                <button
                    v-if="poll.is_draft"
                    @click="startPollAction(poll.id)"
                    class="bg-green-600 text-white px-2 py-1 rounded"
                >
                    Start
                </button>

                <button
                    v-if="poll.is_draft"
                    @click="editingPollId = poll.id"
                    class="bg-yellow-500 text-white px-2 py-1 rounded"
                >
                    Modifier
                </button>

                <a
                    :href="shareUrl(poll.secret_token)"
                    class="text-blue-600 underline"
                    target="_blank"
                >
                    Ouvrir
                </a>

                <button
                    @click="copyLink(poll)"
                    :title="shareUrl(poll.secret_token)"
                    class="px-2 py-1 rounded border text-sm transition-colors"
                    :class="copiedPollId === poll.id
                        ? 'bg-green-100 border-green-500 text-green-700'
                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-100'"
                >
                    {{ copiedPollId === poll.id ? 'Copié !' : 'Copier le lien' }}
                </button>
            </td>

            <td class="border px-3 py-2">
                {{ poll.id }}
            </td>

            <td class="border px-3 py-2">
                {{ poll.title || '-' }}
            </td>

            <td class="border px-3 py-2">
                {{ poll.question }}
            </td>

            <td class="border px-3 py-2">
                {{ poll.is_draft ? 'Oui' : 'Non' }}
            </td>

            <td class="border px-3 py-2">
                {{ poll.started_at || '-' }}
            </td>

            <td class="border px-3 py-2">
                {{ poll.ends_at || '-' }}
            </td>
        </tr>
        </tbody>
    </table>
</template>

<style scoped>
</style>
