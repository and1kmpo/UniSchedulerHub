<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },

    subtitle: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <div class="flex w-full flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-ink dark:text-white">
                {{ title }}
            </h1>

            <p v-if="subtitle" class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                {{ subtitle }}
            </p>
        </div>

        <div v-if="$slots.actions" class="flex shrink-0 items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
