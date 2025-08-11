<template>
    <AppLayout :title="`Edit Classroom — ${classroom.name}`">
        <template #header>
            <h1 class="text-2xl font-bold">Edit Classroom</h1>
        </template>

        <div class="max-w-xl mx-auto mt-6">
            <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input v-model="form.name" type="text" class="input w-full" required />
                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <!-- Building -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Building</label>
                    <select v-model="form.building_id" class="input w-full">
                        <option value="">— None —</option>
                        <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                    <div v-if="form.errors.building_id" class="text-red-600 text-sm mt-1">
                        {{ form.errors.building_id }}
                    </div>
                </div>

                <!-- Floor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Floor</label>
                    <input v-model="form.floor" type="number" min="0" class="input w-full" />
                    <div v-if="form.errors.floor" class="text-red-600 text-sm mt-1">{{ form.errors.floor }}</div>
                </div>

                <!-- Capacity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity</label>
                    <input v-model="form.capacity" type="number" min="1" class="input w-full" />
                    <div v-if="form.errors.capacity" class="text-red-600 text-sm mt-1">{{ form.errors.capacity }}</div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea v-model="form.description" class="input w-full"></textarea>
                    <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
                        {{ form.errors.description }}
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end">
                    <PrimaryButton type="submit" class="mr-2" :disabled="form.processing">
                        <template v-if="form.processing">Updating...</template>
                        <template v-else>Update</template>
                    </PrimaryButton>

                    <SecondaryButton @click="cancel">Cancel</SecondaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { useAlert } from '@/Components/Composables/useAlert'
import axios from 'axios'

const props = defineProps({
    classroom: Object,
    buildings: Array
})

const { toastSuccess } = useAlert()

const form = useForm({
    name: props.classroom?.name || '',
    building_id: props.classroom?.building_id || '',
    floor: props.classroom?.floor || '',
    capacity: props.classroom?.capacity || '',
    description: props.classroom?.description || ''
})

async function submit() {
    form.processing = true
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
    } finally {
        form.processing = false
    }
}

function cancel() {
    router.visit(route('classrooms.index'))
}
</script>

<style scoped>
.input {
    @apply w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}

.btn-primary {
    @apply bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded transition;
}
</style>
