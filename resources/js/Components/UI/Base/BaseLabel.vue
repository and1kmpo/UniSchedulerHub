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
    <label class="block text-sm font-medium text-slate-700 dark:text-zinc-200">
        {{ value }}

        <span v-if="required" class="ml-1 text-danger">
            *
        </span>
    </label>
</template>
