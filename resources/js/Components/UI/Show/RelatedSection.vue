<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },

    description: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <div
        class="overflow-hidden rounded-lg border border-border-light bg-surface shadow-sm dark:border-border-dark dark:bg-surface-dark">
        <div class="flex items-center justify-between border-b border-border-light px-6 py-5 dark:border-border-dark">
            <div>
                <h2 class="text-lg font-semibold text-ink dark:text-white">
                    {{ title }}
                </h2>

                <p v-if="description" class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                    {{ description }}
                </p>
            </div>

            <slot name="actions" />
        </div>

        <slot />
    </div>
</template>
