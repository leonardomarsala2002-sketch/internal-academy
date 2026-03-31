<script setup>
import { useForm } from '@inertiajs/vue3'
import PremiumDateTimeField from '@/Components/PremiumDateTimeField.vue'

const form = useForm({
    title: '',
    description: '',
    starts_at: '',
    ends_at: '',
    capacity: 1,
})

function submit() {
    form.post('/admin/workshops')
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 p-8">
        <div class="max-w-2xl mx-auto bg-white border border-gray-200 rounded-2xl shadow-sm p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Workshop</h1>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black"
                    />
                    <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black"
                    />
                    <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <PremiumDateTimeField
                        v-model="form.starts_at"
                        label="Starts At"
                        :error="form.errors.starts_at"
                    />
                    <PremiumDateTimeField
                        v-model="form.ends_at"
                        label="Ends At"
                        :error="form.errors.ends_at"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                    <input
                        v-model="form.capacity"
                        type="number"
                        min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black"
                    />
                    <p v-if="form.errors.capacity" class="text-red-500 text-xs mt-1">{{ form.errors.capacity }}</p>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-black text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-gray-800 disabled:opacity-50 transition"
                    >
                        {{ form.processing ? 'Saving...' : 'Create Workshop' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
