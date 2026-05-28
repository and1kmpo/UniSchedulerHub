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
    <section
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ title }}
            </h2>

            <p v-if="description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ description }}
            </p>
        </div>

        <div class="p-6">
            <slot />
        </div>
    </section>
</template>