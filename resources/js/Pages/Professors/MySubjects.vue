<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";

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

const selectedGroup = ref(null);

const columns = [
    { key: "subject", label: "Subject" },
    { key: "code", label: "Group" },
    { key: "schedule_summary", label: "Schedule" },
    { key: "capacity_summary", label: "Capacity" },
    { key: "status", label: "Status" },
];

const rows = computed(() =>
    props.groups.map((group) => ({
        id: group.id,
        subject: group.subject?.name ?? "N/A",
        code: group.code ?? group.name,
        schedule_summary: formatSchedules(group.schedules),
        capacity_summary: `${group.subject_enrollments_count}/${group.capacity}`,
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

function formatSchedules(schedules = []) {
    if (!schedules.length) {
        return "Pending";
    }

    return schedules
        .map((schedule) => {
            const room = schedule.classroom ? ` - ${schedule.classroom}` : "";

            return `${formatDay(schedule.day)} ${schedule.start_time}-${schedule.end_time}${room}`;
        })
        .join("; ");
}

const groupStatusVariant = (status) => ({
    draft: "warning",
    published: "success",
    cancelled: "danger",
    closed: "gray",
}[status] || "gray");

const openModal = (row) => {
    selectedGroup.value = row.source;
};

const closeModal = () => {
    selectedGroup.value = null;
};
</script>

<template>
    <CrudPageLayout title="My Class Groups" :subtitle="period
        ? `Assigned groups for ${period.name}`
        : 'Assigned groups for the active academic period'
        ">
        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <StatCard title="Assigned Groups" :value="summary.groups" icon="fa-solid fa-users-rectangle" />
                    <StatCard title="Active Students" :value="summary.students" icon="fa-solid fa-user-graduate" />
                    <StatCard title="Subject Credits" :value="summary.credits" icon="fa-solid fa-layer-group" />
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
                            Groups, schedules, students and grading access for the active period.
                        </p>
                    </div>

                    <div class="p-6">
                        <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                            <template #cell-schedule_summary="{ value }">
                                <span class="block max-w-md whitespace-normal text-sm text-gray-700 dark:text-gray-300">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-status="{ value }">
                                <StatusBadge :label="formatStatus(value)" :variant="groupStatusVariant(value)" />
                            </template>

                            <template #actions="{ row }">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <BaseButton size="sm" variant="secondary" @click="openModal(row)">
                                        <i class="fa-solid fa-eye mr-2" />
                                        Students
                                    </BaseButton>

                                    <Link :href="route('admin.class-groups.enrollments', row.id)">
                                        <BaseButton size="sm" variant="secondary">
                                            <i class="fa-solid fa-users mr-2" />
                                            Enrollments
                                        </BaseButton>
                                    </Link>

                                    <Link v-if="row.source.can_manage_grades" :href="route('groups.grades.index', row.id)">
                                        <BaseButton size="sm" variant="primary">
                                            <i class="fa-solid fa-clipboard-list mr-2" />
                                            Grades
                                        </BaseButton>
                                    </Link>

                                    <BaseButton v-else size="sm" variant="secondary" disabled>
                                        <i class="fa-solid fa-lock mr-2" />
                                        Grades Locked
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

        <div v-if="selectedGroup" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            role="dialog" aria-label="Group students modal">
            <div class="w-full max-w-5xl rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900 dark:text-gray-200">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ selectedGroup.subject?.name }} - {{ selectedGroup.code }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ formatSchedules(selectedGroup.schedules) }}
                        </p>
                    </div>

                    <button class="text-gray-500 hover:text-red-500" @click="closeModal" aria-label="Close">
                        <i class="fa-solid fa-xmark" />
                    </button>
                </div>

                <DataTable v-if="selectedGroup.subject_enrollments.length" :columns="[
                    { key: 'name', label: 'Student' },
                    { key: 'document', label: 'Document' },
                    { key: 'email', label: 'Email' },
                    { key: 'status', label: 'Status' },
                    { key: 'final_grade', label: 'Final Grade' },
                ]" :rows="selectedGroup.subject_enrollments.map((enrollment) => ({
                    id: enrollment.id,
                    name: enrollment.student?.name ?? 'N/A',
                    document: enrollment.student?.document ?? 'N/A',
                    email: enrollment.student?.email ?? 'N/A',
                    status: formatStatus(enrollment.status),
                    final_grade: enrollment.grade?.final_grade ?? 'Pending',
                }))" />

                <EmptyState v-else title="No active students"
                    description="This group has no active enrollments for the current period."
                    icon="fa-solid fa-user-graduate" />
            </div>
        </div>
    </CrudPageLayout>
</template>
