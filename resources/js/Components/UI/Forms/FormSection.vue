<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },

    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="border-b border-border-light p-6 dark:border-border-dark">

        <!-- Header -->
        <div class="mb-6 flex items-start justify-between gap-4">

            <!-- Title & Description -->
            <div>
                <h2 class="text-lg font-semibold text-ink dark:text-white">
                    {{ title }}
                </h2>

                <p v-if="description" class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                    {{ description }}
                </p>
            </div>

            <!-- Actions -->
            <div v-if="$slots.actions" class="flex items-center gap-2">
                <slot name="actions" />
            </div>

        </div>

        <!-- Content -->
        <slot />

    </div>
</template>
