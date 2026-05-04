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

    return {
        polls, setPolls, fetchPolls, deletePoll, startPoll, }

}
