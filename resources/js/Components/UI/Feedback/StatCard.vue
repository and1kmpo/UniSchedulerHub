<script setup>
defineProps({
    title: String,
    value: [String, Number],
    icon: String,
});
</script>

<template>
    <div
        class="grid min-h-[6.25rem] grid-cols-[1fr_auto] items-start gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5"
    >
        <div class="min-w-0 pr-1">
            <p class="text-xs font-medium leading-5 text-gray-500 dark:text-gray-400 sm:text-sm">
                {{ title }}
            </p>

            <h3 class="mt-2 break-words text-2xl font-semibold leading-tight text-gray-900 dark:text-white sm:text-3xl">
                {{ value }}
            </h3>
        </div>

        <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300 sm:h-11 sm:w-11"
        >
            <i :class="icon" class="text-base sm:text-lg"></i>
        </div>
    </div>
</template>
