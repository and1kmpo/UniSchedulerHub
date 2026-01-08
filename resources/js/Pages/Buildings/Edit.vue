<script setup>
import { watch, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useAlert } from '@/Components/Composables/useAlert'
import axios from 'axios'
import SecondaryButton from '@/Components/SecondaryButton.vue'


const props = defineProps({
    building: Object
})

const { toastSuccess, toastError } = useAlert()

// inicializa el formulario con los datos existentes
const form = reactive({
    name: props.building.name,
    code: props.building.code,
    errors: {}
})


// si cambian las props, actualiza el form
watch(() => props.building, (b) => {
    form.name = b.name
    form.code = b.code
})

const submit = async () => {
    try {
        await axios.post(
            route('buildings.update', props.building.id),
            {
                name: form.name,
                code: form.code,
                _method: 'PATCH'
            }
        )
        toastSuccess('Building updated successfully')
        router.visit(route('buildings.index'))
    } catch (e) {
        // si viene validación de Laravel
        if (e.response?.status === 422) {
            form.errors = e.response.data.errors
            console.error('Status:', e.response.status)
            console.error('Data:', e.response.data)
        } else {
            toastError('Please check the fields')
            console.error('Message:', e.message)
        }
    }
}

const cancel = () => {
    router.visit(route('buildings.index'))
}
</script>

<template>
    <AppLayout :title="`Edit Building — ${building.name}`">
        <template #header>
            <h1 class="text-2xl font-bold">Edit Building</h1>
        </template>

        <div class="max-w-xl mx-auto mt-6">
            <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input v-model="form.name" type="text" class="input w-full" />
                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                    <input v-model="form.code" type="text" class="input w-full" />
                    <div v-if="form.errors.code" class="text-red-600 text-sm mt-1">{{ form.errors.code }}</div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary mr-2">
                        Update
                    </button>
                    <SecondaryButton @click="cancel">Cancel</SecondaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.input {
    @apply w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}

.btn-primary {
    @apply bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded transition;
}
</style>
