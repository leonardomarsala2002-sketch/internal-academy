<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({ workshops: Array })

const confirmingDelete = ref(null)

function deleteWorkshop(id) {
    router.delete(`/admin/workshops/${id}`, {
        onFinish: () => { confirmingDelete.value = null }
    })
}

function formatDate(val) {
    return new Date(val).toLocaleString('it-IT', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 p-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Workshops</h1>
                <a href="/admin/workshops/create"
                    class="bg-black text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-800 transition">
                    + Create Workshop
                </a>
            </div>

            <div v-if="workshops.length === 0" class="text-center py-16 text-gray-400">
                No workshops yet.
            </div>

            <div v-else class="space-y-3">
                <div v-for="w in workshops" :key="w.id"
                    class="bg-white border border-gray-200 rounded-xl p-5 flex items-center justify-between shadow-sm">
                    <div>
                        <p class="font-semibold text-gray-900">{{ w.title }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ formatDate(w.starts_at) }} — {{ w.capacity }} seats
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="`/admin/workshops/${w.id}/edit`"
                            class="text-sm px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Edit
                        </a>
                        <button @click="confirmingDelete = w.id"
                            class="text-sm px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition">
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
                    <button @click="confirmingDelete = null"
                        class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="deleteWorkshop(confirmingDelete)"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>