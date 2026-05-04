<script setup>
    import { ref, onMounted } from 'vue'
    import PollCreateForm from '@/components/PollCreateForm.vue'
    import PollTable from '@/components/PollTable.vue'
    import { usePollStore } from '@/stores/usePollStore'

    const showCreateForm = ref(false)
    const { fetchPolls } = usePollStore()

    onMounted(fetchPolls)

    //fermer le formulaire après création
    function onPollCreated() {
    showCreateForm.value = false
}
</script>


<template>
    <main class="p-6">
        <h1 class="mb-6 text-2xl font-bold">Mes sondages</h1>

        <div class="mb-4">
            <button
                @click="showCreateForm = true"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Créer un sondage
            </button>
        </div>

        <PollCreateForm
            v-if="showCreateForm"
            @created="onPollCreated"
        />

        <PollTable />

    </main>
</template>
