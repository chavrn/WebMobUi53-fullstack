<script setup>
import { ref, onMounted } from 'vue'
import { useFetchApi } from '@/composables/useFetchApi'

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
    loginUrl: {
        type: String,
        required: true,
    },
})

const { fetchApi } = useFetchApi()

const poll = ref(null)
const loading = ref(true)
const error = ref(null)

async function loadPoll() {
    try {
        console.log('🔄 Appel API avec token:', props.token);

        // Test: fetch direct sans useFetchApi
        const response = await fetch(`/api/v1/polls/${props.token}`);
        console.log('Response object:', response);
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        console.log('Response headers:', response.headers.get('content-type'));

        const data = await response.json();
        console.log('✅ JSON parsé avec succès:', data);
        poll.value = data;

    } catch (e) {
        console.error('❌ Erreur complète:');
        console.log('   Error:', e);
        console.log('   Error message:', e.message);
        console.log('   Error stack:', e.stack);
        error.value = 'Sondage introuvable ou inaccessible'
    } finally {
        loading.value = false
    }
}

onMounted(loadPoll)
</script>

<template>
    <div class="max-w-xl mx-auto">
        <p v-if="loading">Chargement du sondage…</p>

        <p v-if="error" class="text-red-600">
            {{ error }}
        </p>

        <div v-if="poll">
            <h1 class="text-2xl font-bold mb-2">
                {{ poll.title }}
            </h1>

            <p class="mb-4">
                {{ poll.question }}
            </p>

            <ul class="mb-4">
                <li
                    v-for="option in poll.options"
                    :key="option.id"
                    class="border p-2 mb-2 rounded"
                >
                    {{ option.label }}
                </li>
            </ul>

            <p v-if="poll.is_draft" class="italic text-sm">
                Ce sondage est encore en brouillon
            </p>

            <p v-if="poll.has_ended" class="text-red-600 text-sm">
                Ce sondage est terminé
            </p>

            <p v-if="!poll.is_draft && !poll.has_ended" class="text-green-600 text-sm">
                Sondage en cours
            </p>
        </div>
    </div>
</template>
