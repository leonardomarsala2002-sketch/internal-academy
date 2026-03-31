<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        required: true,
    },
    error: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['update:modelValue'])

const datePart = ref('')
const timePart = ref('09:00')

const timeOptions = Array.from({ length: 48 }, (_, index) => {
    const hours = String(Math.floor(index / 2)).padStart(2, '0')
    const minutes = index % 2 === 0 ? '00' : '30'

    return `${hours}:${minutes}`
})

function syncFromModelValue(value) {
    if (!value || !value.includes('T')) {
        datePart.value = ''
        timePart.value = '09:00'
        return
    }

    const [date, time] = value.split('T')
    datePart.value = date
    timePart.value = (time ?? '09:00').slice(0, 5)
}

watch(() => props.modelValue, syncFromModelValue, { immediate: true })

watch([datePart, timePart], () => {
    if (!datePart.value) {
        emit('update:modelValue', '')
        return
    }

    emit('update:modelValue', `${datePart.value}T${timePart.value}`)
})
</script>

<template>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ label }}</label>
        <div class="rounded-xl border border-gray-200 bg-gradient-to-b from-white to-gray-50 p-3 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input
                    v-model="datePart"
                    type="date"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                />
                <select
                    v-model="timePart"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                >
                    <option v-for="time in timeOptions" :key="time" :value="time">
                        {{ time }}
                    </option>
                </select>
            </div>
        </div>
        <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
    </div>
</template>
