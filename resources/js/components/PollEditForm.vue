<script setup>
import { ref, onMounted } from 'vue'
import { usePollStore } from '@/stores/usePollStore'

const props = defineProps({
    pollId: { type: Number, required: true },
})

const emit = defineEmits(['updated', 'cancel'])

const { fetchPollForEdit, updatePoll } = usePollStore()

const title = ref('')
const question = ref('')
const options = ref([])
const allowMultiple = ref(false)
const resultsPublic = ref(false)
const endsAt = ref(null)
const error = ref(null)
const loading = ref(false)

onMounted(async () => {
    try {
        const poll = await fetchPollForEdit(props.pollId)
        title.value = poll.title ?? ''
        question.value = poll.question
        options.value = poll.options.map(o => ({ label: o.label }))
        allowMultiple.value = poll.allow_multiple_choices
        resultsPublic.value = poll.results_public
        endsAt.value = poll.ends_at ? poll.ends_at.slice(0, 16) : null
    } catch {
        error.value = 'Impossible de charger le sondage.'
    }
})

function addOption() {
    options.value.push({ label: '' })
}

function removeOption(index) {
    options.value.splice(index, 1)
}

async function submit() {
    error.value = null

    if (!title.value.trim()) { error.value = 'Le titre est obligatoire'; return }
    if (!question.value.trim()) { error.value = 'La question est obligatoire'; return }
    if (options.value.length < 2) { error.value = 'Au moins deux options sont requises'; return }
    if (options.value.some(o => !o.label.trim())) { error.value = 'Toutes les options doivent être remplies'; return }

    loading.value = true
    try {
        await updatePoll(props.pollId, {
            title: title.value,
            question: question.value,
            options: options.value.map(o => o.label),
            allow_multiple_choices: allowMultiple.value,
            results_public: resultsPublic.value,
            ends_at: endsAt.value,
        })
        emit('updated')
    } catch {
        error.value = 'Erreur lors de la modification du sondage.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="mb-6 p-4 border rounded space-y-4 bg-yellow-50">
        <h2 class="font-bold text-lg">Modifier le sondage</h2>

        <div>
            <label class="block font-semibold">Titre</label>
            <input v-model="title" type="text" class="border p-1 w-full" />
        </div>

        <div>
            <label class="block font-semibold">Question</label>
            <input v-model="question" type="text" class="border p-1 w-full" />
        </div>

        <div>
            <label class="block font-semibold mb-1">Options de réponse</label>
            <div v-for="(option, index) in options" :key="index" class="flex mb-2">
                <input v-model="option.label" type="text" class="border p-1 flex-1" />
                <button
                    type="button"
                    class="ml-2 px-2 bg-red-600 text-white rounded"
                    @click="removeOption(index)"
                    v-if="options.length > 2"
                >
                    ✕
                </button>
            </div>
            <button type="button" class="mt-2 bg-gray-200 px-3 py-1 rounded" @click="addOption">
                + Ajouter une option
            </button>
        </div>

        <div class="space-y-2">
            <label>
                <input type="checkbox" v-model="allowMultiple" />
                Autoriser les choix multiples
            </label>
            <label>
                <input type="checkbox" v-model="resultsPublic" />
                Résultats publics
            </label>
        </div>

        <div>
            <label class="block font-semibold">Date de fin (optionnel)</label>
            <input type="datetime-local" v-model="endsAt" class="border p-1" />
        </div>

        <p v-if="error" class="text-red-600">{{ error }}</p>

        <div class="flex gap-2">
            <button type="submit" :disabled="loading" class="bg-blue-600 text-white px-4 py-2 rounded">
                Enregistrer
            </button>
            <button type="button" @click="emit('cancel')" class="bg-gray-300 px-4 py-2 rounded">
                Annuler
            </button>
        </div>
    </form>
</template>
