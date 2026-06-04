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
import TableToolbar from "@/Components/UI/Table/TableToolbar.vue";

const props = defineProps({
    students: {
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
            programs: [],
            professors: [],
            statuses: [],
        }),
    },
});

const filterForm = reactive({
    search: props.filters.search || "",
    academic_period_id: props.filters.academic_period_id || "",
    program_id: props.filters.program_id || "",
    professor_id: props.filters.professor_id || "",
    status: props.filters.status || "",
});

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(route("reports.student-assignments.index"), filterPayload(), {
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
        program_id: filterForm.program_id,
        professor_id: filterForm.professor_id,
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
    filterForm.program_id = "";
    filterForm.professor_id = "";
    filterForm.status = "";
}

function printReport() {
    const printableRows = props.students.data.flatMap((student) =>
        student.assignments.map((assignment) => `
            <tr>
                <td>
                    <strong>${escapeHtml(student.name || "-")}</strong><br>
                    <span>${escapeHtml(student.document || "-")} / ${escapeHtml(student.email || "-")}</span>
                </td>
                <td>${escapeHtml(student.program || "No program")}<br><span>Semester ${escapeHtml(student.semester || "-")}</span></td>
                <td><strong>${escapeHtml(assignment.subject.code || "-")}</strong><br>${escapeHtml(assignment.subject.name || "-")}</td>
                <td>${escapeHtml(assignment.professor.name || "Unassigned")}</td>
                <td>${escapeHtml(assignment.group.code || "No group")}</td>
                <td>${escapeHtml(assignment.period || "-")}</td>
                <td>${escapeHtml(assignment.status_label || assignment.status || "-")}</td>
            </tr>
        `)
    ).join("");

    const printWindow = window.open("", "_blank", "width=1200,height=800");

    printWindow.document.write(`
        <!doctype html>
        <html>
            <head>
                <title>Student Assignment Report</title>
                <style>
                    body { color: #111827; font-family: Arial, sans-serif; margin: 32px; }
                    header { border-bottom: 2px solid #111827; margin-bottom: 24px; padding-bottom: 16px; }
                    h1 { font-size: 24px; margin: 0; }
                    p { color: #4b5563; margin: 6px 0 0; }
                    .summary { display: grid; gap: 12px; grid-template-columns: repeat(4, 1fr); margin-bottom: 24px; }
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
                    <h1>Student Assignment Report</h1>
                    <p>Students, enrolled subjects, class groups and responsible professors.</p>
                    <p>Generated ${new Date().toLocaleString()}</p>
                </header>
                <section class="summary">
                    <div class="metric"><span>Students</span><strong>${props.summary.students}</strong></div>
                    <div class="metric"><span>Assignments</span><strong>${props.summary.assignments}</strong></div>
                    <div class="metric"><span>Active credits</span><strong>${props.summary.active_credits}</strong></div>
                    <div class="metric"><span>Minimum credits</span><strong>${props.summary.minimum_credits}</strong></div>
                </section>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Program</th>
                            <th>Subject</th>
                            <th>Professor</th>
                            <th>Group</th>
                            <th>Period</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${printableRows || '<tr><td colspan="7">No assignments found.</td></tr>'}</tbody>
                </table>
            </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}

const csvExportUrl = computed(() => route("reports.student-assignments.export", exportPayload()));

function escapeHtml(value) {
    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function statusVariant(status) {
    return {
        pre_enrolled: "warning",
        enrolled: "success",
        withdrawn: "gray",
        cancelled: "gray",
        approved: "success",
        failed: "danger",
    }[status] || "gray";
}

function creditVariant(student) {
    return student.active_credits >= student.minimum_credits ? "success" : "warning";
}

function formatSubjectType(assignment) {
    return assignment.subject.elective ? "Elective" : "Required";
}

const periodOptions = props.options.periods.map((period) => ({
    label: period.name,
    value: period.id,
}));

const programOptions = props.options.programs.map((program) => ({
    label: program.name,
    value: program.id,
}));

const professorOptions = props.options.professors.map((professor) => ({
    label: professor.name,
    value: professor.id,
}));

const statusOptions = props.options.statuses.map((status) => ({
    label: status.description,
    value: status.code,
}));
</script>

<template>
    <CrudPageLayout
        title="Student Assignment Report"
        subtitle="Students, enrolled subjects, class groups and responsible professors"
    >
        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-4 print:grid-cols-4">
                    <StatCard title="Students" :value="summary.students" icon="fa-solid fa-user-graduate" />
                    <StatCard title="Assignments" :value="summary.assignments" icon="fa-solid fa-book-open" />
                    <StatCard title="Active Credits" :value="summary.active_credits" icon="fa-solid fa-layer-group" />
                    <StatCard title="Minimum Credits" :value="summary.minimum_credits" icon="fa-solid fa-scale-balanced" />
                </section>

                <SectionCard class="print:hidden">
                    <TableToolbar>
                        <template #search>
                            <div class="w-full lg:max-w-sm">
                                <TableSearch v-model="filterForm.search" placeholder="Search student, document or email..." />
                            </div>
                        </template>

                        <template #filters>
                            <div class="grid w-full gap-3 md:grid-cols-4">
                                <BaseSelect v-model="filterForm.academic_period_id" placeholder="Academic period" :options="periodOptions" />
                                <BaseSelect v-model="filterForm.program_id" placeholder="Program" :options="programOptions" />
                                <BaseSelect v-model="filterForm.professor_id" placeholder="Professor" :options="professorOptions" />
                                <BaseSelect v-model="filterForm.status" placeholder="Enrollment status" :options="statusOptions" />
                            </div>
                        </template>

                        <template #actions>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Exports use current filters
                            </span>

                            <Link
                                :href="route('reports.professor-load.index')"
                                class="inline-flex items-center justify-center rounded-xl bg-gray-200 px-4 py-2 text-sm font-medium text-gray-800 transition-all duration-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400"
                            >
                                <i class="fa-solid fa-chalkboard-user mr-2" />
                                Professor Load
                            </Link>

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

                            <BaseButton variant="secondary" @click="clearFilters">
                                <i class="fa-solid fa-rotate-left mr-2" />
                                Reset
                            </BaseButton>
                        </template>
                    </TableToolbar>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Students And Assignments
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Each student is grouped with their subjects, responsible professors and class groups.
                        </p>
                    </div>

                    <div v-if="students.data.length" class="divide-y divide-gray-100 dark:divide-gray-800">
                        <article v-for="student in students.data" :key="student.id" class="p-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                            {{ student.name }}
                                        </h3>
                                        <StatusBadge
                                            :label="`${student.active_credits}/${student.minimum_credits} credits`"
                                            :variant="creditVariant(student)"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ student.document }} / {{ student.email }} / {{ student.program || "No program" }} / Semester {{ student.semester }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2 text-sm">
                                    <span class="rounded-lg bg-gray-100 px-3 py-2 font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        {{ student.assignments_count }} assignments
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="border-b border-gray-100 text-left text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Subject</th>
                                            <th class="px-4 py-3 font-semibold">Professor</th>
                                            <th class="px-4 py-3 font-semibold">Group</th>
                                            <th class="px-4 py-3 font-semibold">Period</th>
                                            <th class="px-4 py-3 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="assignment in student.assignments" :key="assignment.id">
                                            <td class="px-4 py-4">
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ assignment.subject.code }} - {{ assignment.subject.name }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ assignment.subject.credits }} credits / {{ assignment.subject.area || "No area" }} / {{ formatSubjectType(assignment) }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ assignment.professor.name }}
                                                </p>
                                                <p v-if="assignment.professor.email" class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ assignment.professor.email }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                                                {{ assignment.group.code || "No group" }}
                                            </td>
                                            <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                                                {{ assignment.period || "-" }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <StatusBadge :label="assignment.status_label || assignment.status" :variant="statusVariant(assignment.status)" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>

                    <div v-else class="p-6">
                        <EmptyState
                            title="No assignments found"
                            description="Try adjusting the filters or wait until students have active academic assignments."
                            icon="fa-solid fa-file-lines"
                        />
                    </div>

                    <TablePagination v-if="students.data.length" :data="students" class="print:hidden" />
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
