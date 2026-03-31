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
    <div class="min-h-screen bg-gray-50 p-8">
        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
                <a
                    href="/admin/workshops/create"
                    class="bg-black text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                >
                    + Create Workshop
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Total Workshops</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_workshops }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Confirmed Registrations</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_confirmed_registrations }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Waitlisted Registrations</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_waitlisted_registrations }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Most Popular Workshop</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">
                        {{ stats.most_popular_workshop?.title ?? 'N/A' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ stats.most_popular_workshop ? `${stats.most_popular_workshop.confirmed_registrations_count} confirmed` : 'No confirmed registrations yet' }}
                    </p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-900">Registrations Per Workshop</h2>
                </div>

                <div v-if="registrationsPerWorkshop.length === 0" class="p-5 text-sm text-gray-500">
                    No workshop data yet.
                </div>

                <table v-else class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
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
                        <tr v-for="item in registrationsPerWorkshop" :key="item.id" class="border-t border-gray-100">
                            <td class="px-5 py-3 text-gray-900">{{ item.title }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ formatDate(item.starts_at) }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ item.capacity }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ item.confirmed_registrations_count }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ item.waitlisted_registrations_count }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ item.seats_left }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="workshops.length === 0" class="text-center py-16 text-gray-400">
                No workshops yet.
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="w in workshops"
                    :key="w.id"
                    class="bg-white border border-gray-200 rounded-xl p-5 flex items-center justify-between shadow-sm"
                >
                    <div>
                        <p class="font-semibold text-gray-900">{{ w.title }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ formatDate(w.starts_at) }} - {{ w.capacity }} seats
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            :href="`/admin/workshops/${w.id}/edit`"
                            class="text-sm px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                        >
                            Edit
                        </a>
                        <button
                            @click="confirmingDelete = w.id"
                            class="text-sm px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="confirmingDelete" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Delete workshop?</h2>
                <p class="text-sm text-gray-500 mb-6">This action cannot be undone.</p>
                <div class="flex gap-3 justify-end">
                    <button
                        @click="confirmingDelete = null"
                        class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteWorkshop(confirmingDelete)"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
