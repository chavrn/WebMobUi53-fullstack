import { ref } from 'vue'

const notifications = ref([])
let nextId = 0

export function useNotification() {
    function notify(message, type = 'error', duration = 4000) {
        const id = ++nextId
        notifications.value.push({ id, message, type })
        if (duration > 0) {
            setTimeout(() => dismiss(id), duration)
        }
    }

    function dismiss(id) {
        notifications.value = notifications.value.filter(n => n.id !== id)
    }

    return { notifications, notify, dismiss }
}
