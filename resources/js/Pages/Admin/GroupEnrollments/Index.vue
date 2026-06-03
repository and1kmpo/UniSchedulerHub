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

const props = defineProps({
    classGroups: {
        type: Array,
        default: () => [],
    },

    canManageEnrollments: {
        type: Boolean,
        default: false,
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
            capacity: 0,
            pending: 0,
            confirmed: 0,
            incomplete_loads: 0,
            min_credits: 7,
        }),
    },

    incompleteLoads: {
        type: Array,
        default: () => [],
    },

    systemState: {
        type: String,
        default: "ready",
    },
});

const columns = [
    { key: "code", label: "Group" },
    { key: "subject", label: "Subject" },
    { key: "professor", label: "Professor" },
    { key: "period", label: "Period" },
    { key: "capacity_summary", label: "Capacity" },
    { key: "pending", label: "Pending" },
    { key: "confirmed", label: "Confirmed" },
    { key: "status", label: "Status" },
];

const pageTitle = computed(() =>
    props.canManageEnrollments ? "Enrollment Management" : "My Group Rosters"
);

const pageSubtitle = computed(() =>
    props.canManageEnrollments
        ? "Operational workspace to monitor group capacity, pending confirmations and assisted enrollment"
        : "Review students enrolled in your assigned academic groups"
);

const rows = computed(() =>
    props.classGroups.map((group) => ({
        ...group,
        capacity_summary: `${group.enrolled}/${group.capacity}`,
    }))
);

const groupStatusVariant = (status) => ({
    draft: "warning",
    published: "success",
    cancelled: "danger",
    closed: "gray",
}[status] || "gray");

function formatStatus(status) {
    return status ? status.replaceAll("_", " ").toUpperCase() : "PENDING";
}

function countVariant(value) {
    return value > 0 ? "warning" : "gray";
}
</script>

<template>
    <CrudPageLayout :title="pageTitle" :subtitle="pageSubtitle">
        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-3 xl:grid-cols-6">
                    <StatCard title="Groups" :value="summary.groups" icon="fa-solid fa-users-rectangle" />
                    <StatCard title="Active Students" :value="summary.students" icon="fa-solid fa-user-graduate" />
                    <StatCard title="Total Capacity" :value="summary.capacity" icon="fa-solid fa-chair" />
                    <StatCard title="Pending" :value="summary.pending" icon="fa-solid fa-hourglass-half" />
                    <StatCard title="Confirmed" :value="summary.confirmed" icon="fa-solid fa-circle-check" />
                    <StatCard title="Incomplete Loads" :value="summary.incomplete_loads" icon="fa-solid fa-triangle-exclamation" />
                </section>

                <SectionCard>
                    <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                What This View Is For
                            </h2>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ canManageEnrollments
                                    ? "Use this view to operate enrollment after groups are created: monitor capacity, review pending confirmations, detect incomplete credit loads and manage assisted enrollment by group."
                                    : "Use this view to review rosters for your assigned groups. Academic setup and scheduling stay in Class Groups." }}
                            </p>
                            <p class="mt-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Active period: {{ period?.name ?? "No active academic period" }}
                            </p>
                        </div>

                        <StatusBadge :label="period?.state ? formatStatus(period.state) : 'NOT ACTIVE'"
                            :variant="period ? 'success' : 'gray'" />
                    </div>
                </SectionCard>

                <SectionCard v-if="canManageEnrollments && incompleteLoads.length">
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Students Below Minimum Load
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Students with active selections below {{ summary.min_credits }} credits require follow-up before enrollment confirmation.
                        </p>
                    </div>

                    <div class="grid gap-3 p-6 md:grid-cols-2 xl:grid-cols-3">
                        <div
                            v-for="load in incompleteLoads"
                            :key="load.student_id"
                            class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-500/10"
                        >
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ load.student_name ?? "Student" }}
                            </p>

                            <p class="mt-1 text-sm text-amber-800 dark:text-amber-300">
                                {{ load.credits }} / {{ load.min_credits }} credits. Missing {{ load.missing_credits }}.
                            </p>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ canManageEnrollments ? "Enrollment Operations" : "Assigned Rosters" }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ canManageEnrollments
                                ? "Open a group to review its roster, assist enrollment and process withdrawals. Group setup remains in Class Groups."
                                : "Open a group to review enrolled students and current grade status." }}
                        </p>
                    </div>

                    <div class="p-6">
                        <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                            <template #cell-status="{ value }">
                                <StatusBadge :label="formatStatus(value)" :variant="groupStatusVariant(value)" />
                            </template>

                            <template #cell-pending="{ value }">
                                <StatusBadge :label="String(value || 0)" :variant="countVariant(value)" />
                            </template>

                            <template #cell-confirmed="{ value }">
                                <StatusBadge :label="String(value || 0)" :variant="value > 0 ? 'success' : 'gray'" />
                            </template>

                            <template #actions="{ row }">
                                <Link :href="route('admin.class-groups.enrollments', row.id)">
                                    <BaseButton size="sm" :variant="canManageEnrollments ? 'primary' : 'secondary'">
                                        <i :class="canManageEnrollments ? 'fa-solid fa-user-plus mr-2' : 'fa-solid fa-eye mr-2'" />
                                        {{ canManageEnrollments ? "Manage" : "View" }}
                                    </BaseButton>
                                </Link>
                            </template>
                        </DataTable>

                        <EmptyState v-else title="No groups found"
                            :description="systemState === 'no_period'
                                ? 'There is no active academic period. Enrollment review will be available when a period is activated.'
                                : 'There are no class groups available for enrollment review in the active period.'"
                            icon="fa-solid fa-users-rectangle" />
                    </div>
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
