<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useFetchApi } from '@/composables/useFetchApi'

const props = defineProps({
    token:    { type: String, required: true },
    loginUrl: { type: String, required: true },
})

const { fetchApi } = useFetchApi()

const poll      = ref(null)
const loading   = ref(true)
const error     = ref(null)
const voteError = ref(null)
const voting    = ref(false)

// Choix multiples : liste des options cochées avant soumission
const selectedOptionIds = ref([])

const pollInterval = ref(null)

const isActive = computed(() =>
    poll.value && !poll.value.is_draft && !poll.value.has_ended
)

const showResults = computed(() =>
    poll.value && (
        poll.value.has_ended ||
        poll.value.user_has_voted ||
        (!poll.value.is_authenticated && poll.value.results_public)
    )
)

const canVote = computed(() =>
    isActive.value && poll.value.is_authenticated && !poll.value.user_has_voted
)

const formattedEndDate = computed(() => {
    if (!poll.value?.ends_at) return null
    return new Intl.DateTimeFormat('fr-CH', {
        dateStyle: 'long',
        timeStyle: 'short',
    }).format(new Date(poll.value.ends_at))
})

function startPolling() {
    if (pollInterval.value) return
    pollInterval.value = setInterval(async () => {
        try {
            poll.value = await fetchApi({ url: `polls/${props.token}` })
        } catch (e) {
            // Ignore errors during polling
        }
    }, 5000) // Poll every 5 seconds
}

function stopPolling() {
    if (pollInterval.value) {
        clearInterval(pollInterval.value)
        pollInterval.value = null
    }
}

async function loadPoll() {
    try {
        poll.value = await fetchApi({ url: `polls/${props.token}` })
    } catch {
        error.value = 'Sondage introuvable ou inaccessible.'
    } finally {
        loading.value = false
    }
}

async function submitVote(optionId) {
    if (voting.value) return
    voteError.value = null
    voting.value = true
    try {
        const result = await fetchApi({
            url:    `polls/${props.token}/vote`,
            method: 'POST',
            data:   { poll_option_id: optionId },
        })
        poll.value.options       = result.options
        poll.value.user_has_voted = true
    } catch (e) {
        voteError.value = e?.data?.message ?? 'Erreur lors du vote.'
    } finally {
        voting.value = false
    }
}

async function submitMultipleVotes() {
    for (const optionId of selectedOptionIds.value) {
        await submitVote(optionId)
        if (voteError.value) break
    }
    selectedOptionIds.value = []
}

watch(showResults, (newVal) => {
    if (newVal) {
        startPolling()
    } else {
        stopPolling()
    }
})

onMounted(loadPoll)
onUnmounted(stopPolling)
</script>

<template>
    <div class="max-w-xl mx-auto p-4">

        <p v-if="loading" class="text-gray-500">Chargement du sondage…</p>
        <p v-if="error"   class="text-red-600">{{ error }}</p>

        <div v-if="poll">
            <h1 class="text-2xl font-bold mb-1">{{ poll.title }}</h1>
            <p class="mb-6 text-gray-700">{{ poll.question }}</p>

            <!-- Brouillon -->
            <p v-if="poll.is_draft" class="italic text-gray-500">
                Ce sondage n'est pas encore ouvert au vote.
            </p>

            <!-- Résultats (sondage terminé, déjà voté, ou résultats publics) -->
            <template v-else-if="showResults">
                <div v-if="poll.has_ended"
                     class="mb-4 p-4 bg-red-50 border border-red-300 rounded-lg">
                    <p class="font-semibold text-red-700">Sondage clôturé</p>
                    <p class="text-red-600 text-sm mt-1">Il n'est plus possible de voter.</p>
                    <p v-if="poll.ends_at" class="text-red-500 text-xs mt-1">
                        Clôturé le {{ formattedEndDate }}
                    </p>
                </div>
                <p v-else class="text-sm font-medium mb-4 text-green-600">
                    <template v-if="poll.user_has_voted">Votre vote a bien été enregistré</template>
                    <template v-else>Résultats en temps réel</template>
                </p>

                <ul class="space-y-4">
                    <li v-for="option in poll.options" :key="option.id">
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ option.label }}</span>
                            <span class="font-semibold">{{ option.percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div
                                class="bg-blue-500 h-3 rounded-full transition-all duration-500"
                                :style="{ width: option.percentage + '%' }"
                            ></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ option.votes_count }} vote(s)</p>
                    </li>
                </ul>
            </template>

            <!-- Non authentifié -->
            <template v-else-if="!poll.is_authenticated">
                <p class="text-gray-600 mb-4">
                    Vous devez être connecté pour voter.
                </p>
                <a :href="loginUrl"
                   class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Se connecter
                </a>
            </template>

            <!-- Vote — choix unique -->
            <template v-else-if="canVote && !poll.allow_multiple_choices">
                <p class="text-sm text-gray-500 mb-3">Choisissez une option :</p>
                <ul class="space-y-2">
                    <li v-for="option in poll.options" :key="option.id">
                        <button
                            @click="submitVote(option.id)"
                            :disabled="voting"
                            class="w-full text-left border px-4 py-3 rounded
                                   hover:bg-blue-50 hover:border-blue-400
                                   disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ option.label }}
                        </button>
                    </li>
                </ul>
                <p v-if="voting" class="text-sm text-gray-400 mt-3">Envoi en cours…</p>
            </template>

            <!-- Vote — choix multiples -->
            <template v-else-if="canVote && poll.allow_multiple_choices">
                <p class="text-sm text-gray-500 mb-3">Choisissez une ou plusieurs options :</p>
                <ul class="space-y-2 mb-4">
                    <li v-for="option in poll.options" :key="option.id">
                        <label class="flex items-center gap-3 border px-4 py-3 rounded
                                      cursor-pointer hover:bg-blue-50 hover:border-blue-400">
                            <input type="checkbox" :value="option.id" v-model="selectedOptionIds" />
                            {{ option.label }}
                        </label>
                    </li>
                </ul>
                <button
                    @click="submitMultipleVotes"
                    :disabled="voting || selectedOptionIds.length === 0"
                    class="bg-blue-600 text-white px-4 py-2 rounded
                           hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="voting">Envoi en cours…</span>
                    <span v-else>Voter</span>
                </button>
            </template>

            <p v-if="voteError" class="text-red-600 mt-4">{{ voteError }}</p>
        </div>
    </div>
</template>
