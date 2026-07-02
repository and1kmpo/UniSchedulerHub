<script setup>
import { computed, reactive, watch } from "vue";
import { router } from "@inertiajs/vue3";
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
    professors: {
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
        }),
    },
});

const filterForm = reactive({
    search: props.filters.search || "",
    academic_period_id: props.filters.academic_period_id || "",
    status: props.filters.status || "",
});

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(route("reports.professor-load.index"), filterPayload(), {
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
}

const csvExportUrl = computed(() => route("reports.professor-load.export", exportPayload()));

const periodOptions = props.options.periods.map((period) => ({
    label: period.name,
    value: period.id,
}));

const statusOptions = props.options.statuses;

function statusVariant(status) {
    return {
        draft: "gray",
        published: "success",
        cancelled: "danger",
        closed: "gray",
    }[status] || "gray";
}

function optionLabel(options, value) {
    return options.find((option) => String(option.value) === String(value))?.label || value;
}

function printReport() {
    printTableReport({
        title: "Professor Load Report",
        subtitle: "Assigned groups, active students, scheduled blocks and pending grades.",
        filters: [
            { label: "Search", value: filterForm.search },
            { label: "Academic period", value: optionLabel(periodOptions, filterForm.academic_period_id) },
            { label: "Group status", value: optionLabel(statusOptions, filterForm.status) },
        ],
        metrics: [
            { label: "Professors", value: props.summary.professors },
            { label: "Groups", value: props.summary.groups },
            { label: "Active students", value: props.summary.active_students },
            { label: "Scheduled blocks", value: props.summary.scheduled_blocks },
            { label: "Pending grades", value: props.summary.pending_grades },
        ],
        columns: [
            { key: "professor", label: "Professor" },
            { key: "group", label: "Group / Subject" },
            { key: "period", label: "Period" },
            { key: "status", label: "Status" },
            { key: "students", label: "Active students" },
            { key: "blocks", label: "Blocks" },
            { key: "pending", label: "Pending grades" },
        ],
        rows: props.professors.data.flatMap((professor) =>
            professor.groups.map((group) => ({
                professor: (professor.name || "-") + " / " + (professor.document || "-") + " / " + (professor.email || "-"),
                group: (group.code || "-") + " / " + (group.subject.name || "-"),
                period: group.period || "-",
                status: group.status || "-",
                students: group.active_students,
                blocks: group.scheduled_blocks,
                pending: group.pending_grades,
            }))
        ),
    });
}
</script>

<template>
    <CrudPageLayout
        title="Professor Load Report"
        subtitle="Assigned groups, active students, scheduled blocks and pending grades"
    >
        <template v-slot:actions>
            <BaseButton as="a" variant="secondary" :href="route('reports.index')">
                <i class="fa-solid fa-arrow-left mr-2" />
                Reports
            </BaseButton>
        </template>

        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-5">
                    <StatCard title="Professors" :value="summary.professors" icon="fa-solid fa-chalkboard-user" />
                    <StatCard title="Groups" :value="summary.groups" icon="fa-solid fa-layer-group" />
                    <StatCard title="Active Students" :value="summary.active_students" icon="fa-solid fa-users" />
                    <StatCard title="Schedule Blocks" :value="summary.scheduled_blocks" icon="fa-solid fa-calendar-days" />
                    <StatCard title="Pending Grades" :value="summary.pending_grades" icon="fa-solid fa-clipboard-check" />
                </section>

                <SectionCard>
                    <FilterPanel>
                        <template #search>
                            <TableSearch v-model="filterForm.search" placeholder="Search professor, document or email..." />
                        </template>

                        <template #filters>
                            <BaseSelect v-model="filterForm.academic_period_id" placeholder="Academic period" :options="periodOptions" />
                            <BaseSelect v-model="filterForm.status" placeholder="Group status" :options="statusOptions" />
                        </template>

                        <template #reset>
                            <BaseButton variant="secondary" @click="clearFilters">
                                <i class="fa-solid fa-rotate-left mr-2" />
                                Reset filters
                            </BaseButton>
                        </template>

                        <template #actions>
                            <BaseButton as="a" variant="success" :href="csvExportUrl">
                                <i class="fa-solid fa-file-csv mr-2" />
                                Export CSV
                            </BaseButton>

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
                            Teaching Load By Professor
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Each professor is grouped with their assigned class groups, students, schedule blocks and grading workload.
                        </p>
                    </div>

                    <div v-if="professors.data.length" class="divide-y divide-border-light dark:divide-border-dark">
                        <article v-for="professor in professors.data" :key="professor.id" class="p-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-ink dark:text-white">
                                        {{ professor.name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ professor.document }} / {{ professor.email }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
                                    <span class="rounded-lg border border-border-light bg-surface px-3 py-2 font-medium text-slate-700 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-200">
                                        {{ professor.groups_count }} groups
                                    </span>
                                    <span class="rounded-lg border border-border-light bg-surface px-3 py-2 font-medium text-slate-700 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-200">
                                        {{ professor.active_students }} students
                                    </span>
                                    <span class="rounded-lg border border-border-light bg-surface px-3 py-2 font-medium text-slate-700 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-200">
                                        {{ professor.scheduled_blocks }} blocks
                                    </span>
                                    <span class="rounded-lg bg-warning/10 px-3 py-2 font-medium text-amber-700 dark:bg-warning/10 dark:text-warning">
                                        {{ professor.pending_grades }} pending grades
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="border-b border-border-light text-left text-xs uppercase text-slate-500 dark:border-border-dark dark:text-slate-400">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Group</th>
                                            <th class="px-4 py-3 font-semibold">Subject</th>
                                            <th class="px-4 py-3 font-semibold">Period</th>
                                            <th class="px-4 py-3 font-semibold">Status</th>
                                            <th class="px-4 py-3 font-semibold">Students</th>
                                            <th class="px-4 py-3 font-semibold">Blocks</th>
                                            <th class="px-4 py-3 font-semibold">Pending Grades</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-light dark:divide-border-dark">
                                        <tr v-for="group in professor.groups" :key="group.id">
                                            <td class="px-4 py-4 font-medium text-ink dark:text-white">
                                                {{ group.code }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-medium text-ink dark:text-white">
                                                    {{ group.subject.code }} - {{ group.subject.name }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ group.subject.credits }} credits
                                                </p>
                                            </td>
                                            <td class="px-4 py-4 text-slate-700 dark:text-zinc-300">
                                                {{ group.period || "-" }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <StatusBadge :label="group.status" :variant="statusVariant(group.status)" />
                                            </td>
                                            <td class="px-4 py-4 text-slate-700 dark:text-zinc-300">
                                                {{ group.active_students }}
                                            </td>
                                            <td class="px-4 py-4 text-slate-700 dark:text-zinc-300">
                                                {{ group.scheduled_blocks }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <StatusBadge
                                                    :label="`${group.pending_grades} pending`"
                                                    :variant="group.pending_grades > 0 ? 'warning' : 'success'"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>

                    <div v-else class="p-6">
                        <EmptyState
                            title="No professor load found"
                            description="Try adjusting the filters or assign class groups to professors."
                            icon="fa-solid fa-chalkboard-user"
                        />
                    </div>

                    <TablePagination v-if="professors.data.length" :data="professors" />
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
