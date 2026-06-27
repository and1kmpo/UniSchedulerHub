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

        <div class="h-10 w-10 animate-spin rounded-full border-4 border-brand-100 border-t-brand-600 dark:border-brand-500/20 dark:border-t-brand-300" />

        <p class="mt-4 text-sm text-slate-500 dark:text-zinc-400">
            {{ message }}
        </p>

    </div>
</template>
