<script setup>
import { computed, reactive, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import FilterPanel from "@/Components/UI/Filters/FilterPanel.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import TableSearch from "@/Components/UI/Table/TableSearch.vue";
import { printTableReport } from "@/Components/Composables/usePrintableReport";

const props = defineProps({
    groups: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    options: {
        type: Object,
        default: () => ({
            periods: [],
            statuses: [],
            gradeStates: [],
        }),
    },
});

const filterForm = reactive({
    search: props.filters.search || "",
    academic_period_id: props.filters.academic_period_id || "",
    status: props.filters.status || "",
    grade_state: props.filters.grade_state || "",
});

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(route("reports.grade-operations.index"), filterPayload(), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    },
    { deep: true }
);

function filterPayload() {
    return {
        search: filterForm.search,
        academic_period_id: filterForm.academic_period_id,
        status: filterForm.status,
        grade_state: filterForm.grade_state,
        page: 1,
    };
}

function exportPayload() {
    const payload = { ...filterPayload() };
    delete payload.page;

    return Object.fromEntries(
        Object.entries(payload).filter(([, value]) => value !== "" && value !== null && value !== undefined)
    );
}

function clearFilters() {
    filterForm.search = "";
    filterForm.academic_period_id = "";
    filterForm.status = "";
    filterForm.grade_state = "";
}

const csvExportUrl = computed(() => route("reports.grade-operations.export", exportPayload()));

const periodOptions = props.options.periods.map((period) => ({
    label: period.name,
    value: period.id,
}));

const statusOptions = props.options.statuses;
const gradeStateOptions = props.options.gradeStates;

function optionLabel(options, value) {
    return options.find((option) => String(option.value) === String(value))?.label || value;
}

function statusVariant(status) {
    return {
        draft: "gray",
        published: "success",
        cancelled: "danger",
        closed: "gray",
    }[status] || "gray";
}

function progressVariant(value) {
    if (value >= 100) return "success";
    if (value >= 60) return "warning";
    return "danger";
}

function printReport() {
    printTableReport({
        title: "Grade Operations Report",
        subtitle: "Class groups, grade progress, pending grades and grade editing locks.",
        filters: [
            { label: "Search", value: filterForm.search },
            { label: "Academic period", value: optionLabel(periodOptions, filterForm.academic_period_id) },
            { label: "Group status", value: optionLabel(statusOptions, filterForm.status) },
            { label: "Grade state", value: optionLabel(gradeStateOptions, filterForm.grade_state) },
        ],
        metrics: [
            { label: "Groups", value: props.summary.groups },
            { label: "Active students", value: props.summary.active_students },
            { label: "Graded students", value: props.summary.graded_students },
            { label: "Pending grades", value: props.summary.pending_grades },
            { label: "Locked groups", value: props.summary.locked_groups },
            { label: "Completed groups", value: props.summary.completed_groups },
            { label: "Progress", value: props.summary.progress + "%" },
        ],
        columns: [
            { key: "group", label: "Group" },
            { key: "subject", label: "Subject" },
            { key: "professor", label: "Professor" },
            { key: "period", label: "Period" },
            { key: "active", label: "Active students" },
            { key: "graded", label: "Graded" },
            { key: "pending", label: "Pending" },
            { key: "progress", label: "Progress" },
            { key: "editing", label: "Grade editing" },
            { key: "reason", label: "Lock reason" },
        ],
        rows: props.groups.data.map((group) => ({
            group: group.code,
            subject: (group.subject.code || "-") + " - " + (group.subject.name || "-"),
            professor: group.professor,
            period: (group.period || "-") + " / " + group.period_status,
            active: group.active_students,
            graded: group.graded_students,
            pending: group.pending_grades,
            progress: group.progress + "%",
            editing: group.can_edit_grades ? "Open" : "Locked",
            reason: group.lock_reason || "-",
        })),
    });
}
</script>

