<script setup>
import { useNotification } from '@/composables/useNotification'

const { notifications, dismiss } = useNotification()

const typeClasses = {
    success: 'bg-green-50 border-green-500 text-green-800',
    error:   'bg-red-50   border-red-500   text-red-800',
    warning: 'bg-yellow-50 border-yellow-500 text-yellow-800',
    info:    'bg-blue-50  border-blue-500  text-blue-800',
}
</script>

<template>
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-80 max-w-full pointer-events-none">
        <div
            v-for="notif in notifications"
            :key="notif.id"
            class="pointer-events-auto flex items-start justify-between gap-3 border-l-4 rounded px-4 py-3 shadow-md text-sm"
            :class="typeClasses[notif.type] ?? typeClasses.info"
        >
            <span>{{ notif.message }}</span>
            <button
                @click="dismiss(notif.id)"
                class="shrink-0 font-bold leading-none opacity-50 hover:opacity-100 transition-opacity"
            >✕</button>
        </div>
    </div>
</template>
