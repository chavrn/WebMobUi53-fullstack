import { ref } from 'vue';
import { useFetchApi } from '../composables/useFetchApi';
import { useNotification } from '../composables/useNotification';

const polls = ref([]);

export function usePollStore() {
    const { fetchApi } = useFetchApi();
    const { notify } = useNotification();

    function handleApiError(err) {
        if (err?.status === 422) {
            notify(err?.data?.message ?? 'Données invalides.', 'warning')
        } else if (err?.status === 403) {
            notify('Action non autorisée.', 'error')
        } else if (err?.status === 500) {
            notify('Erreur serveur. Veuillez réessayer.', 'error')
        } else {
            notify(err?.data?.message ?? err?.statusText ?? 'Une erreur est survenue.', 'error')
        }
        throw err
    }

    function setPolls(data) {
        polls.value = data;
    }

    async function fetchPolls() {
        try {
            const result = await fetchApi({ url: 'polls', method: 'GET' })
            if (result) polls.value = result
        } catch (err) {
            handleApiError(err)
        }
    }

    async function deletePoll(id) {
        try {
            await fetchApi({ url: 'polls/' + id, method: 'DELETE' });
            polls.value = polls.value.filter(p => p.id !== id);
        } catch (err) {
            handleApiError(err)
        }
    }

    async function startPoll(id) {
        try {
            await fetchApi({ url: `polls/${id}/start`, method: 'POST' });
            await fetchPolls()
        } catch (err) {
            handleApiError(err)
        }
    }

    async function createPoll(data) {
        try {
            await fetchApi({ url: 'polls', method: 'POST', data })
            await fetchPolls()
        } catch (err) {
            handleApiError(err)
        }
    }

    async function fetchPollForEdit(id) {
        try {
            return await fetchApi({ url: `polls/${id}/edit`, method: 'GET' })
        } catch (err) {
            handleApiError(err)
        }
    }

    async function updatePoll(id, data) {
        try {
            const result = await fetchApi({ url: `polls/${id}`, method: 'PUT', data })
            await fetchPolls()
            return result
        } catch (err) {
            handleApiError(err)
        }
    }

    return {
        polls, setPolls, fetchPolls, deletePoll, startPoll, createPoll,
        fetchPollForEdit, updatePoll,
    }
}
