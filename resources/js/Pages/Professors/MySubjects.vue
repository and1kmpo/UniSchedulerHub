<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import { formatTime } from "@/Components/Composables/useDateTimeFormatter";

const props = defineProps({
    groups: {
        type: Array,
        default: () => [],
    },

    period: {
        type: Object,
        default: null,
    },

    summary: {
        type: Object,
        default: () => ({
            groups: 0,
            students: 0,
            credits: 0,
        }),
    },

    systemState: {
        type: String,
        default: "ready",
    },
});

const columns = [
    { key: "subject", label: "Subject" },
    { key: "code", label: "Group" },
    { key: "schedule_summary", label: "Schedule" },
    { key: "modality_summary", label: "Mode" },
    { key: "students_summary", label: "Students" },
    { key: "grade_summary", label: "Grades" },
    { key: "status", label: "Status" },
];

const rows = computed(() =>
    props.groups.map((group) => ({
        id: group.id,
        subject: `${group.subject?.code ?? "N/A"} - ${group.subject?.name ?? "N/A"}`,
        code: group.code ?? group.name,
        schedule_summary: formatSchedules(group.schedules),
        modality_summary: `${formatLabel(group.modality)} / ${formatLabel(group.shift)}`,
        students_summary: `${group.subject_enrollments_count}/${group.capacity}`,
        grade_summary: formatGradeProgress(group.subject_enrollments),
        status: group.status,
        source: group,
    }))
);

function formatDay(day) {
    return day ? day.charAt(0).toUpperCase() + day.slice(1) : "";
}

function formatStatus(status) {
    return status ? status.replaceAll("_", " ").toUpperCase() : "PENDING";
}

function formatLabel(value) {
    return value ? value.replaceAll("_", " ").toUpperCase() : "TBD";
}

function formatSchedules(schedules = []) {
    if (!schedules.length) {
        return "Pending";
    }

    return schedules
        .map((schedule) => {
            const room = (schedule.classroom_location || schedule.classroom)
                ? ` - ${schedule.classroom_location || schedule.classroom}`
                : "";

            return `${formatDay(schedule.day)} ${formatTime(schedule.start_time)}-${formatTime(schedule.end_time)}${room}`;
        })
        .join("; ");
}

function formatGradeProgress(enrollments = []) {
    if (!enrollments.length) {
        return "No students";
    }

    const graded = enrollments.filter((enrollment) =>
        enrollment.grade?.final_grade !== null && enrollment.grade?.final_grade !== undefined
    ).length;

    return `${graded}/${enrollments.length} graded`;
}

const groupStatusVariant = (status) => ({
    draft: "warning",
    published: "success",
    cancelled: "danger",
    closed: "gray",
}[status] || "gray");
</script>

<template>
    <CrudPageLayout title="My Class Groups" :subtitle="period
        ? `Teaching workspace for ${period.name}`
        : 'Teaching workspace for the active academic period'
        ">
        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <StatCard title="Assigned Groups" :value="summary.groups" icon="fa-solid fa-users-rectangle" />
                    <StatCard title="Active Students" :value="summary.students" icon="fa-solid fa-user-graduate" />
                    <StatCard title="Credits Assigned" :value="summary.credits" icon="fa-solid fa-layer-group" />
                </section>

                <SectionCard>
                    <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Current Academic Period
                            </h2>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ period?.name ?? "No active academic period" }}
                            </p>
                        </div>

                        <StatusBadge :label="period?.state ? formatStatus(period.state) : 'NOT ACTIVE'"
                            :variant="period?.can_edit_grades ? 'success' : 'gray'" />
                    </div>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Assigned Class Groups
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Teaching groups, schedules, enrolled students and grade entry for the active period.
                        </p>
                    </div>

                    <div class="p-6">
                        <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                            <template #cell-schedule_summary="{ value }">
                                <span class="block max-w-md whitespace-normal text-sm text-gray-700 dark:text-gray-300">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-modality_summary="{ value }">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-students_summary="{ value }">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-grade_summary="{ value }">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-status="{ value }">
                                <StatusBadge :label="formatStatus(value)" :variant="groupStatusVariant(value)" />
                            </template>

                            <template #actions="{ row }">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <Link :href="route('admin.class-groups.enrollments', row.id)">
                                        <BaseButton size="sm" variant="secondary">
                                            <i class="fa-solid fa-users mr-2" />
                                            Roster
                                        </BaseButton>
                                    </Link>

                                    <Link v-if="row.source.can_view_grades" :href="route('groups.grades.index', row.id)">
                                        <BaseButton size="sm" :variant="row.source.can_edit_grades ? 'primary' : 'secondary'">
                                            <i class="fa-solid fa-clipboard-list mr-2" />
                                            {{ row.source.can_edit_grades ? "Grades" : "View Grades" }}
                                        </BaseButton>
                                    </Link>

                                    <BaseButton v-else size="sm" variant="secondary" disabled>
                                        <i class="fa-solid fa-lock mr-2" />
                                        Grades Unavailable
                                    </BaseButton>
                                </div>
                            </template>
                        </DataTable>

                        <EmptyState v-else title="No assigned groups"
                            :description="systemState === 'no_period'
                                ? 'There is no active academic period. Assigned groups will appear when a period is activated.'
                                : 'You have no assigned class groups in the active academic period.'"
                            icon="fa-solid fa-users-rectangle" />
                    </div>
                </SectionCard>
            </div>
        </CrudContainer>

    </CrudPageLayout>
</template>
