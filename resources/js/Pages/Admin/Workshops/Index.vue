<script setup>
import { router } from '@inertiajs/vue3'
import { onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
    workshops: Array,
    stats: Object,
    registrationsPerWorkshop: Array,
})

const confirmingDelete = ref(null)
let pollingHandle = null

function deleteWorkshop(id) {
    router.delete(`/admin/workshops/${id}`, {
        onFinish: () => { confirmingDelete.value = null },
    })
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

function pollDashboard() {
    router.reload({
        only: ['stats', 'registrationsPerWorkshop', 'workshops'],
        preserveState: true,
        preserveScroll: true,
    })
}

function logout() {
    router.post('/logout')
}

onMounted(() => {
    pollingHandle = setInterval(pollDashboard, 10000)
})

onUnmounted(() => {
    if (pollingHandle) {
        clearInterval(pollingHandle)
    }
})
</script>

<template>
    <div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-6xl">
            <div class="mb-8 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Admin Dashboard</h1>
                    <p class="mt-1 text-sm text-slate-600">Monitor workshops, registrations and participation trends.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        href="/admin/workshops/create"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/25"
                    >
                        + Create Workshop
                    </a>
                    <button
                        @click="logout"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                    >
                        Logout
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">Total Workshops</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ stats.total_workshops }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">Confirmed Registrations</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ stats.total_confirmed_registrations }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">Waitlisted Registrations</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ stats.total_waitlisted_registrations }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">Most Popular Workshop</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">
                        {{ stats.most_popular_workshop?.title ?? 'N/A' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ stats.most_popular_workshop ? `${stats.most_popular_workshop.confirmed_registrations_count} confirmed` : 'No confirmed registrations yet' }}
                    </p>
                </div>
            </div>

            <div class="mb-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Registrations Per Workshop</h2>
                </div>

                <div v-if="registrationsPerWorkshop.length === 0" class="p-5 text-sm text-slate-500">
                    No workshop data yet.
                </div>

                <table v-else class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-5 py-3 font-medium">Workshop</th>
                            <th class="text-left px-5 py-3 font-medium">Start</th>
                            <th class="text-left px-5 py-3 font-medium">Capacity</th>
                            <th class="text-left px-5 py-3 font-medium">Confirmed</th>
                            <th class="text-left px-5 py-3 font-medium">Waitlisted</th>
                            <th class="text-left px-5 py-3 font-medium">Seats Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in registrationsPerWorkshop" :key="item.id" class="border-t border-slate-100">
                            <td class="px-5 py-3 text-slate-900">{{ item.title }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ formatDate(item.starts_at) }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ item.capacity }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ item.confirmed_registrations_count }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ item.waitlisted_registrations_count }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ item.seats_left }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="workshops.length === 0" class="py-16 text-center text-slate-400">
                No workshops yet.
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="w in workshops"
                    :key="w.id"
                    class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p class="font-semibold text-slate-900">{{ w.title }}</p>
                        <p class="mt-0.5 text-sm text-slate-500">
                            {{ formatDate(w.starts_at) }} - {{ w.capacity }} seats
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            :href="`/admin/workshops/${w.id}/edit`"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-900/15"
                        >
                            Edit
                        </a>
                        <button
                            @click="confirmingDelete = w.id"
                            class="rounded-lg border border-rose-200 px-3 py-1.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="confirmingDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="mx-4 w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="mb-2 text-lg font-semibold text-slate-900">Delete workshop?</h2>
                <p class="mb-6 text-sm text-slate-500">This action cannot be undone.</p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="confirmingDelete = null"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteWorkshop(confirmingDelete)"
                        class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
