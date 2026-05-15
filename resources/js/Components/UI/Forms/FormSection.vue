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
    <div class="border-b border-gray-200 p-6 dark:border-gray-800">

        <!-- Header -->
        <div class="mb-6 flex items-start justify-between gap-4">

            <!-- Title & Description -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ title }}
                </h2>

                <p v-if="description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
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