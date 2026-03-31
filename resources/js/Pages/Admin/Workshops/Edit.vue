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
    <div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-5xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <a href="/admin/workshops" class="mb-6 inline-block text-sm font-medium text-slate-500 transition hover:text-slate-700">
                Back
            </a>

            <h1 class="mb-6 text-2xl font-bold tracking-tight text-slate-900">Edit Workshop</h1>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Title</label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 transition focus:border-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                    />
                    <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 transition focus:border-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/20"
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
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Capacity</label>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="decreaseCapacity"
                            class="h-10 w-10 rounded-lg border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                        >
                            -
                        </button>
                        <input
                            v-model.number="form.capacity"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-center text-sm text-slate-800 [appearance:textfield] transition focus:border-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/20 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                        />
                        <button
                            type="button"
                            @click="increaseCapacity"
                            class="h-10 w-10 rounded-lg border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-900/20"
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
                        class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Update Workshop' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
