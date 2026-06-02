<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
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
});

const columns = [
    { key: "code", label: "Group" },
    { key: "subject", label: "Subject" },
    { key: "professor", label: "Professor" },
    { key: "period", label: "Period" },
    { key: "capacity_summary", label: "Capacity" },
    { key: "status", label: "Status" },
];

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
</script>

<template>
    <CrudPageLayout title="Group Enrollments" subtitle="Review active enrollments by academic group">
        <CrudContainer>
            <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                <template #cell-status="{ value }">
                    <StatusBadge :label="formatStatus(value)" :variant="groupStatusVariant(value)" />
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
                description="There are no class groups available for enrollment review."
                icon="fa-solid fa-users-rectangle" />
        </CrudContainer>
    </CrudPageLayout>
</template>
