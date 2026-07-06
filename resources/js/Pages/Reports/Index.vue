<script setup>
import { computed, ref } from "vue";
import { route } from "ziggy-js";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import ContextHelp from "@/Components/UI/Feedback/ContextHelp.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import TableSearch from "@/Components/UI/Table/TableSearch.vue";

const props = defineProps({
    reports: {
        type: Array,
        default: () => [],
    },
});

const search = ref("");
const selectedCategory = ref("");

const categoryOptions = computed(() => {
    const categories = [...new Set(props.reports.map((report) => report.category).filter(Boolean))];

    return categories.map((category) => ({
        label: category,
        value: category,
    }));
});

const filteredReports = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.reports.filter((report) => {
        const matchesCategory = !selectedCategory.value || report.category === selectedCategory.value;
        const searchableText = [
            report.title,
            report.description,
            report.category,
        ].join(" ").toLowerCase();

        return matchesCategory && (!term || searchableText.includes(term));
    });
});

function resetFilters() {
    search.value = "";
    selectedCategory.value = "";
}
</script>

<template>
    <CrudPageLayout
        title="Reports"
        subtitle="Select an academic report to analyze, export or print operational data"
    >
        <CrudContainer>
            <SectionCard class="mb-6">
                <div class="space-y-5 p-5">
                    <ContextHelp
                        title="How to use the report bank"
                        description="Use reports for detailed analysis, exports and print-ready evidence. Dashboards show operational status; reports answer specific institutional questions."
                        icon="fa-solid fa-compass"
                    />

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-ink dark:text-white">
                                Report Bank
                            </h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Search by operational goal, category, actor or report name.
                            </p>
                        </div>

                        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-200">
                            <i class="fa-solid fa-chart-line" />
                            {{ reports.length }} reports available
                        </span>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px_auto] lg:items-end">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-zinc-200">
                                Search
                            </label>
                            <TableSearch
                                v-model="search"
                                placeholder="Search reports..."
                            />
                        </div>

                        <BaseSelect
                            v-model="selectedCategory"
                            label="Category"
                            placeholder="All categories"
                            :options="categoryOptions"
                        />

                        <BaseButton
                            variant="secondary"
                            class="w-full lg:w-auto"
                            :disabled="!search && !selectedCategory"
                            @click="resetFilters"
                        >
                            <i class="fa-solid fa-rotate-left mr-2" />
                            Reset
                        </BaseButton>
                    </div>
                </div>
            </SectionCard>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <SectionCard v-for="report in filteredReports" :key="report.route">
                    <div class="flex h-full flex-col p-6">
                        <div class="flex items-start justify-between gap-4">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                <i :class="report.icon" />
                            </span>
                            <span class="rounded-full border border-border-light bg-surface px-3 py-1 text-right text-xs font-semibold text-slate-600 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-300">
                                {{ report.category }}
                            </span>
                        </div>

                        <div class="mt-5 flex-1">
                            <h2 class="text-base font-semibold text-ink dark:text-white">
                                {{ report.title }}
                            </h2>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                {{ report.description }}
                            </p>
                        </div>

                        <div class="mt-6">
                            <BaseButton
                                as="a"
                                variant="primary"
                                class="w-full"
                                :href="route(report.route)"
                            >
                                <i class="fa-solid fa-arrow-right mr-2" />
                                Open report
                            </BaseButton>
                        </div>
                    </div>
                </SectionCard>
            </div>

            <EmptyState
                v-if="filteredReports.length === 0"
                title="No reports found"
                description="Adjust the search or category filters to find an available academic report."
                icon="fa-solid fa-file-circle-question"
            />
        </CrudContainer>
    </CrudPageLayout>
</template>
