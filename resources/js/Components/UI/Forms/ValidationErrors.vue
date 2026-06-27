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
        class="border-b border-danger/20 bg-danger/10 p-4 dark:border-danger/30 dark:bg-danger/10">
        <div class="flex items-start gap-3">

            <i class="fa-solid fa-circle-exclamation mt-0.5 text-danger"></i>

            <div>

                <h3 class="font-medium text-danger">
                    Please fix the following errors
                </h3>

                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-danger">
                    <li v-for="(error, key) in errors" :key="key">
                        {{ error }}
                    </li>
                </ul>

            </div>

        </div>
    </div>
</template>
