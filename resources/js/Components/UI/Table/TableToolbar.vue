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
        class="flex flex-col gap-4 border-b border-border-light bg-slate-50 p-4 dark:border-border-dark dark:bg-zinc-900/50 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center">
            <slot name="search" />

            <slot name="filters" />
        </div>

        <div class="flex items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
