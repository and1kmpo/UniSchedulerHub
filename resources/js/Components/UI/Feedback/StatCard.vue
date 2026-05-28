<script setup>
defineProps({
    title: String,
    value: [String, Number],
    icon: String,
});
</script>

<template>
    <div class="flex items-center justify-between rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ title }}
            </p>

            <h3 class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">
                {{ value }}
            </h3>
        </div>

        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-500/15">
            <i :class="icon" class="text-2xl text-indigo-600 dark:text-indigo-300"></i>
        </div>
    </div>
</template>
