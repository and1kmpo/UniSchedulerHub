<script setup>
import { computed, reactive, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import FilterPanel from "@/Components/UI/Filters/FilterPanel.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import TableSearch from "@/Components/UI/Table/TableSearch.vue";
import { printTableReport } from "@/Components/Composables/usePrintableReport";

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

function optionLabel(options, value) {
    return options.find((option) => String(option.value) === String(value))?.label || value;
}

function printReport() {
    printTableReport({
        title: "Student Assignment Report",
        subtitle: "Students, enrolled subjects, class groups and responsible professors.",
        filters: [
            { label: "Search", value: filterForm.search },
            { label: "Academic period", value: optionLabel(periodOptions, filterForm.academic_period_id) },
            { label: "Program", value: optionLabel(programOptions, filterForm.program_id) },
            { label: "Professor", value: optionLabel(professorOptions, filterForm.professor_id) },
            { label: "Enrollment status", value: optionLabel(statusOptions, filterForm.status) },
        ],
        metrics: [
            { label: "Students", value: props.summary.students },
            { label: "Assignments", value: props.summary.assignments },
            { label: "Active credits", value: props.summary.active_credits },
            { label: "Minimum credits", value: props.summary.minimum_credits },
        ],
        columns: [
            { key: "student", label: "Student" },
            { key: "program", label: "Program" },
            { key: "subject", label: "Subject" },
            { key: "professor", label: "Professor" },
            { key: "group", label: "Group" },
            { key: "period", label: "Period" },
            { key: "status", label: "Status" },
        ],
        rows: props.students.data.flatMap((student) =>
            student.assignments.map((assignment) => ({
                student: (student.name || "-") + " / " + (student.document || "-") + " / " + (student.email || "-"),
                program: (student.program || "No program") + " / Semester " + (student.semester || "-"),
                subject: (assignment.subject.code || "-") + " - " + (assignment.subject.name || "-"),
                professor: assignment.professor.name || "Unassigned",
                group: assignment.group.code || "No group",
                period: assignment.period || "-",
                status: assignment.status_label || assignment.status || "-",
            }))
        ),
    });
}

const csvExportUrl = computed(() => route("reports.student-assignments.export", exportPayload()));

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

function creditLabel(student) {
    return student.active_credits + "/" + student.minimum_credits + " credits";
}

function studentMeta(student) {
    return [student.document, student.email, student.program || "No program", "Semester " + student.semester].join(" / ");
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
    <CrudPageLayout title="Student Assignment Report"
        subtitle="Students, enrolled subjects, class groups and responsible professors">
        <template v-slot:actions>
            <BaseButton as="a" variant="secondary" :href="route('reports.index')">
                <i class="fa-solid fa-arrow-left mr-2" />
                Reports
            </BaseButton>
        </template>

        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-4 print:grid-cols-4">
                    <StatCard title="Students" :value="summary.students" icon="fa-solid fa-user-graduate" />
                    <StatCard title="Assignments" :value="summary.assignments" icon="fa-solid fa-book-open" />
                    <StatCard title="Active Credits" :value="summary.active_credits" icon="fa-solid fa-layer-group" />
                    <StatCard title="Minimum Credits" :value="summary.minimum_credits"
                        icon="fa-solid fa-scale-balanced" />
                </section>

                <SectionCard class="print:hidden">
                    <FilterPanel>
                        <template #search>
                            <TableSearch v-model="filterForm.search"
                                placeholder="Search student, document or email..." />
                        </template>

                        <template #filters>
                            <BaseSelect v-model="filterForm.academic_period_id" placeholder="Academic period"
                                :options="periodOptions" />
                            <BaseSelect v-model="filterForm.program_id" placeholder="Program"
                                :options="programOptions" />
                            <BaseSelect v-model="filterForm.professor_id" placeholder="Professor"
                                :options="professorOptions" />
                            <BaseSelect v-model="filterForm.status" placeholder="Enrollment status"
                                :options="statusOptions" />
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
                            Students And Assignments
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Each student is grouped with their subjects, responsible professors and class groups.
                        </p>
                    </div>

                    <div v-if="students.data.length" class="divide-y divide-border-light dark:divide-border-dark">
                        <article v-for="student in students.data" :key="student.id" class="p-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-base font-semibold text-ink dark:text-white">
                                            {{ student.name }}
                                        </h3>
                                        <StatusBadge
                                            :label="creditLabel(student)"
                                            :variant="creditVariant(student)" />
                                    </div>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ studentMeta(student) }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2 text-sm">
                                    <span
                                        class="rounded-lg border border-border-light bg-surface px-3 py-2 font-medium text-slate-700 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-200">
                                        {{ student.assignments_count }} assignments
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead
                                        class="border-b border-border-light text-left text-xs uppercase text-slate-500 dark:border-border-dark dark:text-slate-400">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Subject</th>
                                            <th class="px-4 py-3 font-semibold">Professor</th>
                                            <th class="px-4 py-3 font-semibold">Group</th>
                                            <th class="px-4 py-3 font-semibold">Period</th>
                                            <th class="px-4 py-3 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-light dark:divide-border-dark">
                                        <tr v-for="assignment in student.assignments" :key="assignment.id">
                                            <td class="px-4 py-4">
                                                <p class="font-medium text-ink dark:text-white">
                                                    {{ assignment.subject.code }} - {{ assignment.subject.name }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ assignment.subject.credits }} credits / {{
                                                    assignment.subject.area || "No area" }} / {{
                                                    formatSubjectType(assignment) }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-medium text-ink dark:text-white">
                                                    {{ assignment.professor.name }}
                                                </p>
                                                <p v-if="assignment.professor.email"
                                                    class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ assignment.professor.email }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4 text-slate-700 dark:text-zinc-300">
                                                {{ assignment.group.code || "No group" }}
                                            </td>
                                            <td class="px-4 py-4 text-slate-700 dark:text-zinc-300">
                                                {{ assignment.period || "-" }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <StatusBadge :label="assignment.status_label || assignment.status"
                                                    :variant="statusVariant(assignment.status)" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>

                    <div v-else class="p-6">
                        <EmptyState title="No assignments found"
                            description="Try adjusting the filters or wait until students have active academic assignments."
                            icon="fa-solid fa-file-lines" />
                    </div>

                    <TablePagination v-if="students.data.length" :data="students" class="print:hidden" />
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
