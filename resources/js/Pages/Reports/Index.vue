<script setup>
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

defineProps({
    reports: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <CrudPageLayout
        title="Reports"
        subtitle="Select an academic report to analyze, export or print operational data"
    >
        <CrudContainer>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <SectionCard v-for="report in reports" :key="report.route">
                    <div class="flex h-full flex-col p-6">
                        <div class="flex items-start justify-between gap-4">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <i :class="report.icon" />
                            </span>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ report.category }}
                            </span>
                        </div>

                        <div class="mt-5 flex-1">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ report.title }}
                            </h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ report.description }}
                            </p>
                        </div>

                        <div class="mt-6">
                            <Link
                                :href="route(report.route)"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <i class="fa-solid fa-arrow-right mr-2" />
                                Open report
                            </Link>
                        </div>
                    </div>
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
