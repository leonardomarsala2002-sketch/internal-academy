<script setup>
import { computed, ref, watch } from 'vue'

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

const dayPart = ref('')
const monthPart = ref('')
const yearPart = ref('')
const timePart = ref('09:00')
const isSyncingFromModel = ref(false)
const dayInputRef = ref(null)
const monthInputRef = ref(null)
const yearInputRef = ref(null)

function todayDateString() {
    const now = new Date()
    const yyyy = now.getFullYear()
    const mm = String(now.getMonth() + 1).padStart(2, '0')
    const dd = String(now.getDate()).padStart(2, '0')

    return `${yyyy}-${mm}-${dd}`
}

function currentTimeString() {
    const now = new Date()
    const hh = String(now.getHours()).padStart(2, '0')
    const mm = String(now.getMinutes()).padStart(2, '0')

    return `${hh}:${mm}`
}

function setNowTime() {
    timePart.value = currentTimeString()
}

function isoDateFromDate(date) {
    const yyyy = date.getFullYear()
    const mm = String(date.getMonth() + 1).padStart(2, '0')
    const dd = String(date.getDate()).padStart(2, '0')

    return `${yyyy}-${mm}-${dd}`
}

function applyIsoDate(isoDate) {
    if (!isoDate || !isoDate.includes('-')) {
        dayPart.value = ''
        monthPart.value = ''
        yearPart.value = ''
        return
    }

    const [yyyy, mm, dd] = isoDate.split('-')
    dayPart.value = dd
    monthPart.value = mm
    yearPart.value = yyyy
}

function setNowDateTime() {
    applyIsoDate(todayDateString())
    setNowTime()
}

function setTodayDate() {
    applyIsoDate(todayDateString())
}

function normalizeDigits(value, maxLength) {
    return value.replace(/\D/g, '').slice(0, maxLength)
}

function focusSegment(segment) {
    const map = {
        day: dayInputRef,
        month: monthInputRef,
        year: yearInputRef,
    }

    map[segment]?.value?.focus()
    map[segment]?.value?.select()
}

function nextSegment(segment) {
    if (segment === 'day') return 'month'
    if (segment === 'month') return 'year'
    return null
}

function prevSegment(segment) {
    if (segment === 'year') return 'month'
    if (segment === 'month') return 'day'
    return null
}

function segmentValue(segment) {
    if (segment === 'day') return dayPart.value
    if (segment === 'month') return monthPart.value
    return yearPart.value
}

function setSegmentValue(segment, value) {
    const normalized = normalizeDigits(value, segment === 'year' ? 4 : 2)
    if (segment === 'day') dayPart.value = normalized
    if (segment === 'month') monthPart.value = normalized
    if (segment === 'year') yearPart.value = normalized
}

function handleDateSegmentInput(segment, event) {
    setSegmentValue(segment, event.target.value)

    const maxLen = segment === 'year' ? 4 : 2
    if (segmentValue(segment).length === maxLen) {
        const next = nextSegment(segment)
        if (next) {
            focusSegment(next)
        }
    }
}

function handleDateSegmentKeydown(segment, event) {
    if (event.key === 'Backspace' && !segmentValue(segment)) {
        const prev = prevSegment(segment)
        if (prev) {
            event.preventDefault()
            focusSegment(prev)
        }
        return
    }

    if (event.key === '/' || event.key === '-' || event.key === '.' || event.key === ' ') {
        event.preventDefault()
        padSegmentOnDemand(segment)

        const next = nextSegment(segment)
        if (next) {
            focusSegment(next)
        }
    }
}

function handleDateSegmentPaste(segment, event) {
    const pasted = (event.clipboardData?.getData('text') ?? '').replace(/\D/g, '')
    if (!pasted) {
        return
    }

    event.preventDefault()

    const current = {
        day: dayPart.value,
        month: monthPart.value,
        year: yearPart.value,
    }

    const order = ['day', 'month', 'year']
    let startIndex = order.indexOf(segment)
    let remaining = pasted

    while (startIndex < order.length && remaining.length > 0) {
        const key = order[startIndex]
        const maxLen = key === 'year' ? 4 : 2
        const available = maxLen - current[key].length
        const chunk = remaining.slice(0, Math.max(available, 0))
        current[key] = (current[key] + chunk).slice(0, maxLen)
        remaining = remaining.slice(chunk.length)
        startIndex++
    }

    dayPart.value = current.day
    monthPart.value = current.month
    yearPart.value = current.year

    if (current.year.length === 4) {
        focusSegment('year')
    } else if (current.month.length === 2) {
        focusSegment('year')
    } else {
        focusSegment('month')
    }
}

