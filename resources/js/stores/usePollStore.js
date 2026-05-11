import { ref } from 'vue';
import { useFetchApi } from '../composables/useFetchApi';

const polls = ref([]);

export function usePollStore() {
    const { fetchApi } = useFetchApi();

    function setPolls(data) {
        polls.value = data;
    }
    async function fetchPolls() {
        const result = await fetchApi({ url: 'polls', method: 'GET' })
        if (result) {
            polls.value = result
        }
    }
    async function deletePoll(id) {
        const result = await fetchApi({ url: 'polls/' + id, method: 'DELETE' });
        if (result) {
            polls.value = polls.value.filter(p => p.id !== id);
        }
    }
    async function startPoll(id) {
        const result = await fetchApi({ url: `polls/${id}/start`, method: 'POST' });
        if (result !== false) {
            await fetchPolls()
        }
    }


    async function createPoll(data) {
        const result = await fetchApi({
            url: 'polls',
            method: 'POST',
            data,
        })

        if (result !== false) {
            await fetchPolls()
        }
    }

    async function fetchPollForEdit(id) {
        return await fetchApi({ url: `polls/${id}/edit`, method: 'GET' })
    }

    async function updatePoll(id, data) {
        const result = await fetchApi({
            url: `polls/${id}`,
            method: 'PUT',
            data,
        })

        if (result !== false) {
            await fetchPolls()
        }

        return result
    }

    return {
        polls, setPolls, fetchPolls, deletePoll, startPoll, createPoll,
        fetchPollForEdit, updatePoll,
    }

}
