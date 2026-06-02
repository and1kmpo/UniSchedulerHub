<script setup>
defineProps({
    load: {
        type: Object,
        default: () => ({
            credits: 0,
            groups: 0,
            weekly_hours: 0,
        }),
    },
})
</script>

<template>
    <div class="
            rounded-2xl
            border
            border-gray-200
            bg-white
            p-6
            shadow-sm
            dark:border-gray-800
            dark:bg-gray-900
        ">
        <div class="flex items-center justify-between">

            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Academic Load
                </p>

                <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ load.credits }} Credits
                </h3>

            </div>

            <div class="
                    flex
                    h-14
                    w-14
                    items-center
                    justify-center
                    rounded-2xl
                    bg-indigo-100
                    text-indigo-600
                    dark:bg-indigo-500/10
                    dark:text-indigo-300
                ">
                <i class="fa-solid fa-graduation-cap text-xl"></i>
            </div>

        </div>

        <div class="mt-6 grid grid-cols-2 gap-4">

            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Groups
                </p>

                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ load.groups }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Weekly Hours
                </p>

                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ load.weekly_hours }}
                </p>
            </div>

        </div>
    </div>
</template>