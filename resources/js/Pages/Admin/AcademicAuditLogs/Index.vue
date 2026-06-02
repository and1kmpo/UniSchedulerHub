<script setup>
import { reactive, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import TableToolbar from "@/Components/UI/Table/TableToolbar.vue";
import TableSearch from "@/Components/UI/Table/TableSearch.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

const props = defineProps({
    logs: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    filterOptions: {
        type: Object,
        default: () => ({
            actions: [],
            users: [],
            auditableTypes: [],
        }),
    },
});

const columns = [
    { key: "created_at", label: "Date", sortable: true },
    { key: "action", label: "Action", sortable: true },
    { key: "entity", label: "Entity" },
    { key: "user", label: "User" },
    { key: "summary", label: "Summary" },
    { key: "metadata", label: "Metadata" },
];

const filterForm = reactive({
    search: props.filters.search || "",
    action: props.filters.action || "",
    user_id: props.filters.user_id || "",
    auditable_type: props.filters.auditable_type || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(
            route("academic-audit-logs.index"),
            {
                ...filterForm,
                sort: props.filters?.sort,
                direction: props.filters?.direction,
                page: 1,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    },
    { deep: true }
);

function clearFilters() {
    filterForm.search = "";
    filterForm.action = "";
    filterForm.user_id = "";
    filterForm.auditable_type = "";
    filterForm.date_from = "";
    filterForm.date_to = "";
}

function actionVariant(action) {
    if (action.includes("deleted") || action.includes("cancelled")) return "danger";
    if (action.includes("updated") || action.includes("transitioned")) return "warning";
    if (action.includes("created")) return "success";
    return "gray";
}

function metadataEntries(metadata) {
    return Object.entries(metadata || {}).slice(0, 4);
}
</script>

<template>
    <CrudPageLayout title="Academic Audit Logs" subtitle="Trace critical academic operations across enrollments, schedules, grades and periods">
        <CrudContainer>
            <TableToolbar>
                <template #search>
                    <div class="w-full lg:max-w-sm">
                        <TableSearch v-model="filterForm.search" placeholder="Search logs..." />
                    </div>
                </template>

                <template #filters>
                    <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                        <BaseSelect
                            v-model="filterForm.action"
                            :options="filterOptions.actions"
                            placeholder="All actions"
                        />

                        <BaseSelect
                            v-model="filterForm.user_id"
                            :options="filterOptions.users"
                            placeholder="All users"
                        />

                        <BaseSelect
                            v-model="filterForm.auditable_type"
                            :options="filterOptions.auditableTypes"
                            placeholder="All entities"
                        />

                        <input
                            v-model="filterForm.date_from"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            aria-label="Date from"
                        />

                        <input
                            v-model="filterForm.date_to"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            aria-label="Date to"
                        />
                    </div>
                </template>

                <template #actions>
                    <BaseButton variant="secondary" @click="clearFilters">
                        <i class="fa-solid fa-rotate-left mr-2"></i>
                        Reset
                    </BaseButton>
                </template>
            </TableToolbar>

            <DataTable v-if="logs.data.length" :columns="columns" :rows="logs.data" :filters="filters" sortable>
                <template #cell-action="{ row }">
                    <StatusBadge :label="row.action_label" :variant="actionVariant(row.action)" />
                </template>

                <template #cell-entity="{ row }">
                    <div class="space-y-1">
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ row.entity }}
                        </p>
                        <p v-if="row.entity_id" class="text-xs text-gray-500 dark:text-gray-400">
                            ID {{ row.entity_id }}
                        </p>
                    </div>
                </template>

                <template #cell-user="{ row }">
                    <div v-if="row.user" class="space-y-1">
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ row.user.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ row.user.email }}
                        </p>
                    </div>

                    <span v-else class="text-gray-500 dark:text-gray-400">
                        System
                    </span>
                </template>

                <template #cell-summary="{ row }">
                    <span class="text-gray-700 dark:text-gray-300">
                        {{ row.summary || "No summary available" }}
                    </span>
                </template>

                <template #cell-metadata="{ row }">
                    <dl v-if="metadataEntries(row.metadata).length" class="space-y-1 text-xs">
                        <div v-for="[key, value] in metadataEntries(row.metadata)" :key="key" class="flex gap-2">
                            <dt class="min-w-24 font-semibold text-gray-500 dark:text-gray-400">
                                {{ key }}
                            </dt>
                            <dd class="truncate text-gray-700 dark:text-gray-300">
                                {{ value }}
                            </dd>
                        </div>
                    </dl>

                    <span v-else class="text-gray-500 dark:text-gray-400">
                        -
                    </span>
                </template>
            </DataTable>

            <EmptyState
                v-else
                title="No audit logs found"
                description="Critical academic actions will appear here when enrollments, schedules, grades or periods change."
                icon="fa-solid fa-shield-halved"
            />

            <TablePagination v-if="logs.data.length" :data="logs" />
        </CrudContainer>
    </CrudPageLayout>
</template>
