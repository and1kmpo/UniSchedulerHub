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
            alerts: [],
        }),
    },
});

const filterForm = reactive({
    search: props.filters.search || "",
    academic_period_id: props.filters.academic_period_id || "",
    status: props.filters.status || "",
    alert: props.filters.alert || "",
});

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(route("reports.group-capacity-conflicts.index"), filterPayload(), {
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
        alert: filterForm.alert,
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
    filterForm.alert = "";
}

const csvExportUrl = computed(() => route("reports.group-capacity-conflicts.export", exportPayload()));

const periodOptions = props.options.periods.map((period) => ({
    label: period.name,
    value: period.id,
}));

const statusOptions = props.options.statuses;
const alertOptions = props.options.alerts;

function statusVariant(status) {
    return {
        draft: "gray",
        published: "success",
        cancelled: "danger",
        closed: "gray",
    }[status] || "gray";
}

function utilizationVariant(value) {
    if (value >= 100) return "danger";
    if (value >= 85) return "warning";
    return "success";
}

function alertVariant(alert) {
    return {
        "Schedule conflict": "danger",
        Full: "danger",
        "Near capacity": "warning",
        "No schedule": "warning",
        "No capacity": "danger",
    }[alert] || "gray";
}

function optionLabel(options, value) {
    return options.find((option) => String(option.value) === String(value))?.label || value;
}

function printReport() {
    printTableReport({
        title: "Group Capacity And Conflict Report",
        subtitle: "Class groups, seats, utilization, schedule conflicts and operational alerts.",
        filters: [
            { label: "Search", value: filterForm.search },
            { label: "Academic period", value: optionLabel(periodOptions, filterForm.academic_period_id) },
            { label: "Group status", value: optionLabel(statusOptions, filterForm.status) },
            { label: "Operational alert", value: optionLabel(alertOptions, filterForm.alert) },
        ],
        metrics: [
            { label: "Groups", value: props.summary.groups },
            { label: "Active students", value: props.summary.active_students },
            { label: "Capacity", value: props.summary.total_capacity },
            { label: "Available seats", value: props.summary.available_seats },
            { label: "Full groups", value: props.summary.full_groups },
            { label: "Near capacity", value: props.summary.near_capacity },
            { label: "Conflicts", value: props.summary.conflicts },
            { label: "Utilization", value: props.summary.utilization + "%" },
        ],
        columns: [
            { key: "group", label: "Group" },
            { key: "subject", label: "Subject" },
            { key: "professor", label: "Professor" },
            { key: "period", label: "Period" },
            { key: "capacity", label: "Capacity" },
            { key: "students", label: "Students" },
            { key: "utilization", label: "Utilization" },
            { key: "alerts", label: "Alerts" },
        ],
        rows: props.groups.data.map((group) => ({
            group: group.code,
            subject: (group.subject.code || "-") + " - " + (group.subject.name || "-"),
            professor: group.professor,
            period: group.period || "-",
            capacity: group.capacity,
            students: group.active_students,
            utilization: group.utilization + "%",
            alerts: group.alerts.join(", ") || "No alerts",
        })),
    });
}
</script>

<template>
    <CrudPageLayout
        title="Group Capacity And Conflict Report"
        subtitle="Class groups, seats, utilization, schedule conflicts and operational alerts"
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
                    <StatCard title="Capacity" :value="summary.total_capacity" icon="fa-solid fa-chair" />
                    <StatCard title="Available Seats" :value="summary.available_seats" icon="fa-solid fa-door-open" />
                    <StatCard title="Full" :value="summary.full_groups" icon="fa-solid fa-circle-exclamation" />
                    <StatCard title="Near Capacity" :value="summary.near_capacity" icon="fa-solid fa-gauge-high" />
                    <StatCard title="Conflicts" :value="summary.conflicts" icon="fa-solid fa-triangle-exclamation" />
                    <StatCard title="Utilization" :value="summary.utilization + '%'" icon="fa-solid fa-chart-simple" />
                </section>

                <SectionCard>
                    <FilterPanel>
                        <template #search>
                            <TableSearch v-model="filterForm.search" placeholder="Search group, subject or professor..." />
                        </template>

                        <template #filters>
                            <BaseSelect v-model="filterForm.academic_period_id" placeholder="Academic period" :options="periodOptions" />
                            <BaseSelect v-model="filterForm.status" placeholder="Group status" :options="statusOptions" />
                            <BaseSelect v-model="filterForm.alert" placeholder="Operational alert" :options="alertOptions" />
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
                            Operational Status By Group
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Review seats, schedule blocks, conflicts and capacity alerts before enrollment or publication decisions.
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
                                        <StatusBadge :label="group.utilization + '% utilization'" :variant="utilizationVariant(group.utilization)" />
                                    </div>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ group.subject.code }} - {{ group.subject.name }} / {{ group.professor }} / {{ group.period || "-" }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
                                    <span class="rounded-lg bg-slate-100 px-3 py-2 font-medium text-slate-700 dark:bg-zinc-900 dark:text-zinc-200">
                                        {{ group.active_students }}/{{ group.capacity }} students
                                    </span>
                                    <span class="rounded-lg bg-slate-100 px-3 py-2 font-medium text-slate-700 dark:bg-zinc-900 dark:text-zinc-200">
                                        {{ group.available_seats }} seats
                                    </span>
                                    <span class="rounded-lg bg-slate-100 px-3 py-2 font-medium text-slate-700 dark:bg-zinc-900 dark:text-zinc-200">
                                        {{ group.scheduled_blocks }} blocks
                                    </span>
                                    <span class="rounded-lg bg-warning/10 px-3 py-2 font-medium text-amber-700 dark:bg-warning/10 dark:text-warning">
                                        {{ group.conflict_blocks }} conflicts
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <StatusBadge
                                    v-for="alert in group.alerts"
                                    :key="alert"
                                    :label="alert"
                                    :variant="alertVariant(alert)"
                                />
                                <span v-if="!group.alerts.length" class="text-sm text-slate-500 dark:text-slate-400">
                                    No operational alerts.
                                </span>
                            </div>

                            <div class="mt-5 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="border-b border-slate-100 text-left text-xs uppercase text-slate-500 dark:border-border-dark dark:text-slate-400">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Time block</th>
                                            <th class="px-4 py-3 font-semibold">Classroom</th>
                                            <th class="px-4 py-3 font-semibold">Building</th>
                                            <th class="px-4 py-3 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="schedule in group.schedules" :key="schedule.id">
                                            <td class="px-4 py-4">
                                                <p class="font-medium text-ink dark:text-white">
                                                    {{ schedule.day }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ schedule.time }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4 text-slate-700 dark:text-zinc-300">
                                                {{ schedule.classroom }}
                                            </td>
                                            <td class="px-4 py-4 text-slate-700 dark:text-zinc-300">
                                                {{ schedule.building }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <StatusBadge
                                                    :label="schedule.conflict ? 'Conflict' : 'Clear'"
                                                    :variant="schedule.conflict ? 'danger' : 'success'"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p v-if="!group.schedules.length" class="py-4 text-sm text-slate-500 dark:text-slate-400">
                                    No published schedule blocks for this group.
                                </p>
                            </div>
                        </article>
                    </div>

                    <div v-else class="p-6">
                        <EmptyState
                            title="No groups found"
                            description="Try adjusting the filters or publish class groups with capacity and schedule data."
                            icon="fa-solid fa-triangle-exclamation"
                        />
                    </div>

                    <TablePagination v-if="groups.data.length" :data="groups" />
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
