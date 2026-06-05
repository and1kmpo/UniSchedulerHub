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

const props = defineProps({
    professors: {
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
        }),
    },
});

const filterForm = reactive({
    search: props.filters.search || "",
    academic_period_id: props.filters.academic_period_id || "",
    status: props.filters.status || "",
});

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(route("reports.professor-load.index"), filterPayload(), {
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
}

const csvExportUrl = computed(() => route("reports.professor-load.export", exportPayload()));

const periodOptions = props.options.periods.map((period) => ({
    label: period.name,
    value: period.id,
}));

const statusOptions = props.options.statuses;

function statusVariant(status) {
    return {
        draft: "gray",
        published: "success",
        cancelled: "danger",
        closed: "gray",
    }[status] || "gray";
}

function printReport() {
    const printableRows = props.professors.data.flatMap((professor) =>
        professor.groups.map((group) => `
            <tr>
                <td>
                    <strong>${escapeHtml(professor.name || "-")}</strong><br>
                    <span>${escapeHtml(professor.document || "-")} / ${escapeHtml(professor.email || "-")}</span>
                </td>
                <td><strong>${escapeHtml(group.code || "-")}</strong><br>${escapeHtml(group.subject.name || "-")}</td>
                <td>${escapeHtml(group.period || "-")}</td>
                <td>${escapeHtml(group.status || "-")}</td>
                <td>${group.active_students}</td>
                <td>${group.scheduled_blocks}</td>
                <td>${group.pending_grades}</td>
            </tr>
        `)
    ).join("");

    printHtml(`
        <!doctype html>
        <html>
            <head>
                <title>Professor Load Report</title>
                <style>
                    body { color: #111827; font-family: Arial, sans-serif; margin: 32px; }
                    header { border-bottom: 2px solid #111827; margin-bottom: 24px; padding-bottom: 16px; }
                    h1 { font-size: 24px; margin: 0; }
                    p { color: #4b5563; margin: 6px 0 0; }
                    .summary { display: grid; gap: 12px; grid-template-columns: repeat(5, 1fr); margin-bottom: 24px; }
                    .metric { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; }
                    .metric span { color: #6b7280; display: block; font-size: 12px; text-transform: uppercase; }
                    .metric strong { display: block; font-size: 22px; margin-top: 6px; }
                    table { border-collapse: collapse; font-size: 12px; width: 100%; }
                    th { background: #f3f4f6; text-align: left; }
                    th, td { border: 1px solid #d1d5db; padding: 8px; vertical-align: top; }
                    td span { color: #6b7280; font-size: 11px; }
                    @page { margin: 18mm; size: landscape; }
                </style>
            </head>
            <body>
                <header>
                    <h1>Professor Load Report</h1>
                    <p>Assigned groups, active students, scheduled blocks and pending grades.</p>
                    <p>Generated ${new Date().toLocaleString()}</p>
                </header>
                <section class="summary">
                    <div class="metric"><span>Professors</span><strong>${props.summary.professors}</strong></div>
                    <div class="metric"><span>Groups</span><strong>${props.summary.groups}</strong></div>
                    <div class="metric"><span>Active students</span><strong>${props.summary.active_students}</strong></div>
                    <div class="metric"><span>Scheduled blocks</span><strong>${props.summary.scheduled_blocks}</strong></div>
                    <div class="metric"><span>Pending grades</span><strong>${props.summary.pending_grades}</strong></div>
                </section>
                <table>
                    <thead>
                        <tr>
                            <th>Professor</th>
                            <th>Group / Subject</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>Active students</th>
                            <th>Blocks</th>
                            <th>Pending grades</th>
                        </tr>
                    </thead>
                    <tbody>${printableRows || '<tr><td colspan="7">No professor load found.</td></tr>'}</tbody>
                </table>
            </body>
        </html>
    `);
}

function escapeHtml(value) {
    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function printHtml(html) {
    const iframe = document.createElement("iframe");

    iframe.style.position = "fixed";
    iframe.style.right = "0";
    iframe.style.bottom = "0";
    iframe.style.width = "0";
    iframe.style.height = "0";
    iframe.style.border = "0";

    document.body.appendChild(iframe);

    const printDocument = iframe.contentWindow.document;
    printDocument.open();
    printDocument.write(html);
    printDocument.close();

    iframe.onload = () => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        setTimeout(() => document.body.removeChild(iframe), 500);
    };
}
</script>

<template>
    <CrudPageLayout
        title="Professor Load Report"
        subtitle="Assigned groups, active students, scheduled blocks and pending grades"
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
                <section class="grid grid-cols-1 gap-6 md:grid-cols-5">
                    <StatCard title="Professors" :value="summary.professors" icon="fa-solid fa-chalkboard-user" />
                    <StatCard title="Groups" :value="summary.groups" icon="fa-solid fa-layer-group" />
                    <StatCard title="Active Students" :value="summary.active_students" icon="fa-solid fa-users" />
                    <StatCard title="Schedule Blocks" :value="summary.scheduled_blocks" icon="fa-solid fa-calendar-days" />
                    <StatCard title="Pending Grades" :value="summary.pending_grades" icon="fa-solid fa-clipboard-check" />
                </section>

                <SectionCard>
                    <div class="space-y-4 border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                        <div class="grid gap-3 lg:grid-cols-[minmax(18rem,24rem)_1fr]">
                            <TableSearch v-model="filterForm.search" placeholder="Search professor, document or email..." />

                            <div class="grid gap-3 md:grid-cols-2">
                                <BaseSelect v-model="filterForm.academic_period_id" placeholder="Academic period" :options="periodOptions" />
                                <BaseSelect v-model="filterForm.status" placeholder="Group status" :options="statusOptions" />
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
                            Teaching Load By Professor
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Each professor is grouped with their assigned class groups, students, schedule blocks and grading workload.
                        </p>
                    </div>

                    <div v-if="professors.data.length" class="divide-y divide-gray-100 dark:divide-gray-800">
                        <article v-for="professor in professors.data" :key="professor.id" class="p-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                        {{ professor.name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ professor.document }} / {{ professor.email }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
                                    <span class="rounded-lg bg-gray-100 px-3 py-2 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        {{ professor.groups_count }} groups
                                    </span>
                                    <span class="rounded-lg bg-gray-100 px-3 py-2 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        {{ professor.active_students }} students
                                    </span>
                                    <span class="rounded-lg bg-gray-100 px-3 py-2 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        {{ professor.scheduled_blocks }} blocks
                                    </span>
                                    <span class="rounded-lg bg-amber-50 px-3 py-2 font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                        {{ professor.pending_grades }} pending grades
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="border-b border-gray-100 text-left text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Group</th>
                                            <th class="px-4 py-3 font-semibold">Subject</th>
                                            <th class="px-4 py-3 font-semibold">Period</th>
                                            <th class="px-4 py-3 font-semibold">Status</th>
                                            <th class="px-4 py-3 font-semibold">Students</th>
                                            <th class="px-4 py-3 font-semibold">Blocks</th>
                                            <th class="px-4 py-3 font-semibold">Pending Grades</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="group in professor.groups" :key="group.id">
                                            <td class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                                {{ group.code }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ group.subject.code }} - {{ group.subject.name }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ group.subject.credits }} credits
                                                </p>
                                            </td>
                                            <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                                                {{ group.period || "-" }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <StatusBadge :label="group.status" :variant="statusVariant(group.status)" />
                                            </td>
                                            <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                                                {{ group.active_students }}
                                            </td>
                                            <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                                                {{ group.scheduled_blocks }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <StatusBadge
                                                    :label="`${group.pending_grades} pending`"
                                                    :variant="group.pending_grades > 0 ? 'warning' : 'success'"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>

                    <div v-else class="p-6">
                        <EmptyState
                            title="No professor load found"
                            description="Try adjusting the filters or assign class groups to professors."
                            icon="fa-solid fa-chalkboard-user"
                        />
                    </div>

                    <TablePagination v-if="professors.data.length" :data="professors" />
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
