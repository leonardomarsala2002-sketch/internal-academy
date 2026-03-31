<script setup>
import { useForm } from '@inertiajs/vue3'
import PremiumDateTimeField from '@/Components/PremiumDateTimeField.vue'

const props = defineProps({ workshop: Object })

function toDatetimeLocal(value) {
    if (!value) {
        return ''
    }

    const date = new Date(value)
    const pad = (number) => String(number).padStart(2, '0')

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

const form = useForm({
    title: props.workshop.title,
    description: props.workshop.description,
    starts_at: toDatetimeLocal(props.workshop.starts_at),
    ends_at: toDatetimeLocal(props.workshop.ends_at),
    capacity: props.workshop.capacity,
})

function submit() {
    form.put(`/admin/workshops/${props.workshop.id}`)
}

function increaseCapacity() {
    form.capacity = Number(form.capacity || 1) + 1
}

function decreaseCapacity() {
    form.capacity = Math.max(1, Number(form.capacity || 1) - 1)
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 p-8">
        <div class="max-w-4xl mx-auto bg-white border border-gray-200 rounded-2xl shadow-sm p-8">
            <a href="/admin/workshops" class="text-sm text-gray-500 hover:text-gray-700 mb-6 inline-block">
                Back
            </a>

            <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Workshop</h1>

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

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
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
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="decreaseCapacity"
                            class="h-10 w-10 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition"
                        >
                            -
                        </button>
                        <input
                            v-model.number="form.capacity"
                            type="number"
                            min="1"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-center [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none focus:outline-none focus:ring-2 focus:ring-black"
                        />
                        <button
                            type="button"
                            @click="increaseCapacity"
                            class="h-10 w-10 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition"
                        >
                            +
                        </button>
                    </div>
                    <p v-if="form.errors.capacity" class="text-red-500 text-xs mt-1">{{ form.errors.capacity }}</p>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-black text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-gray-800 disabled:opacity-50 transition"
                    >
                        {{ form.processing ? 'Saving...' : 'Update Workshop' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
