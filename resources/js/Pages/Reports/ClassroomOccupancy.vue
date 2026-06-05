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
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import TableSearch from "@/Components/UI/Table/TableSearch.vue";
import { printTableReport } from "@/Components/Composables/usePrintableReport";

const props = defineProps({
    classrooms: {
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
            buildings: [],
            statuses: [],
        }),
    },
});

const filterForm = reactive({
    search: props.filters.search || "",
    building_id: props.filters.building_id || "",
    academic_period_id: props.filters.academic_period_id || "",
    status: props.filters.status || "",
});

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(route("reports.classroom-occupancy.index"), filterPayload(), {
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
        building_id: filterForm.building_id,
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
    filterForm.building_id = "";
    filterForm.academic_period_id = "";
    filterForm.status = "";
}

const csvExportUrl = computed(() => route("reports.classroom-occupancy.export", exportPayload()));

const buildingOptions = props.options.buildings.map((building) => ({
    label: building.name,
    value: building.id,
}));

const periodOptions = props.options.periods.map((period) => ({
    label: period.name,
    value: period.id,
}));

const statusOptions = props.options.statuses;

function statusVariant(status) {
    return {
        active: "success",
        inactive: "gray",
        maintenance: "warning",
    }[status] || "gray";
}

function utilizationVariant(value) {
    if (value >= 100) return "danger";
    if (value >= 85) return "warning";
    return "success";
}

function optionLabel(options, value) {
    return options.find((option) => String(option.value) === String(value))?.label || value;
}

function printReport() {
    printTableReport({
        title: "Classroom Occupancy Report",
        subtitle: "Classrooms, capacity, scheduled blocks, assigned groups and utilization.",
        filters: [
            { label: "Search", value: filterForm.search },
            { label: "Building", value: optionLabel(buildingOptions, filterForm.building_id) },
            { label: "Academic period", value: optionLabel(periodOptions, filterForm.academic_period_id) },
            { label: "Classroom status", value: optionLabel(statusOptions, filterForm.status) },
        ],
        metrics: [
            { label: "Classrooms", value: props.summary.classrooms },
            { label: "Capacity", value: props.summary.total_capacity },
            { label: "Blocks", value: props.summary.scheduled_blocks },
            { label: "Groups", value: props.summary.assigned_groups },
            { label: "Conflicts", value: props.summary.conflicts },
            { label: "Utilization", value: props.summary.average_utilization + "%" },
        ],
        columns: [
            { key: "building", label: "Building" },
            { key: "classroom", label: "Classroom" },
            { key: "capacity", label: "Capacity" },
            { key: "blocks", label: "Blocks" },
            { key: "groups", label: "Groups" },
            { key: "conflicts", label: "Conflicts" },
            { key: "utilization", label: "Utilization" },
        ],
        rows: props.classrooms.data.map((classroom) => ({
            building: classroom.building,
            classroom: classroom.name,
            capacity: classroom.capacity,
            blocks: classroom.scheduled_blocks,
            groups: classroom.assigned_groups,
            conflicts: classroom.conflicts,
            utilization: classroom.average_utilization + "%",
        })),
    });
}
</script>

<template>
    <CrudPageLayout
        title="Classroom Occupancy Report"
        subtitle="Classrooms, capacity, scheduled blocks, assigned groups and utilization"
    >
        <template v-slot:actions>
            <Link
                :href="route('reports.index')"
                class="inline-flex items-center justify-center rounded-xl bg-gray-200 px-4 py-2 text-sm font-medium text-gray-800 transition-all duration-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400"
            >
                <i class="fa-solid fa-arrow-left mr-2" />
                Reports
            </Link>
        </template>

        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-3 xl:grid-cols-6">
                    <StatCard title="Classrooms" :value="summary.classrooms" icon="fa-solid fa-door-open" />
                    <StatCard title="Capacity" :value="summary.total_capacity" icon="fa-solid fa-users" />
                    <StatCard title="Blocks" :value="summary.scheduled_blocks" icon="fa-solid fa-calendar-days" />
                    <StatCard title="Groups" :value="summary.assigned_groups" icon="fa-solid fa-layer-group" />
                    <StatCard title="Conflicts" :value="summary.conflicts" icon="fa-solid fa-triangle-exclamation" />
                    <StatCard title="Utilization" :value="summary.average_utilization + '%'" icon="fa-solid fa-chart-simple" />
                </section>

                <SectionCard>
                    <div class="space-y-4 border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                        <div class="grid gap-3 lg:grid-cols-[minmax(18rem,24rem)_1fr]">
                            <TableSearch v-model="filterForm.search" placeholder="Search classroom or building..." />

                            <div class="grid gap-3 md:grid-cols-3">
                                <BaseSelect v-model="filterForm.building_id" placeholder="Building" :options="buildingOptions" />
                                <BaseSelect v-model="filterForm.academic_period_id" placeholder="Academic period" :options="periodOptions" />
                                <BaseSelect v-model="filterForm.status" placeholder="Classroom status" :options="statusOptions" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Exports use the current search and filter criteria.
                            </p>
                            <BaseButton variant="secondary" @click="clearFilters">
                                <i class="fa-solid fa-rotate-left mr-2" />
                                Reset filters
                            </BaseButton>
                        </div>

                        <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                            <span class="text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">
                                Report actions
                            </span>

                            <div class="flex flex-wrap gap-2 sm:justify-end">
                                <a
                                    :href="csvExportUrl"
                                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-all duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                >
                                    <i class="fa-solid fa-file-csv mr-2" />
                                    Export CSV
                                </a>

                                <BaseButton variant="secondary" @click="printReport">
                                    <i class="fa-solid fa-print mr-2" />
                                    Print / PDF
                                </BaseButton>
                            </div>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Occupancy By Classroom
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Each classroom is grouped with its schedule blocks, groups, utilization and detected conflicts.
                        </p>
                    </div>

                    <div v-if="classrooms.data.length" class="divide-y divide-gray-100 dark:divide-gray-800">
                        <article v-for="classroom in classrooms.data" :key="classroom.id" class="p-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                            {{ classroom.name }}
                                        </h3>
                                        <StatusBadge :label="classroom.status" :variant="statusVariant(classroom.status)" />
                                        <StatusBadge
                                            :label="classroom.average_utilization + '% utilization'"
                                            :variant="utilizationVariant(classroom.average_utilization)"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ classroom.building }} / {{ classroom.capacity }} seats
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
                                    <span class="rounded-lg bg-gray-100 px-3 py-2 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        {{ classroom.scheduled_blocks }} blocks
                                    </span>
                                    <span class="rounded-lg bg-gray-100 px-3 py-2 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        {{ classroom.assigned_groups }} groups
                                    </span>
                                    <span class="rounded-lg bg-gray-100 px-3 py-2 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        {{ classroom.active_students }} students
                                    </span>
                                    <span class="rounded-lg bg-amber-50 px-3 py-2 font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                        {{ classroom.conflicts }} conflicts
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="border-b border-gray-100 text-left text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Time block</th>
                                            <th class="px-4 py-3 font-semibold">Group</th>
                                            <th class="px-4 py-3 font-semibold">Subject</th>
                                            <th class="px-4 py-3 font-semibold">Professor</th>
                                            <th class="px-4 py-3 font-semibold">Period</th>
                                            <th class="px-4 py-3 font-semibold">Utilization</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="schedule in classroom.schedules" :key="schedule.id">
                                            <td class="px-4 py-4">
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ schedule.day }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ schedule.time }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ schedule.group.code || 'No group' }}
                                                </p>
                                                <StatusBadge
                                                    v-if="schedule.conflict"
                                                    label="Conflict"
                                                    variant="danger"
                                                />
                                            </td>
                                            <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                                                {{ schedule.subject.code }} - {{ schedule.subject.name }}
                                            </td>
                                            <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                                                {{ schedule.professor || '-' }}
                                            </td>
                                            <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                                                {{ schedule.period || '-' }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <StatusBadge
                                                    :label="schedule.utilization + '%'"
                                                    :variant="utilizationVariant(schedule.utilization)"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p v-if="!classroom.schedules.length" class="py-4 text-sm text-gray-500 dark:text-gray-400">
                                    No scheduled blocks for this classroom with the current filters.
                                </p>
                            </div>
                        </article>
                    </div>

                    <div v-else class="p-6">
                        <EmptyState
                            title="No classrooms found"
                            description="Try adjusting the filters or create classrooms with valid building and capacity data."
                            icon="fa-solid fa-door-open"
                        />
                    </div>

                    <TablePagination v-if="classrooms.data.length" :data="classrooms" />
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
