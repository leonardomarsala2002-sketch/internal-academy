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
    <div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-5xl">
            <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Employee Dashboard</h1>
                    <p class="mt-1 text-sm text-slate-600">Browse upcoming workshops and manage your registrations.</p>
                </div>
                <button
                    @click="logout"
                    class="h-10 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                >
                    Logout
                </button>
            </div>

            <div class="mb-4 h-16">
                <div
                    v-if="successToast"
                    class="flex h-full items-center rounded-xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm"
                >
                    {{ successToast }}
                </div>

                <div
                    v-if="errorToast"
                    class="flex h-full items-center rounded-xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm font-medium text-rose-700 shadow-sm"
                >
                    {{ errorToast }}
                </div>
            </div>

            <div
                v-if="workshops.length === 0"
                class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-500 shadow-sm"
            >
                No upcoming workshops.
            </div>

            <div v-else class="space-y-5">
                <div
                    v-for="workshop in workshops"
                    :key="workshop.id"
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-xl font-semibold text-slate-900">{{ workshop.title }}</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ workshop.description }}</p>

                            <div class="mt-5 grid gap-2 text-sm text-slate-700">
                                <p><span class="font-semibold text-slate-900">Start:</span> {{ formatDate(workshop.starts_at) }}</p>
                                <p><span class="font-semibold text-slate-900">End:</span> {{ formatDate(workshop.ends_at) }}</p>
                                <p><span class="font-semibold text-slate-900">Capacity:</span> {{ workshop.capacity }}</p>
                                <p><span class="font-semibold text-slate-900">Seats left:</span> {{ workshop.seats_left }}</p>
                                <p><span class="font-semibold text-slate-900">Status:</span> {{ stateLabel(workshop.state) }}</p>
                            </div>
                        </div>

                        <div class="shrink-0 min-w-[148px] space-y-2">
                            <span
                                class="inline-flex w-full items-center justify-center rounded-full border px-3 py-1 text-xs font-semibold tracking-wide"
                                :class="stateBadgeClass(workshop.state)"
                            >
                                {{ stateLabel(workshop.state) }}
                            </span>

                            <button
                                v-if="workshop.state === 'available' || workshop.state === 'full'"
                                @click="register(workshop.id)"
                                class="h-10 w-full rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/30"
                            >
                                {{ workshop.state === 'full' ? 'Join Waitlist' : 'Register' }}
                            </button>

                            <button
                                v-else
                                @click="cancel(workshop.id)"
                                class="h-10 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-900/15"
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
