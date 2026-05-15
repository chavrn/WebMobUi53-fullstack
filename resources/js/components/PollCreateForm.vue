<script setup>
import { ref } from 'vue'
import { usePollStore } from '@/stores/usePollStore'

const { createPoll } = usePollStore()

const emit = defineEmits(['created'])

const isDraft = ref(true)

// Champs principaux
const title = ref('')
const question = ref('')

// Options de réponse
const options = ref([
    { label: '' },
    { label: '' },
])

// Paramètres
const allowMultiple = ref(false)
const resultsPublic = ref(false)
const endsAt = ref(null)

// UI
const error = ref(null)
const loading = ref(false)

function addOption() {
    options.value.push({ label: '' })
}

function removeOption(index) {
    options.value.splice(index, 1)
}

async function submit() {
    error.value = null

    if (!title.value.trim()) {
        error.value = 'Le titre est obligatoire'
        return
    }

    if (!question.value.trim()) {
        error.value = 'La question est obligatoire'
        return
    }

    if (options.value.length < 2) {
        error.value = 'Au moins deux options sont requises'
        return
    }

    if (options.value.some(o => !o.label.trim())) {
        error.value = 'Toutes les options doivent être remplies'
        return
    }

    loading.value = true

    try {
        await createPoll({
            title: title.value,
            question: question.value,
            options: options.value.map(o => o.label),
            allow_multiple_choices: allowMultiple.value,
            results_public: resultsPublic.value,
            ends_at: endsAt.value,
            isDraft: isDraft.value,
        })

        emit('created')

        title.value = ''
        question.value = ''
        options.value = [{ label: '' }, { label: '' }]
        allowMultiple.value = false
        resultsPublic.value = false
        endsAt.value = null

    } catch {
        // Erreur affichée via le système de notification
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="mb-6 p-4 border rounded space-y-4">
        <h2 class="font-bold text-lg">Créer un sondage</h2>

        <!-- Titre -->
        <div>
            <label class="block font-semibold">Titre</label>
            <input
                v-model="title"
                type="text"
                class="border p-1 w-full"
            />
        </div>

        <!-- Question -->
        <div>
            <label class="block font-semibold">Question</label>
            <input
                v-model="question"
                type="text"
                class="border p-1 w-full"
            />
        </div>

        <!-- Options -->
        <div>
            <label class="block font-semibold mb-1">
                Options de réponse
            </label>

            <div
                v-for="(option, index) in options"
                :key="index"
                class="flex mb-2"
            >
                <input
                    v-model="option.label"
                    type="text"
                    class="border p-1 flex-1"
                />
                <button
                    type="button"
                    class="ml-2 px-2 bg-red-600 text-white rounded"
                    @click="removeOption(index)"
                    v-if="options.length > 2"
                >
                    ✕
                </button>
            </div>

            <button
                type="button"
                class="mt-2 bg-gray-200 px-3 py-1 rounded"
                @click="addOption"
            >
                + Ajouter une option
            </button>
        </div>

        <!-- Paramètres -->
        <div class="space-y-2">
            <label>
                <input type="checkbox" v-model="allowMultiple" />
                Autoriser les choix multiples
            </label>

            <label>
                <input type="checkbox" v-model="resultsPublic" />
                Résultats publics
            </label>
            <div class="space-y-1">
                <p class="font-semibold">État du sondage</p>

                <label class="block">
                    <input
                        type="radio"
                        :value="true"
                        v-model="isDraft"
                    />
                    Brouillon (non visible)
                </label>

                <label class="block">
                    <input
                        type="radio"
                        :value="false"
                        v-model="isDraft"
                    />
                    Publier immédiatement
                </label>
            </div>
        </div>

        <!-- Fin -->
        <div>
            <label class="block font-semibold">
                Date de fin (optionnel)
            </label>
            <input
                type="datetime-local"
                v-model="endsAt"
                class="border p-1"
            />
        </div>

        <!-- Erreur -->
        <p v-if="error" class="text-red-600">{{ error }}</p>

        <!-- Submit -->
        <button
            type="submit"
            :disabled="loading"
            class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-60 disabled:cursor-not-allowed"
        >
            <span v-if="loading">Chargement…</span>
            <span v-else>Créer le sondage</span>
        </button>
    </form>
</template>
