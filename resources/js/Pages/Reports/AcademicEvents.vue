<script setup>
import { computed, reactive, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseInput from "@/Components/UI/Base/BaseInput.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import FilterPanel from "@/Components/UI/Filters/FilterPanel.vue";
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
            <BaseButton as="a" variant="secondary" :href="route('reports.index')">
                <i class="fa-solid fa-arrow-left mr-2" />
                Reports
            </BaseButton>
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
                    <FilterPanel>
                        <template #search>
                            <TableSearch v-model="filterForm.search" placeholder="Search action, summary, actor or entity..." />
                        </template>

                        <template #filters>
                            <BaseSelect v-model="filterForm.event_type" placeholder="Event type" :options="options.eventTypes" />
                            <BaseSelect v-model="filterForm.action" placeholder="Action" :options="options.actions" />
                            <BaseSelect v-model="filterForm.user_id" placeholder="Actor" :options="options.users" />
                            <BaseSelect v-model="filterForm.auditable_type" placeholder="Entity" :options="options.entities" />
                            <BaseInput
                                v-model="filterForm.date_from"
                                type="date"
                                aria-label="Date from"
                            />
                            <BaseInput
                                v-model="filterForm.date_to"
                                type="date"
                                aria-label="Date to"
                            />
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
                            Audited Academic Events
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Use this report to review who performed critical academic operations and what records were affected.
                        </p>
                    </div>

                    <div v-if="events.data.length" class="divide-y divide-border-light dark:divide-border-dark">
                        <article v-for="event in events.data" :key="event.id" class="p-6">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <StatusBadge :label="event.event_type_label" :variant="eventVariant(event.event_type)" />
                                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                            {{ formatDateTime(event.created_at) }}
                                        </span>
                                    </div>

                                    <h3 class="mt-3 text-base font-semibold text-ink dark:text-white">
                                        {{ event.action_label }}
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-600 dark:text-zinc-300">
                                        {{ event.summary }}
                                    </p>
                                </div>

                                <div class="rounded-lg border border-border-light px-4 py-3 text-sm dark:border-border-dark xl:min-w-72">
                                    <p class="font-semibold text-ink dark:text-white">
                                        {{ event.user?.name || "System" }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ event.user?.email || "Automated operation" }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-4 lg:grid-cols-[16rem_1fr]">
                                <div class="rounded-lg border border-border-light bg-surface p-4 dark:border-border-dark dark:bg-surface-dark">
                                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                                        Affected record
                                    </p>
                                    <p class="mt-2 text-sm font-semibold text-ink dark:text-white">
                                        {{ event.entity }}
                                    </p>
                                    <p v-if="event.entity_id" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        ID {{ event.entity_id }}
                                    </p>
                                    <p v-if="event.change_count" class="mt-3 text-xs font-semibold text-warning dark:text-warning">
                                        {{ event.change_count }} changed field(s)
                                    </p>
                                </div>

                                <div class="rounded-lg border border-border-light bg-surface p-4 dark:border-border-dark dark:bg-surface-dark">
                                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">
                                        Context
                                    </p>
                                    <dl v-if="metadataEntries(event.metadata).length" class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <div v-for="[key, value] in metadataEntries(event.metadata)" :key="key">
                                            <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                {{ formatKey(key) }}
                                            </dt>
                                            <dd class="mt-1 break-words text-sm text-ink dark:text-white">
                                                {{ formatValue(value) }}
                                            </dd>
                                        </div>
                                    </dl>
                                    <p v-else class="mt-3 text-sm text-slate-500 dark:text-slate-400">
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
