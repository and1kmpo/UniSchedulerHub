<script setup>
defineProps({
    message: {
        type: String,
        default: "Loading...",
    },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16">

        <div class="h-10 w-10 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600" />

        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            {{ message }}
        </p>

    </div>
</template>