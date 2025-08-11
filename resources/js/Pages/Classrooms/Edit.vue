<template>
    <AppLayout :title="`Edit Classroom — ${classroom.name}`">
        <template #header>
            <h1 class="text-2xl font-bold">Edit Classroom</h1>
        </template>

        <div class="max-w-xl mx-auto mt-6">
            <div class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <Form :classroom="classroom" :buildings="buildings" :submitText="'Update'"
                    :submitTextLoading="'Updating...'" @submit="handleSubmit" @cancel="cancel" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Form from './Form.vue'
import { useAlert } from '@/Components/Composables/useAlert'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    classroom: Object,
    buildings: Array
})

const { toastSuccess } = useAlert()

async function handleSubmit(form) {
    try {
        await axios.post(route('classrooms.update', props.classroom.id), {
            ...form.data(),
            _method: 'put'
        })
        toastSuccess('Classroom updated successfully')
        router.visit(route('classrooms.index'))
    } catch (error) {
        if (error.response?.status === 422) {
            form.errors = error.response.data.errors
        }
    }
}

function cancel() {
    router.visit(route('classrooms.index'))
}
</script>
