<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    title: '',
    description: '',
    starts_at: '',
    ends_at: '',
    capacity: ''
})

function submit() {
    form.post('/admin/workshops')
}
</script>

<template>
    <div style="padding: 40px; max-width: 500px; margin: 0 auto;">
        <h1 style="margin-bottom: 20px;">Create Workshop</h1>

        <form @submit.prevent="submit">

            <!-- TITLE -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">
                    Title
                </label>

                <input
                    v-model="form.title"
                    placeholder="Workshop title"
                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;"
                />

                <div v-if="form.errors.title" style="color: red; margin-top: 5px;">
                    {{ form.errors.title }}
                </div>
            </div>

            <!-- DESCRIPTION -->
            <div style="margin-bottom: 20px;">
                <label>Description</label>

                <textarea
                    v-model="form.description"
                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;"
                ></textarea>

                <div v-if="form.errors.description" style="color: red;">
                    {{ form.errors.description }}
                </div>
            </div>

            <!-- START DATE -->
            <div style="margin-bottom: 20px;">
                <label>Start Date</label>

                <input type="date" v-model="form.starts_at" />

                <div v-if="form.errors.starts_at" style="color: red;">
                    {{ form.errors.starts_at }}
                </div>
            </div>

            <!-- END DATE -->
            <div style="margin-bottom: 20px;">
                <label>End Date</label>

                <input type="date" v-model="form.ends_at" />

                <div v-if="form.errors.ends_at" style="color: red;">
                    {{ form.errors.ends_at }}
                </div>
            </div>

            <!-- CAPACITY -->
            <div style="margin-bottom: 20px;">
                <label>Capacity</label>

                <input type="number" v-model="form.capacity" />

                <div v-if="form.errors.capacity" style="color: red;">
                    {{ form.errors.capacity }}
                </div>
            </div>

            <!-- BUTTON -->
            <button
                type="submit"
                style="padding: 10px 15px; background: black; color: white; border: none; border-radius: 6px; cursor: pointer;"
            >
                Save
            </button>

        </form>
    </div>
</template>