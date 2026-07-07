<script setup>
defineProps({
    note: {
        type: String,
        default: "Exports use the current search and filter criteria.",
    },

    actionsLabel: {
        type: String,
        default: "Report actions",
    },
});
</script>

<template>
    <section
        class="space-y-4 border-b border-border-light bg-slate-50 p-4 dark:border-border-dark dark:bg-surface-dark/50"
    >
        <div class="grid gap-3 lg:grid-cols-[minmax(18rem,24rem)_1fr]">
            <slot name="search" />

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <slot name="filters" />
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500 dark:text-zinc-400">
                {{ note }}
            </p>

            <div v-if="$slots.reset" class="flex justify-start sm:justify-end">
                <slot name="reset" />
            </div>
        </div>

        <div
            v-if="$slots.actions"
            class="flex flex-col gap-3 border-t border-border-light pt-4 dark:border-border-dark sm:flex-row sm:items-center sm:justify-between"
        >
            <span class="text-xs font-semibold uppercase text-slate-400 dark:text-zinc-500">
                {{ actionsLabel }}
            </span>

            <div class="flex flex-wrap gap-2 sm:justify-end">
                <slot name="actions" />
            </div>
        </div>
    </section>
</template>
