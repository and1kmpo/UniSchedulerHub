<script setup>
defineProps({
    value: {
        type: String,
        required: true,
    },

    required: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
        {{ value }}

        <span v-if="required" class="ml-1 text-red-500">
            *
        </span>
    </label>
</template>