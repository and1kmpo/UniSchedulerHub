<script setup>
import { useForm } from '@inertiajs/vue3'
import { onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    program: {
        type: Object,
        default: () => ({
            name: '',
            description: ''
        })
    },
    mode: {
        type: String,
        default: 'create',
    }
})

const form = useForm({
    name: props.program.name,
    description: props.program.description,
})

const submit = () => {
    if (props.mode === 'edit') {
        router.put(route('programs.update', props.program.id), form)
    } else {
        router.post(route('programs.store'), form)
    }
}

watch(() => props.program, (newProgram) => {
    form.name = newProgram.name
    form.description = newProgram.description
})
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-lg shadow">
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" v-model="form.name"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-300"
                required />
            <span class="text-sm text-red-500" v-if="form.errors.name">{{ form.errors.name }}</span>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea v-model="form.description" rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-300"
                required></textarea>
            <span class="text-sm text-red-500" v-if="form.errors.description">{{ form.errors.description }}</span>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2 rounded shadow transition">
                {{ mode === 'edit' ? 'Update' : 'Create' }}
            </button>
        </div>
    </form>
</template>
