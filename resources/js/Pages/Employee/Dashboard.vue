<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
    workshops: Array,
})

function register(workshopId) {
    router.post(`/workshops/${workshopId}/register`)
}

function formatDate(value) {
    return new Date(value).toLocaleString('it-IT', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 p-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Employee Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Upcoming workshops</p>
            </div>

            <div
                v-if="workshops.length === 0"
                class="bg-white rounded-2xl border border-gray-200 p-8 text-gray-500"
            >
                No upcoming workshops.
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="workshop in workshops"
                    :key="workshop.id"
                    class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ workshop.title }}
                            </h2>

                            <p class="text-sm text-gray-600 mt-1">
                                {{ workshop.description }}
                            </p>

                            <div class="mt-4 space-y-1 text-sm text-gray-500">
                                <p><strong>Start:</strong> {{ formatDate(workshop.starts_at) }}</p>
                                <p><strong>End:</strong> {{ formatDate(workshop.ends_at) }}</p>
                                <p><strong>Capacity:</strong> {{ workshop.capacity }}</p>
                                <p><strong>Seats left:</strong> {{ workshop.seats_left }}</p>
                            </div>
                        </div>

                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <span
                                v-if="workshop.is_registered"
                                class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700"
                            >
                                Registered
                            </span>

                            <span
                                v-else-if="workshop.is_full"
                                class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700"
                            >
                                Full
                            </span>

                            <span
                                v-else
                                class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700"
                            >
                                Available
                            </span>

                            <button
                                v-if="!workshop.is_registered && !workshop.is_full"
                                @click="register(workshop.id)"
                                class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                Register
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>