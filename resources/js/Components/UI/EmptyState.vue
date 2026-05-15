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
            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 sm:h-20 sm:w-20">
            <i :class="icon" class="text-3xl text-gray-400 dark:text-gray-500 sm:text-4xl" />
        </div>

        <!-- Title -->
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200 sm:text-xl">
            {{ title }}
        </h2>

        <!-- Description -->
        <p v-if="description"
            class="mt-2 max-w-md text-sm leading-relaxed text-gray-500 dark:text-gray-400 sm:text-base">
            {{ description }}
        </p>

        <!-- Actions -->
        <div v-if="$slots.default" class="mt-6">
            <slot />
        </div>
    </div>
</template>