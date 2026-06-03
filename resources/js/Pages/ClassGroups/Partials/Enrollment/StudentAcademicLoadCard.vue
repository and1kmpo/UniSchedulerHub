<script setup>
defineProps({
    load: {
        type: Object,
        default: () => ({
            credits: 0,
            groups: 0,
            weekly_hours: 0,
            min_credits: 7,
            max_credits: 21,
            meets_minimum: true,
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

        <div class="mt-4 rounded-xl px-4 py-3 text-sm" :class="load.meets_minimum
            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
            : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'">
            <i :class="load.meets_minimum ? 'fa-solid fa-circle-check mr-2' : 'fa-solid fa-triangle-exclamation mr-2'"></i>
            <span v-if="load.meets_minimum">
                Meets minimum load of {{ load.min_credits }} credits
            </span>
            <span v-else>
                Below minimum load of {{ load.min_credits }} credits
            </span>
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

        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
            Regular range: {{ load.min_credits }}-{{ load.max_credits }} credits
        </p>
    </div>
</template>
