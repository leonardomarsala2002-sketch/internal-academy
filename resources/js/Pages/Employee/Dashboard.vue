<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineProps({
    workshops: Array,
})

const page = usePage()
const flashSuccess = computed(() => page.props.flash?.success)
const registrationError = computed(() => page.props.errors?.registration)

function register(workshopId) {
    router.post(`/workshops/${workshopId}/register`)
}

function cancel(workshopId) {
    router.delete(`/workshops/${workshopId}/register`)
}

function stateLabel(state) {
    if (state === 'confirmed') return 'Confirmed'
    if (state === 'waitlisted') return 'Waitlisted'
    if (state === 'full') return 'Full'

    return 'Available'
}

function stateBadgeClass(state) {
    if (state === 'confirmed') return 'bg-blue-100 text-blue-700'
    if (state === 'waitlisted') return 'bg-amber-100 text-amber-700'
    if (state === 'full') return 'bg-red-100 text-red-700'

    return 'bg-green-100 text-green-700'
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

            <div v-if="flashSuccess" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ flashSuccess }}
            </div>

            <div v-if="registrationError" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ registrationError }}
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
                            <h2 class="text-lg font-semibold text-gray-900">{{ workshop.title }}</h2>
                            <p class="text-sm text-gray-600 mt-1">{{ workshop.description }}</p>

                            <div class="mt-4 space-y-1 text-sm text-gray-500">
                                <p><strong>Start:</strong> {{ formatDate(workshop.starts_at) }}</p>
                                <p><strong>End:</strong> {{ formatDate(workshop.ends_at) }}</p>
                                <p><strong>Capacity:</strong> {{ workshop.capacity }}</p>
                                <p><strong>Seats left:</strong> {{ workshop.seats_left }}</p>
                                <p><strong>Status:</strong> {{ stateLabel(workshop.state) }}</p>
                            </div>
                        </div>

                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                :class="stateBadgeClass(workshop.state)"
                            >
                                {{ stateLabel(workshop.state) }}
                            </span>

                            <button
                                v-if="workshop.state === 'available' || workshop.state === 'full'"
                                @click="register(workshop.id)"
                                class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                {{ workshop.state === 'full' ? 'Join Waitlist' : 'Register' }}
                            </button>

                            <button
                                v-else
                                @click="cancel(workshop.id)"
                                class="px-4 py-2 text-sm font-medium bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
