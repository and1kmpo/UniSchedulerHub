<template>
    <form @submit.prevent="submit">
        <div class="mb-4">
            <label class="block font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input v-model="form.name" type="text" class="input" required />
        </div>

        <div class="mb-4">
            <label class="block font-medium text-gray-700 dark:text-gray-300">Building</label>
            <select v-model="form.building_id" class="input">
                <option value="">— None —</option>
                <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-medium text-gray-700 dark:text-gray-300">Floor</label>
            <input v-model="form.floor" type="number" min="0" class="input" />
        </div>

        <div class="mb-4">
            <label class="block font-medium text-gray-700 dark:text-gray-300">Capacity</label>
            <input v-model="form.capacity" type="number" min="1" class="input" />
        </div>

        <div class="mb-4">
            <label class="block font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea v-model="form.description" class="input"></textarea>
        </div>

        <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded shadow transition">
            Save
        </button>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    classroom: Object,
    buildings: Array,
})

const form = useForm({
    name: props.classroom?.name || '',
    building_id: props.classroom?.building_id || '',
    floor: props.classroom?.floor || '',
    capacity: props.classroom?.capacity || '',
    description: props.classroom?.description || '',
})

function submit() {
    if (props.classroom) {
        form.put(route('classrooms.update', props.classroom.id))
    } else {
        form.post(route('classrooms.store'))
    }
}
</script>
