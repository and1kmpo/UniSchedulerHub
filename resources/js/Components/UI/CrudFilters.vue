<script setup>
defineProps({
    title: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <div
        class="flex flex-col gap-4 border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/60 sm:flex-row sm:items-center sm:justify-between">
        <div v-if="title">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ title }}
            </h3>
        </div>

        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <slot />
        </div>
    </div>
</template>