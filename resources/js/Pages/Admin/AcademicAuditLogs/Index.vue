<script setup>
import { computed, reactive, ref, watch } from "vue";
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
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            today: 0,
            grade_events: 0,
            enrollment_events: 0,
        }),
    },
});

const columns = [
    { key: "created_at", label: "Date", sortable: true },
    { key: "action", label: "Action", sortable: true },
    { key: "entity", label: "Entity" },
    { key: "user", label: "User" },
    { key: "summary", label: "Summary" },
];

const filterForm = reactive({
    search: props.filters.search || "",
    action: props.filters.action || "",
    user_id: props.filters.user_id || "",
    auditable_type: props.filters.auditable_type || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

const selectedLog = ref(null);

const activeFiltersCount = computed(() =>
    Object.values(filterForm).filter((value) => value !== "" && value !== null && value !== undefined).length
);

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

function openDetails(log) {
    selectedLog.value = log;
}

function closeDetails() {
    selectedLog.value = null;
}

function formatKey(key) {
    return String(key)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function formatValue(value) {
    if (value === null || value === undefined || value === "") return "-";
    if (typeof value === "boolean") return value ? "Yes" : "No";
    if (Array.isArray(value)) return value.join(", ");
    if (typeof value === "object") return JSON.stringify(value);
    return value;
}

function metadataEntries(metadata) {
    return Object.entries(metadata || {}).filter(([key]) => !["before", "after"].includes(key));
}

function changeRows(metadata) {
    const before = metadata?.before || {};
    const after = metadata?.after || {};
    const keys = [...new Set([...Object.keys(before), ...Object.keys(after)])];

    return keys
        .filter((key) => JSON.stringify(before[key] ?? null) !== JSON.stringify(after[key] ?? null))
        .map((key) => ({
            key,
            before: before[key],
            after: after[key],
        }));
}
</script>

<template>
    <CrudPageLayout title="Academic Audit Logs" subtitle="Trace critical academic operations across enrollments, schedules, grades and periods">
        <CrudContainer>
            <div class="grid gap-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Total events</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total }}</p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Today</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.today }}</p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Grade events</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.grade_events }}</p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Enrollment events</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.enrollment_events }}</p>
                </div>
            </div>

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
                        Reset<span v-if="activeFiltersCount"> ({{ activeFiltersCount }})</span>
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
                    <span class="line-clamp-2 text-gray-700 dark:text-gray-300">
                        {{ row.summary || "No summary available" }}
                    </span>
                </template>

                <template #actions="{ row }">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-indigo-300 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:text-gray-300 dark:hover:border-indigo-500 dark:hover:text-indigo-300"
                        @click="openDetails(row)"
                    >
                        <i class="fa-solid fa-eye mr-2 text-xs"></i>
                        Details
                    </button>
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

        <div
            v-if="selectedLog"
            class="fixed inset-0 z-50 flex items-end bg-black/40 px-4 py-6 sm:items-center sm:justify-center"
            role="dialog"
            aria-modal="true"
            @click.self="closeDetails"
        >
            <section class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-lg bg-white shadow-xl dark:bg-gray-900">
                <header class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <StatusBadge :label="selectedLog.action_label" :variant="actionVariant(selectedLog.action)" />
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ selectedLog.created_at }}</span>
                        </div>
                        <h2 class="mt-3 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ selectedLog.summary || "Audit event details" }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ selectedLog.entity }}<span v-if="selectedLog.entity_id"> ID {{ selectedLog.entity_id }}</span>
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:hover:bg-gray-800 dark:hover:text-white"
                        aria-label="Close details"
                        @click="closeDetails"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </header>

                <div class="grid gap-6 px-6 py-5 lg:grid-cols-[1fr_1.4fr]">
                    <section class="space-y-4">
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Actor</h3>
                            <div v-if="selectedLog.user" class="mt-3 space-y-1 text-sm">
                                <p class="font-medium text-gray-900 dark:text-white">{{ selectedLog.user.name }}</p>
                                <p class="text-gray-500 dark:text-gray-400">{{ selectedLog.user.email }}</p>
                            </div>
                            <p v-else class="mt-3 text-sm text-gray-500 dark:text-gray-400">System</p>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Context</h3>
                            <dl class="mt-3 space-y-2 text-sm">
                                <div v-for="[key, value] in metadataEntries(selectedLog.metadata)" :key="key" class="grid grid-cols-[9rem_1fr] gap-3">
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">{{ formatKey(key) }}</dt>
                                    <dd class="break-words text-gray-800 dark:text-gray-200">{{ formatValue(value) }}</dd>
                                </div>
                                <div v-if="!metadataEntries(selectedLog.metadata).length" class="text-gray-500 dark:text-gray-400">
                                    No additional context recorded.
                                </div>
                            </dl>
                        </div>
                    </section>

                    <section class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Changes</h3>

                        <div v-if="changeRows(selectedLog.metadata).length" class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="border-b border-gray-100 text-left text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                    <tr>
                                        <th class="py-2 pr-4 font-semibold">Field</th>
                                        <th class="px-4 py-2 font-semibold">Before</th>
                                        <th class="px-4 py-2 font-semibold">After</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr v-for="change in changeRows(selectedLog.metadata)" :key="change.key">
                                        <td class="py-3 pr-4 font-medium text-gray-700 dark:text-gray-300">{{ formatKey(change.key) }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ formatValue(change.before) }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ formatValue(change.after) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            This event did not record before/after field changes.
                        </p>
                    </section>
                </div>
            </section>
        </div>
    </CrudPageLayout>
</template>
