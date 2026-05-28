<script setup>
defineProps({
    errors: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div v-if="Object.keys(errors).length"
        class="border-b border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-500/10">
        <div class="flex items-start gap-3">

            <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>

            <div>

                <h3 class="font-medium text-red-700 dark:text-red-300">
                    Please fix the following errors
                </h3>

                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-600 dark:text-red-300">
                    <li v-for="(error, key) in errors" :key="key">
                        {{ error }}
                    </li>
                </ul>

            </div>

        </div>
    </div>
</template>