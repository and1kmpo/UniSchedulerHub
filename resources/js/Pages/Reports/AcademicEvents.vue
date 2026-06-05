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
import { formatDate, formatDateTime } from "@/Components/Composables/useDateTimeFormatter";
import { printTableReport } from "@/Components/Composables/usePrintableReport";

const props = defineProps({
    events: {
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
            actions: [],
            eventTypes: [],
            users: [],
            entities: [],
        }),
    },
});

const filterForm = reactive({
    search: props.filters.search || "",
    action: props.filters.action || "",
    event_type: props.filters.event_type || "",
    user_id: props.filters.user_id || "",
    auditable_type: props.filters.auditable_type || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(route("reports.academic-events.index"), filterPayload(), {
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
        action: filterForm.action,
        event_type: filterForm.event_type,
        user_id: filterForm.user_id,
        auditable_type: filterForm.auditable_type,
        date_from: filterForm.date_from,
        date_to: filterForm.date_to,
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
    filterForm.action = "";
    filterForm.event_type = "";
    filterForm.user_id = "";
    filterForm.auditable_type = "";
    filterForm.date_from = "";
    filterForm.date_to = "";
}

const csvExportUrl = computed(() => route("reports.academic-events.export", exportPayload()));

function optionLabel(options, value) {
    return options.find((option) => String(option.value) === String(value))?.label || value;
}

function eventVariant(type) {
    return {
        enrollment: "success",
        schedule: "warning",
        grade: "primary",
        academic_period: "gray",
    }[type] || "gray";
}

function metadataEntries(metadata) {
    return Object.entries(metadata || {}).filter(([key]) => !["before", "after"].includes(key));
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

function printReport() {
    printTableReport({
        title: "Academic Events Report",
        subtitle: "Critical academic events, actors, affected records and audit context.",
        filters: [
            { label: "Search", value: filterForm.search },
            { label: "Event type", value: optionLabel(props.options.eventTypes, filterForm.event_type) },
            { label: "Action", value: optionLabel(props.options.actions, filterForm.action) },
            { label: "Actor", value: optionLabel(props.options.users, filterForm.user_id) },
            { label: "Entity", value: optionLabel(props.options.entities, filterForm.auditable_type) },
            { label: "Date from", value: formatDate(filterForm.date_from, "") },
            { label: "Date to", value: formatDate(filterForm.date_to, "") },
        ],
        metrics: [
            { label: "Events", value: props.summary.events },
            { label: "Today", value: props.summary.today },
            { label: "Enrollment", value: props.summary.enrollment_events },
            { label: "Schedules", value: props.summary.schedule_events },
            { label: "Grades", value: props.summary.grade_events },
            { label: "Periods", value: props.summary.period_events },
            { label: "Actors", value: props.summary.actors },
        ],
        columns: [
            { key: "date", label: "Date" },
            { key: "type", label: "Type" },
            { key: "action", label: "Action" },
            { key: "actor", label: "Actor" },
            { key: "entity", label: "Entity" },
            { key: "summary", label: "Summary" },
        ],
        rows: props.events.data.map((event) => ({
            date: formatDateTime(event.created_at),
            type: event.event_type_label,
            action: event.action_label,
            actor: event.user?.name || "System",
            entity: event.entity + (event.entity_id ? " #" + event.entity_id : ""),
            summary: event.summary,
        })),
    });
}
</script>

<template>
    <CrudPageLayout
        title="Academic Events Report"
        subtitle="Critical academic events, actors, affected records and audit context"
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
                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard title="Events" :value="summary.events" icon="fa-solid fa-shield-halved" />
                    <StatCard title="Today" :value="summary.today" icon="fa-solid fa-calendar-day" />
                    <StatCard title="Enrollment" :value="summary.enrollment_events" icon="fa-solid fa-user-check" />
                    <StatCard title="Schedules" :value="summary.schedule_events" icon="fa-solid fa-calendar-days" />
                    <StatCard title="Grades" :value="summary.grade_events" icon="fa-solid fa-clipboard-check" />
                    <StatCard title="Periods" :value="summary.period_events" icon="fa-solid fa-arrows-rotate" />
                    <StatCard title="Actors" :value="summary.actors" icon="fa-solid fa-users-gear" />
                </section>

                <SectionCard>
                    <div class="space-y-4 border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                        <div class="grid gap-3 lg:grid-cols-[minmax(18rem,24rem)_1fr]">
                            <TableSearch v-model="filterForm.search" placeholder="Search action, summary, actor or entity..." />

                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <BaseSelect v-model="filterForm.event_type" placeholder="Event type" :options="options.eventTypes" />
                                <BaseSelect v-model="filterForm.action" placeholder="Action" :options="options.actions" />
                                <BaseSelect v-model="filterForm.user_id" placeholder="Actor" :options="options.users" />
                                <BaseSelect v-model="filterForm.auditable_type" placeholder="Entity" :options="options.entities" />
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:max-w-xl">
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
                            Audited Academic Events
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Use this report to review who performed critical academic operations and what records were affected.
                        </p>
                    </div>

                    <div v-if="events.data.length" class="divide-y divide-gray-100 dark:divide-gray-800">
                        <article v-for="event in events.data" :key="event.id" class="p-6">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <StatusBadge :label="event.event_type_label" :variant="eventVariant(event.event_type)" />
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ formatDateTime(event.created_at) }}
                                        </span>
                                    </div>

                                    <h3 class="mt-3 text-base font-semibold text-gray-900 dark:text-white">
                                        {{ event.action_label }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                        {{ event.summary }}
                                    </p>
                                </div>

                                <div class="rounded-lg border border-gray-200 px-4 py-3 text-sm dark:border-gray-800 xl:min-w-72">
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ event.user?.name || "System" }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ event.user?.email || "Automated operation" }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-4 lg:grid-cols-[16rem_1fr]">
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/60">
                                    <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                        Affected record
                                    </p>
                                    <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ event.entity }}
                                    </p>
                                    <p v-if="event.entity_id" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        ID {{ event.entity_id }}
                                    </p>
                                    <p v-if="event.change_count" class="mt-3 text-xs font-semibold text-amber-600 dark:text-amber-300">
                                        {{ event.change_count }} changed field(s)
                                    </p>
                                </div>

                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/60">
                                    <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                        Context
                                    </p>
                                    <dl v-if="metadataEntries(event.metadata).length" class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <div v-for="[key, value] in metadataEntries(event.metadata)" :key="key">
                                            <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                {{ formatKey(key) }}
                                            </dt>
                                            <dd class="mt-1 break-words text-sm text-gray-900 dark:text-white">
                                                {{ formatValue(value) }}
                                            </dd>
                                        </div>
                                    </dl>
                                    <p v-else class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                        No additional context recorded.
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>

                    <EmptyState
                        v-else
                        title="No academic events found"
                        description="Adjust filters or wait until audited academic actions are recorded."
                        icon="fa-solid fa-file-circle-question"
                    />
                </SectionCard>

                <TablePagination v-if="events.data.length" :data="events" />
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
