<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import { formatDate } from "@/Components/Composables/useDateTimeFormatter";

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
    record: {
        type: Object,
        required: true,
    },
    backRoute: {
        type: String,
        default: "student.subjects",
    },
    backRouteParams: {
        type: Array,
        default: () => [],
    },
});

const columns = [
    { key: "subject", label: "Subject" },
    { key: "credits", label: "Credits" },
    { key: "group", label: "Group" },
    { key: "professor", label: "Professor" },
    { key: "final_grade", label: "Final Grade" },
    { key: "grade_status", label: "Grade Status" },
    { key: "enrollment_status", label: "Enrollment" },
];

const periodRows = (period) =>
    period.courses.map((course) => ({
        ...course,
        subject: [course.subject_code, course.subject_name].filter(Boolean).join(" - "),
        final_grade: course.final_grade ?? "Pending",
    }));

const backHref = computed(() => {
    try {
        return route(props.backRoute, props.backRouteParams);
    } catch {
        return route("student.subjects");
    }
});

const formatPercent = (value) => `${Number(value || 0).toFixed(1)}%`;

const gradeVariant = (status) => ({
    passed: "success",
    failed: "danger",
    failed_attendance: "warning",
}[status] || "gray");

const enrollmentVariant = (status) => ({
    enrolled: "success",
    pre_enrolled: "warning",
    approved: "success",
    failed: "danger",
    withdrawn: "gray",
    cancelled: "gray",
}[status] || "gray");

const formatStatus = (status, fallback = "Pending") =>
    status ? status.replaceAll("_", " ").toUpperCase() : fallback;
</script>

<template>
    <CrudPageLayout
        title="Academic Record"
        subtitle="Official academic progress by period, credits and grades"
    >
        <template #actions>
            <Link :href="backHref">
                <BaseButton variant="secondary">
                    <i class="fa-solid fa-arrow-left mr-2" />
                    Back
                </BaseButton>
            </Link>
        </template>

        <CrudContainer>
            <div class="space-y-6">
                <SectionCard>
                    <div class="grid gap-6 p-6 lg:grid-cols-[1.4fr_1fr]">
                        <div>
                            <p class="font-mono text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-300">
                                Academic transcript preview
                            </p>

                            <h2 class="mt-2 text-2xl font-semibold text-ink dark:text-white">
                                {{ student.user?.name }}
                            </h2>

                            <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="font-medium text-slate-500 dark:text-zinc-400">Document</dt>
                                    <dd class="mt-1 font-mono text-ink dark:text-white">{{ student.document }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-slate-500 dark:text-zinc-400">Program</dt>
                                    <dd class="mt-1 text-ink dark:text-white">{{ student.program?.name ?? "N/A" }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-slate-500 dark:text-zinc-400">Curriculum</dt>
                                    <dd class="mt-1 text-ink dark:text-white">{{ student.curriculum?.name ?? "N/A" }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-slate-500 dark:text-zinc-400">Academic Status</dt>
                                    <dd class="mt-1">
                                        <StatusBadge
                                            :label="formatStatus(student.academic_status, 'N/A')"
                                            :variant="enrollmentVariant(student.academic_status)"
                                        />
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-xl border border-border-light bg-slate-50 p-5 dark:border-border-dark dark:bg-zinc-900">
                            <p class="text-sm font-semibold text-ink dark:text-white">
                                Record interpretation
                            </p>
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-zinc-400">
                                This view summarizes the student academic path from official enrollments and registered grades. It is designed as the base for a formal transcript workflow.
                            </p>
                        </div>
                    </div>
                </SectionCard>

                <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                    <StatCard title="Periods" :value="record.summary.periods" icon="fa-solid fa-calendar-days" />
                    <StatCard title="Subjects" :value="record.summary.subjects" icon="fa-solid fa-book-open" />
                    <StatCard title="Attempted Credits" :value="record.summary.attempted_credits" icon="fa-solid fa-layer-group" />
                    <StatCard title="Approved Credits" :value="record.summary.approved_credits" icon="fa-solid fa-circle-check" />
                    <StatCard title="Average" :value="record.summary.weighted_average ?? 'Pending'" icon="fa-solid fa-chart-line" />
                    <StatCard title="Completion" :value="formatPercent(record.summary.completion_rate)" icon="fa-solid fa-gauge-high" />
                </section>

                <div v-if="record.periods.length" class="space-y-6">
                    <SectionCard v-for="period in record.periods" :key="period.id ?? period.name">
                        <div class="flex flex-col gap-4 border-b border-border-light p-6 dark:border-border-dark lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-ink dark:text-white">
                                    {{ period.name }}
                                </h2>

                                <p class="mt-1 font-mono text-xs text-slate-500 dark:text-zinc-400">
                                    {{ formatDate(period.start_date, "No start date") }} - {{ formatDate(period.end_date, "No end date") }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                                <div class="rounded-lg border border-border-light bg-slate-50 px-3 py-2 dark:border-border-dark dark:bg-zinc-900">
                                    <p class="text-xs text-slate-500 dark:text-zinc-400">Subjects</p>
                                    <p class="font-mono text-lg font-semibold text-ink dark:text-white">{{ period.subjects }}</p>
                                </div>
                                <div class="rounded-lg border border-border-light bg-slate-50 px-3 py-2 dark:border-border-dark dark:bg-zinc-900">
                                    <p class="text-xs text-slate-500 dark:text-zinc-400">Attempted</p>
                                    <p class="font-mono text-lg font-semibold text-ink dark:text-white">{{ period.attempted_credits }}</p>
                                </div>
                                <div class="rounded-lg border border-border-light bg-slate-50 px-3 py-2 dark:border-border-dark dark:bg-zinc-900">
                                    <p class="text-xs text-slate-500 dark:text-zinc-400">Approved</p>
                                    <p class="font-mono text-lg font-semibold text-ink dark:text-white">{{ period.approved_credits }}</p>
                                </div>
                                <div class="rounded-lg border border-border-light bg-slate-50 px-3 py-2 dark:border-border-dark dark:bg-zinc-900">
                                    <p class="text-xs text-slate-500 dark:text-zinc-400">Average</p>
                                    <p class="font-mono text-lg font-semibold text-ink dark:text-white">{{ period.weighted_average ?? "Pending" }}</p>
                                </div>
                            </div>
                        </div>

                        <DataTable :columns="columns" :rows="periodRows(period)">
                            <template #cell-grade_status="{ row }">
                                <StatusBadge
                                    :label="row.grade_status_label ?? formatStatus(row.grade_status)"
                                    :variant="gradeVariant(row.grade_status)"
                                />
                            </template>

                            <template #cell-enrollment_status="{ row }">
                                <StatusBadge
                                    :label="row.enrollment_status_label ?? formatStatus(row.enrollment_status)"
                                    :variant="enrollmentVariant(row.enrollment_status)"
                                />
                            </template>
                        </DataTable>
                    </SectionCard>
                </div>

                <EmptyState
                    v-else
                    title="No academic record yet"
                    description="The transcript will appear when the student has enrollments connected to academic periods."
                    icon="fa-solid fa-scroll"
                />
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
