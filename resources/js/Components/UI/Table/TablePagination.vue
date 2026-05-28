<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    data: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div
        class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/80 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <p class="text-center text-sm text-gray-500 dark:text-gray-400 sm:text-left">
            Showing {{ data.from ?? 0 }}-{{ data.to ?? 0 }} of {{ data.total ?? 0 }}
        </p>

        <div
            class="mx-auto inline-flex items-center overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:mx-0">
            <Link v-if="data.prev_page_url" :href="data.prev_page_url"
                class="inline-flex h-10 w-11 items-center justify-center text-gray-700 transition hover:bg-gray-100 focus:outline-none dark:text-gray-200 dark:hover:bg-gray-700"
                aria-label="Previous page">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </Link>

            <span v-else class="inline-flex h-10 w-11 items-center justify-center text-gray-300 dark:text-gray-600"
                aria-hidden="true">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </span>

            <span
                class="border-x border-gray-200 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">
                {{ data.current_page }} / {{ data.last_page }}
            </span>

            <Link v-if="data.next_page_url" :href="data.next_page_url"
                class="inline-flex h-10 w-11 items-center justify-center text-gray-700 transition hover:bg-gray-100 focus:outline-none dark:text-gray-200 dark:hover:bg-gray-700"
                aria-label="Next page">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </Link>

            <span v-else class="inline-flex h-10 w-11 items-center justify-center text-gray-300 dark:text-gray-600"
                aria-hidden="true">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </span>
        </div>
    </div>
</template>
