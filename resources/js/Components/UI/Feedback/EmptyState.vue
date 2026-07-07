<script setup>
defineProps({
    title: {
        type: String,
        default: 'No data found',
    },

    description: {
        type: String,
        default: 'There is no information available.',
    },

    icon: {
        type: String,
        default: 'fa-solid fa-folder-open',
    },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center px-4 py-12 text-center sm:px-6 sm:py-16">
        <!-- Icon -->
        <div
            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300 sm:h-20 sm:w-20">
            <i :class="icon" class="text-3xl sm:text-4xl" />
        </div>

        <!-- Title -->
        <h2 class="text-lg font-semibold text-ink dark:text-white sm:text-xl">
            {{ title }}
        </h2>

        <!-- Description -->
        <p v-if="description"
            class="mt-2 max-w-md text-sm leading-relaxed text-slate-500 dark:text-zinc-400 sm:text-base">
            {{ description }}
        </p>

        <!-- Actions -->
        <div v-if="$slots.default" class="mt-6">
            <slot />
        </div>
    </div>
</template>
