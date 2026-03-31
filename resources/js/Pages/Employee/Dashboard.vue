<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'

defineProps({
    workshops: Array,
})

const page = usePage()
const flashSuccess = computed(() => page.props.flash?.success)
const registrationError = computed(() => page.props.errors?.registration)

const successToast = ref('')
const errorToast = ref('')

let successTimer = null
let errorTimer = null

watch(flashSuccess, (message) => {
    if (!message) {
        return
    }

    successToast.value = message

    if (successTimer) {
        clearTimeout(successTimer)
    }

    successTimer = setTimeout(() => {
        successToast.value = ''
    }, 3500)
}, { immediate: true })

watch(registrationError, (message) => {
    if (!message) {
        return
    }

    errorToast.value = message

    if (errorTimer) {
        clearTimeout(errorTimer)
    }

    errorTimer = setTimeout(() => {
        errorToast.value = ''
    }, 4500)
}, { immediate: true })

onBeforeUnmount(() => {
    if (successTimer) {
        clearTimeout(successTimer)
    }

    if (errorTimer) {
        clearTimeout(errorTimer)
    }
})

function register(workshopId) {
    router.post(`/workshops/${workshopId}/register`)
}

function cancel(workshopId) {
    router.delete(`/workshops/${workshopId}/register`)
}

function logout() {
    router.post('/logout')
}

function stateLabel(state) {
    if (state === 'confirmed') return 'Confirmed'
    if (state === 'waitlisted') return 'Waitlisted'
    if (state === 'full') return 'Full'

    return 'Available'
}

function stateBadgeClass(state) {
    if (state === 'confirmed') return 'bg-blue-100 text-blue-700 border-blue-200'
    if (state === 'waitlisted') return 'bg-amber-100 text-amber-700 border-amber-200'
    if (state === 'full') return 'bg-red-100 text-red-700 border-red-200'

    return 'bg-green-100 text-green-700 border-green-200'
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
            <div class="mb-8 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Employee Dashboard</h1>
                    <p class="text-sm text-gray-500 mt-1">Browse upcoming workshops and manage your registrations.</p>
                </div>
                <button
                    @click="logout"
                    class="h-10 px-4 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition"
                >
                    Logout
                </button>
            </div>

            <div class="mb-4 h-16">
                <div
                    v-if="successToast"
                    class="h-full rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 shadow-sm flex items-center"
                >
                    {{ successToast }}
                </div>

                <div
                    v-if="errorToast"
                    class="h-full rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm flex items-center"
                >
                    {{ errorToast }}
                </div>
            </div>

            <div
                v-if="workshops.length === 0"
                class="bg-white rounded-2xl border border-gray-200 p-10 text-center text-gray-500"
            >
                No upcoming workshops.
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="workshop in workshops"
                    :key="workshop.id"
                    class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm"
                >
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                        <div class="min-w-0">
                            <h2 class="text-xl font-semibold text-gray-900">{{ workshop.title }}</h2>
                            <p class="text-sm text-gray-600 mt-1">{{ workshop.description }}</p>

                            <div class="mt-5 space-y-2 text-sm text-gray-600">
                                <p><span class="font-medium text-gray-800">Start:</span> {{ formatDate(workshop.starts_at) }}</p>
                                <p><span class="font-medium text-gray-800">End:</span> {{ formatDate(workshop.ends_at) }}</p>
                                <p><span class="font-medium text-gray-800">Capacity:</span> {{ workshop.capacity }}</p>
                                <p><span class="font-medium text-gray-800">Seats left:</span> {{ workshop.seats_left }}</p>
                                <p><span class="font-medium text-gray-800">Status:</span> {{ stateLabel(workshop.state) }}</p>
                            </div>
                        </div>

                        <div class="shrink-0 flex flex-col items-stretch gap-2 min-w-[140px]">
                            <span
                                class="inline-flex justify-center items-center rounded-full border px-3 py-1 text-xs font-semibold"
                                :class="stateBadgeClass(workshop.state)"
                            >
                                {{ stateLabel(workshop.state) }}
                            </span>

                            <button
                                v-if="workshop.state === 'available' || workshop.state === 'full'"
                                @click="register(workshop.id)"
                                class="h-10 px-4 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                {{ workshop.state === 'full' ? 'Join Waitlist' : 'Register' }}
                            </button>

                            <button
                                v-else
                                @click="cancel(workshop.id)"
                                class="h-10 px-4 text-sm font-semibold bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
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
