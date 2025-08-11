<template>
    <form @submit.prevent="onSubmit">
        <!-- Name -->
        <div class="mb-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input v-model="form.name" type="text" class="input w-full" required />
            <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
        </div>

        <!-- Building -->
        <div class="mb-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Building</label>
            <select v-model="form.building_id" class="input w-full">
                <option value="">— None —</option>
                <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
            <div v-if="form.errors.building_id" class="text-red-600 text-sm mt-1">{{ form.errors.building_id }}</div>
        </div>

        <!-- Floor -->
        <div class="mb-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Floor</label>
            <input v-model="form.floor" type="number" min="0" class="input w-full" />
            <div v-if="form.errors.floor" class="text-red-600 text-sm mt-1">{{ form.errors.floor }}</div>
        </div>

        <!-- Capacity -->
        <div class="mb-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacity</label>
            <input v-model="form.capacity" type="number" min="1" class="input w-full" />
            <div v-if="form.errors.capacity" class="text-red-600 text-sm mt-1">{{ form.errors.capacity }}</div>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea v-model="form.description" class="input w-full"></textarea>
            <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end">
            <PrimaryButton type="submit" class="mr-2" :disabled="form.processing">
                <template v-if="form.processing">{{ submitTextLoading }}</template>
                <template v-else>{{ submitText }}</template>
            </PrimaryButton>
            <SecondaryButton @click="$emit('cancel')">Cancel</SecondaryButton>
        </div>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
    classroom: Object,
    buildings: Array,
    submitText: {
        type: String,
        default: 'Save'
    },
    submitTextLoading: {
        type: String,
        default: 'Saving...'
    }
})

const form = useForm({
    name: props.classroom?.name || '',
    building_id: props.classroom?.building_id || '',
    floor: props.classroom?.floor || '',
    capacity: props.classroom?.capacity || '',
    description: props.classroom?.description || ''
})

defineExpose({ form })

const emit = defineEmits(['submit', 'cancel'])

function onSubmit(event) {
    event.preventDefault()
    emit('submit', form)
}


</script>

<style scoped>
.input {
    @apply w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}
</style>
