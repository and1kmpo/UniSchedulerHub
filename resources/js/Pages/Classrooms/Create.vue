<template>
    <AppLayout>
        <template #header>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">New Classroom</h1>
        </template>

        <div class="max-w-xl mx-auto mt-6">
            <div class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <Form :buildings="buildings" :submitText="'Create'" :submitTextLoading="'Creating...'"
                    @submit="handleSubmit" @cancel="cancel" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Form from './Form.vue'
import { useAlert } from '@/Components/Composables/useAlert'
import { router } from '@inertiajs/vue3'

defineProps({ buildings: Array })

const { toastSuccess } = useAlert()

function handleSubmit(classroomForm) {
    if (typeof classroomForm.post !== 'function') {
        console.error('classroomForm no tiene .post()')
        console.log(classroomForm)
        return
    }

    classroomForm.post(route('classrooms.store'), {
        onSuccess: () => {
            toastSuccess('Classroom created successfully')
            router.visit(route('classrooms.index'))
        }
    })
}


function cancel() {
    router.visit(route('classrooms.index'))
}
</script>