function normalizeTimeInput() {
    timePart.value = timePart.value.replace(/[^\d:]/g, '').slice(0, 5)
}

function normalizeDayOnBlur() {
    if (!dayPart.value) {
        return
    }

    const value = Math.min(31, Math.max(1, Number(dayPart.value)))
    dayPart.value = String(value).padStart(2, '0')
}

function normalizeMonthOnBlur() {
    if (!monthPart.value) {
        return
    }

    const value = Math.min(12, Math.max(1, Number(monthPart.value)))
    monthPart.value = String(value).padStart(2, '0')
}

function normalizeYearOnBlur() {
    if (!yearPart.value) {
        return
    }

    if (yearPart.value.length === 2) {
        yearPart.value = `20${yearPart.value}`
    }
}

function padSegmentOnDemand(segment) {
    if (segment === 'day' && dayPart.value.length === 1) {
        dayPart.value = dayPart.value.padStart(2, '0')
    }

    if (segment === 'month' && monthPart.value.length === 1) {
        monthPart.value = monthPart.value.padStart(2, '0')
    }
}

function normalizeTimeOnBlur() {
    const compact = timePart.value.replace(/[^\d]/g, '')

    let hours = null
    let minutes = null

    if (compact.length >= 4) {
        hours = Number(compact.slice(0, 2))
        minutes = Number(compact.slice(2, 4))
    } else if (compact.length === 3) {
        hours = Number(compact.slice(0, 1))
        minutes = Number(compact.slice(1, 3))
    } else if (compact.length <= 2 && compact.length > 0) {
        hours = Number(compact)
        minutes = 0
    } else {
        const match = /^(\d{1,2}):(\d{1,2})$/.exec(timePart.value)
        if (!match) {
            return
        }

        hours = Number(match[1])
        minutes = Number(match[2])
    }

    if (hours === null || minutes === null) {
        return
    }

    hours = Math.min(23, Math.max(0, hours))
    minutes = Math.min(59, Math.max(0, minutes))

    timePart.value = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`
}

function parsedIsoDate() {
    if (!dayPart.value || !monthPart.value || !yearPart.value) {
        return ''
    }

    const dd = dayPart.value.padStart(2, '0')
    const mm = monthPart.value.padStart(2, '0')
    const yyyy = yearPart.value.length === 2 ? `20${yearPart.value}` : yearPart.value

    if (yyyy.length !== 4 || dd.length !== 2 || mm.length !== 2) {
        return ''
    }

    const date = new Date(Number(yyyy), Number(mm) - 1, Number(dd))
    if (
        Number.isNaN(date.getTime()) ||
        date.getFullYear() !== Number(yyyy) ||
        date.getMonth() !== Number(mm) - 1 ||
        date.getDate() !== Number(dd)
    ) {
        return ''
    }

    return `${yyyy}-${mm}-${dd}`
}

const selectedWeekday = computed(() => {
    const sourceDate = parsedIsoDate() || todayDateString()
    const year = Number(sourceDate.slice(0, 4))
    const month = Number(sourceDate.slice(5, 7)) - 1
    const day = Number(sourceDate.slice(8, 10))
    const date = new Date(year, month, day)

    const weekday = new Intl.DateTimeFormat('en-US', { weekday: 'long' }).format(date)
    return weekday.charAt(0).toUpperCase() + weekday.slice(1)
})

function shiftMinutes(delta) {
    const isoDate = parsedIsoDate()

    if (!isoDate || !/^\d{2}:\d{2}$/.test(timePart.value)) {
        setNowDateTime()
        return
    }

    const [hours, minutes] = timePart.value.split(':').map(Number)
    const date = new Date(
        Number(isoDate.slice(0, 4)),
        Number(isoDate.slice(5, 7)) - 1,
        Number(isoDate.slice(8, 10)),
        hours,
        minutes,
    )

    date.setMinutes(date.getMinutes() + delta)
    applyIsoDate(isoDateFromDate(date))

    const hh = String(date.getHours()).padStart(2, '0')
    const mm = String(date.getMinutes()).padStart(2, '0')
    timePart.value = `${hh}:${mm}`
}

function syncFromModelValue(value) {
    isSyncingFromModel.value = true

    if (!value || !value.includes('T')) {
        applyIsoDate('')
        timePart.value = '09:00'
        isSyncingFromModel.value = false
        return
    }

    const [date, time] = value.split('T')
    applyIsoDate(date)
    timePart.value = (time ?? '09:00').slice(0, 5)

    isSyncingFromModel.value = false
}

watch(() => props.modelValue, syncFromModelValue, { immediate: true })

watch([dayPart, monthPart, yearPart], ([newDay, newMonth, newYear], [oldDay, oldMonth, oldYear]) => {
    if (isSyncingFromModel.value) {
        return
    }

    const newIso = parsedIsoDate()
    const oldYearNormalized = oldYear && oldYear.length === 2 ? `20${oldYear}` : oldYear

    if (
        newIso &&
        newIso === todayDateString() &&
        (newDay !== oldDay || newMonth !== oldMonth || newYear !== oldYear)
    ) {
        if (`${oldYearNormalized}-${String(oldMonth || '').padStart(2, '0')}-${String(oldDay || '').padStart(2, '0')}` !== todayDateString()) {
            setNowTime()
        }
    }
})

watch([dayPart, monthPart, yearPart, timePart], () => {
    const isoDate = parsedIsoDate()

    if (!dayPart.value && !monthPart.value && !yearPart.value) {
        emit('update:modelValue', '')
        return
    }

    if (!isoDate || !/^\d{2}:\d{2}$/.test(timePart.value)) {
        return
    }

    emit('update:modelValue', `${isoDate}T${timePart.value}`)
})
</script>

<template>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ label }}</label>
        <div class="rounded-xl border border-gray-200 bg-gradient-to-b from-white to-gray-50 p-3 shadow-sm space-y-3">
            <p class="text-xs font-semibold tracking-wide text-gray-500">
                {{ selectedWeekday }}
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div class="min-w-0">
                    <p class="mb-1 text-xs font-medium text-gray-500">Date</p>
                    <div class="flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus-within:border-black focus-within:ring-2 focus-within:ring-black/10">
                        <input
                            ref="dayInputRef"
                            v-model="dayPart"
                            type="text"
                            inputmode="numeric"
                            maxlength="2"
                            placeholder="dd"
                            @input="handleDateSegmentInput('day', $event)"
                            @keydown="handleDateSegmentKeydown('day', $event)"
                            @paste="handleDateSegmentPaste('day', $event)"
                            @blur="normalizeDayOnBlur"
                            autocomplete="off"
                            class="w-8 border-0 bg-transparent p-0 text-center shadow-none ring-0 outline-none focus:border-0 focus:ring-0 focus:outline-none"
                        />
                        <span class="mx-1 text-gray-400">/</span>
                        <input
                            ref="monthInputRef"
                            v-model="monthPart"
                            type="text"
                            inputmode="numeric"
                            maxlength="2"
                            placeholder="mm"
                            @input="handleDateSegmentInput('month', $event)"
                            @keydown="handleDateSegmentKeydown('month', $event)"
                            @paste="handleDateSegmentPaste('month', $event)"
                            @blur="normalizeMonthOnBlur"
                            autocomplete="off"
                            class="w-8 border-0 bg-transparent p-0 text-center shadow-none ring-0 outline-none focus:border-0 focus:ring-0 focus:outline-none"
                        />
                        <span class="mx-1 text-gray-400">/</span>
                        <input
                            ref="yearInputRef"
                            v-model="yearPart"
                            type="text"
                            inputmode="numeric"
                            maxlength="4"
                            placeholder="yyyy"
                            @input="handleDateSegmentInput('year', $event)"
                            @keydown="handleDateSegmentKeydown('year', $event)"
                            @paste="handleDateSegmentPaste('year', $event)"
                            @blur="normalizeYearOnBlur"
                            autocomplete="off"
                            class="w-14 border-0 bg-transparent p-0 text-center shadow-none ring-0 outline-none focus:border-0 focus:ring-0 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="min-w-0">
                    <p class="mb-1 text-xs font-medium text-gray-500">Time</p>
                    <input
                        v-model="timePart"
                        type="text"
                        inputmode="numeric"
                        placeholder="hh:mm"
                        @input="normalizeTimeInput"
                        @blur="normalizeTimeOnBlur"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                    />
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="setTodayDate"
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition"
                >
                    Today
                </button>
                <button
                    type="button"
                    @click="shiftMinutes(-15)"
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition"
                >
                    -15m
                </button>
                <button
                    type="button"
                    @click="setNowDateTime"
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition"
                >
                    Now
                </button>
                <button
                    type="button"
                    @click="shiftMinutes(15)"
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition"
                >
                    +15m
                </button>
            </div>
        </div>
        <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
    </div>
</template>