<template>
    <CrudPageLayout
        title="Grade Operations Report"
        subtitle="Class groups, grade progress, pending grades and grade editing locks"
    >
        <template v-slot:actions>
            <Link
                :href="route('reports.index')"
                class="inline-flex items-center justify-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition-all duration-200 hover:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-600"
            >
                <i class="fa-solid fa-arrow-left mr-2" />
                Reports
            </Link>
        </template>

        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard title="Groups" :value="summary.groups" icon="fa-solid fa-layer-group" />
                    <StatCard title="Active Students" :value="summary.active_students" icon="fa-solid fa-users" />
                    <StatCard title="Graded Students" :value="summary.graded_students" icon="fa-solid fa-circle-check" />
                    <StatCard title="Pending Grades" :value="summary.pending_grades" icon="fa-solid fa-clipboard-question" />
                    <StatCard title="Locked Groups" :value="summary.locked_groups" icon="fa-solid fa-lock" />
                    <StatCard title="Completed Groups" :value="summary.completed_groups" icon="fa-solid fa-check-double" />
                    <StatCard title="Progress" :value="summary.progress + '%'" icon="fa-solid fa-chart-simple" />
                </section>

                <SectionCard>
                    <FilterPanel>
                        <template #search>
                            <TableSearch v-model="filterForm.search" placeholder="Search group, subject or professor..." />
                        </template>

                        <template #filters>
                            <BaseSelect v-model="filterForm.academic_period_id" placeholder="Academic period" :options="periodOptions" />
                            <BaseSelect v-model="filterForm.status" placeholder="Group status" :options="statusOptions" />
                            <BaseSelect v-model="filterForm.grade_state" placeholder="Grade state" :options="gradeStateOptions" />
                        </template>

                        <template #reset>
                            <BaseButton variant="secondary" @click="clearFilters">
                                <i class="fa-solid fa-rotate-left mr-2" />
                                Reset filters
                            </BaseButton>
                        </template>

                        <template #actions>
                            <a
                                :href="csvExportUrl"
                                class="inline-flex items-center justify-center rounded-lg bg-success px-4 py-2 text-sm font-medium text-white transition-all duration-200 hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-success"
                            >
                                <i class="fa-solid fa-file-csv mr-2" />
                                Export CSV
                            </a>

                            <BaseButton variant="secondary" @click="printReport">
                                <i class="fa-solid fa-print mr-2" />
                                Print / PDF
                            </BaseButton>
                        </template>
                    </FilterPanel>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-border-light p-6 dark:border-border-dark">
                        <h2 class="text-lg font-semibold text-ink dark:text-white">
                            Grading Progress By Group
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Use this report to identify grading workload, locked groups and pending evaluation actions.
                        </p>
                    </div>

                    <div v-if="groups.data.length" class="divide-y divide-gray-100 dark:divide-gray-800">
                        <article v-for="group in groups.data" :key="group.id" class="p-6">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-base font-semibold text-ink dark:text-white">
                                            {{ group.code }}
                                        </h3>
                                        <StatusBadge :label="group.status" :variant="statusVariant(group.status)" />
                                        <StatusBadge
                                            :label="group.can_edit_grades ? 'Grade editing open' : 'Grade editing locked'"
                                            :variant="group.can_edit_grades ? 'success' : 'warning'"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ group.subject.code }} - {{ group.subject.name }} / {{ group.professor }} / {{ group.period || "-" }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
                                    <span class="rounded-lg bg-slate-100 px-3 py-2 font-medium text-slate-700 dark:bg-zinc-900 dark:text-zinc-200">
                                        {{ group.active_students }} active
                                    </span>
                                    <span class="rounded-lg bg-slate-100 px-3 py-2 font-medium text-slate-700 dark:bg-zinc-900 dark:text-zinc-200">
                                        {{ group.graded_students }} graded
                                    </span>
                                    <span class="rounded-lg bg-warning/10 px-3 py-2 font-medium text-amber-700 dark:bg-warning/10 dark:text-warning">
                                        {{ group.pending_grades }} pending
                                    </span>
                                    <StatusBadge :label="group.progress + '% progress'" :variant="progressVariant(group.progress)" />
                                </div>
                            </div>

                            <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_1.2fr]">
                                <div class="rounded-lg border border-border-light p-4 dark:border-border-dark">
                                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                                        Academic period
                                    </p>
                                    <p class="mt-2 font-medium text-ink dark:text-white">
                                        {{ group.period || "No period" }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ group.period_status }}
                                    </p>
                                </div>

                                <div class="rounded-lg border border-border-light p-4 dark:border-border-dark">
                                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                                        Grade lock reason
                                    </p>
                                    <p class="mt-2 text-sm text-slate-700 dark:text-zinc-300">
                                        {{ group.lock_reason || "Grades are editable for this group." }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div v-else class="p-6">
                        <EmptyState
                            title="No grading operations found"
                            description="Try adjusting the filters or verify that groups have active enrolled students."
                            icon="fa-solid fa-clipboard-check"
                        />
                    </div>

                    <TablePagination v-if="groups.data.length" :data="groups" />
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
