<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from "chart.js";
import { Bar, Doughnut, Line } from "vue-chartjs";
import { formatDateTime } from "@/Components/Composables/useDateTimeFormatter";
import { useTranslations } from "@/Components/Composables/useTranslations";

ChartJS.register(
    ArcElement,
    BarElement,
    CategoryScale,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip
);

const props = defineProps({
    dashboardType: {
        type: String,
        default: "academic",
    },
    academicDashboard: {
        type: Object,
        default: () => ({}),
    },
    professorDashboard: {
        type: Object,
        default: () => ({}),
    },
});

const { t } = useTranslations();

const metricCards = [
    {
        key: "active_enrollments",
        label: t("dashboard.metrics.active_enrollments"),
        icon: "fa-solid fa-user-check",
        tone: "text-success bg-success/10 dark:bg-success/10 dark:text-success",
    },
    {
        key: "published_groups",
        label: t("dashboard.metrics.published_groups"),
        icon: "fa-solid fa-layer-group",
        tone: "text-brand-600 bg-brand-50 dark:bg-brand-500/10 dark:text-brand-300",
    },
    {
        key: "schedule_conflicts",
        label: t("dashboard.metrics.schedule_conflicts"),
        icon: "fa-solid fa-triangle-exclamation",
        tone: "text-warning bg-warning/10 dark:bg-warning/10 dark:text-warning",
    },
    {
        key: "capacity_utilization",
        label: t("dashboard.metrics.capacity_utilization"),
        icon: "fa-solid fa-chart-simple",
        suffix: "%",
        tone: "text-accent bg-accent/10 dark:bg-accent/10 dark:text-accent",
    },
];

const professorMetricCards = [
    { key: "assigned_groups", label: t("dashboard.metrics.assigned_groups"), icon: "fa-solid fa-layer-group" },
    { key: "active_students", label: t("dashboard.metrics.active_students"), icon: "fa-solid fa-users" },
    { key: "pending_grades", label: t("dashboard.metrics.pending_grades"), icon: "fa-solid fa-clipboard-check" },
    { key: "scheduled_blocks", label: t("dashboard.metrics.scheduled_blocks"), icon: "fa-solid fa-calendar-days" },
];

const chartColors = {
    brand: "#2563EB",
    brandSoft: "rgba(37, 99, 235, 0.14)",
    accent: "#06B6D4",
    success: "#10B981",
    warning: "#F59E0B",
    danger: "#EF4444",
    ink: "#0F172A",
    slate: "#5C6B73",
    border: "#E2E8F0",
};

const palette = [
    chartColors.brand,
    chartColors.success,
    chartColors.warning,
    chartColors.danger,
    chartColors.accent,
    chartColors.ink,
    chartColors.slate,
];

const baseChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "bottom",
            labels: {
                boxWidth: 10,
                boxHeight: 10,
                usePointStyle: true,
            },
        },
        tooltip: {
            backgroundColor: "#111827",
            padding: 12,
            titleFont: { weight: "600" },
        },
    },
};

const axisChartOptions = {
    ...baseChartOptions,
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: "#64748b" },
        },
        y: {
            beginAtZero: true,
            grid: { color: "rgba(148, 163, 184, 0.18)" },
            ticks: {
                color: "#64748b",
                precision: 0,
            },
        },
    },
};

function valueFor(metrics, card) {
    return `${metrics?.[card.key] ?? 0}${card.suffix || ""}`;
}

function occupancyColor(value) {
    if (value >= 100) return "bg-danger/100";
    if (value >= 90) return "bg-warning/100";
    return "bg-success/100";
}

function simpleBarData(items, label) {
    return {
        labels: (items || []).map((item) => item.label),
        datasets: [
            {
                label,
                data: (items || []).map((item) => item.value),
                backgroundColor: chartColors.brand,
                borderRadius: 8,
                maxBarThickness: 42,
            },
        ],
    };
}

function capacityChartData(items) {
    return {
        labels: (items || []).map((item) => item.label),
        datasets: [
            {
                label: t("dashboard.used_seats"),
                data: (items || []).map((item) => item.used),
                backgroundColor: chartColors.brand,
                borderRadius: 8,
                maxBarThickness: 34,
            },
            {
                label: t("dashboard.capacity"),
                data: (items || []).map((item) => item.capacity),
                backgroundColor: chartColors.border,
                borderRadius: 8,
                maxBarThickness: 34,
            },
        ],
    };
}

function doughnutData(items, label) {
    return {
        labels: (items || []).map((item) => item.label),
        datasets: [
            {
                label,
                data: (items || []).map((item) => item.value),
                backgroundColor: palette,
                borderColor: "#ffffff",
                borderWidth: 2,
            },
        ],
    };
}

function trendData(items) {
    return {
        labels: (items || []).map((item) => item.label),
        datasets: [
            {
                label: t("dashboard.enrollments"),
                data: (items || []).map((item) => item.value),
                borderColor: chartColors.brand,
                backgroundColor: chartColors.brandSoft,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
        ],
    };
}

function gradingProgressData(items) {
    return {
        labels: (items || []).map((item) => item.label),
        datasets: [
            {
                label: t("dashboard.graded"),
                data: (items || []).map((item) => item.graded),
                backgroundColor: chartColors.success,
                borderRadius: 8,
            },
            {
                label: t("common.pending"),
                data: (items || []).map((item) => item.pending),
                backgroundColor: chartColors.warning,
                borderRadius: 8,
            },
        ],
    };
}
</script>

<template>
    <AppLayout :title="dashboardType === 'professor' ? t('dashboard.professor_title') : t('dashboard.academic_page_title')">
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-brand-600 dark:text-brand-300">
                        TARRAYA
                    </p>
                    <h1 class="text-2xl font-semibold text-ink dark:text-white">
                        {{ dashboardType === "professor" ? t("dashboard.professor_title") : t("dashboard.academic_title") }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ dashboardType === "professor"
                            ? t("dashboard.professor_subtitle")
                            : t("dashboard.academic_subtitle") }}
                    </p>
                </div>

                <div v-if="dashboardType === 'academic' && academicDashboard.activePeriod"
                    class="inline-flex items-center gap-2 rounded-lg border border-border-light bg-surface px-4 py-2 text-sm dark:border-border-dark dark:bg-surface-dark">
                    <i class="fa-solid fa-calendar-check text-brand-500"></i>
                    <span class="font-medium text-ink dark:text-white">{{ academicDashboard.activePeriod.name }}</span>
                    <span class="text-slate-500 dark:text-slate-400">{{ academicDashboard.activePeriod.status_label }}</span>
                </div>
            </div>
        </template>

        <main class="min-h-screen bg-slate-50 py-6 dark:bg-dark-bg">
            <div v-if="dashboardType === 'academic'" class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-12">
                    <article v-for="card in metricCards" :key="card.key"
                        class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                                <p class="mt-3 text-3xl font-semibold text-ink dark:text-white">
                                    {{ valueFor(academicDashboard.metrics, card) }}
                                </p>
                            </div>
                            <span :class="['inline-flex h-10 w-10 items-center justify-center rounded-lg', card.tone]">
                                <i :class="card.icon"></i>
                            </span>
                        </div>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-8">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.capacity_overview") }}</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.capacity_overview_description") }}</p>
                            </div>
                            <Link :href="route('class-groups.index')" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                                {{ t("dashboard.view_groups") }}
                            </Link>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-xl border border-border-light bg-slate-50 p-4 dark:border-border-dark dark:bg-dark-bg">
                                <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ t("dashboard.used_seats") }}</p>
                                <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ academicDashboard.capacity?.used_seats ?? 0 }}</p>
                            </div>
                            <div class="rounded-xl border border-border-light bg-slate-50 p-4 dark:border-border-dark dark:bg-dark-bg">
                                <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ t("dashboard.available_seats") }}</p>
                                <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ academicDashboard.capacity?.available_seats ?? 0 }}</p>
                            </div>
                            <div class="rounded-xl border border-border-light bg-slate-50 p-4 dark:border-border-dark dark:bg-dark-bg">
                                <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ t("dashboard.full_groups") }}</p>
                                <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ academicDashboard.metrics?.full_groups ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            <div v-for="group in academicDashboard.capacity?.high_occupancy_groups || []" :key="group.id">
                                <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                    <span class="font-medium text-slate-800 dark:text-zinc-200">{{ group.code }} - {{ group.subject }}</span>
                                    <span class="text-slate-500 dark:text-slate-400">{{ group.enrollments }}/{{ group.capacity }} · {{ group.occupancy }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100 dark:bg-dark-bg">
                                    <div class="h-2 rounded-full" :class="occupancyColor(group.occupancy)" :style="{ width: `${Math.min(group.occupancy, 100)}%` }"></div>
                                </div>
                            </div>
                            <p v-if="!(academicDashboard.capacity?.high_occupancy_groups || []).length" class="text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_high_occupancy") }}
                            </p>
                        </div>
                    </article>

                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-4">
                        <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.enrollment_status") }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.enrollment_status_description") }}</p>

                        <div class="mt-5 space-y-3">
                            <div v-for="status in academicDashboard.enrollmentStatus || []" :key="status.label" class="flex items-center justify-between rounded-xl border border-border-light bg-slate-50 px-4 py-3 dark:border-border-dark dark:bg-dark-bg">
                                <span class="text-sm font-medium text-slate-700 dark:text-zinc-300">{{ status.label }}</span>
                                <span class="text-sm font-semibold text-ink dark:text-white">{{ status.value }}</span>
                            </div>
                            <p v-if="!(academicDashboard.enrollmentStatus || []).length" class="text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_enrollment_records") }}
                            </p>
                        </div>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.enrollment_trend") }}</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.enrollment_trend_description") }}</p>
                            </div>
                        </div>

                        <div class="mt-5 h-72">
                            <Line
                                v-if="(academicDashboard.charts?.enrollment_trend || []).length"
                                :data="trendData(academicDashboard.charts.enrollment_trend)"
                                :options="axisChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_enrollment_activity") }}
                            </p>
                        </div>
                    </article>

                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-4">
                        <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.status_mix") }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.status_mix_description") }}</p>

                        <div class="mt-5 h-72">
                            <Doughnut
                                v-if="(academicDashboard.charts?.status_distribution || []).length"
                                :data="doughnutData(academicDashboard.charts.status_distribution, t('dashboard.enrollments'))"
                                :options="baseChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_status_data") }}
                            </p>
                        </div>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-6">
                        <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.capacity_by_group") }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.capacity_by_group_description") }}</p>

                        <div class="mt-5 h-72">
                            <Bar
                                v-if="(academicDashboard.charts?.capacity_by_group || []).length"
                                :data="capacityChartData(academicDashboard.charts.capacity_by_group)"
                                :options="axisChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_capacity_data") }}
                            </p>
                        </div>
                    </article>

                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-6">
                        <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.subject_areas") }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.subject_areas_description") }}</p>

                        <div class="mt-5 h-72">
                            <Bar
                                v-if="(academicDashboard.charts?.subject_areas || []).length"
                                :data="simpleBarData(academicDashboard.charts.subject_areas, t('dashboard.subjects'))"
                                :options="axisChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_subject_area_data") }}
                            </p>
                        </div>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-4">
                        <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.schedule_conflicts_title") }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.schedule_conflicts_description") }}</p>

                        <div class="mt-5 space-y-4">
                            <div v-for="conflict in academicDashboard.scheduleConflicts || []" :key="conflict.id" class="rounded-xl border border-warning/30 bg-warning/10 p-4 dark:border-warning/30 dark:bg-warning/10">
                                <p class="text-sm font-semibold text-amber-900 dark:text-warning">{{ conflict.day }} · {{ conflict.time }}</p>
                                <p class="mt-1 text-sm text-amber-800 dark:text-warning">{{ conflict.first_group }} conflicts with {{ conflict.second_group }}</p>
                                <p class="mt-1 text-xs text-amber-700 dark:text-warning">{{ conflict.classroom || t("dashboard.professor_overlap") }}</p>
                            </div>
                            <p v-if="!(academicDashboard.scheduleConflicts || []).length" class="text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_schedule_conflicts") }}
                            </p>
                        </div>
                    </article>

                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-4">
                        <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.professor_load") }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.professor_load_description") }}</p>

                        <div class="mt-5 space-y-3">
                            <div v-for="professor in academicDashboard.professorLoad || []" :key="professor.name" class="flex items-center justify-between rounded-xl border border-border-light bg-slate-50 px-4 py-3 dark:border-border-dark dark:bg-dark-bg">
                                <div>
                                    <p class="text-sm font-medium text-slate-800 dark:text-zinc-200">{{ professor.name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ t("dashboard.groups_count", null, { count: professor.groups }) }}</p>
                                </div>
                                <span class="text-sm font-semibold text-ink dark:text-white">{{ t("dashboard.students_count", null, { count: professor.students }) }}</span>
                            </div>
                            <p v-if="!(academicDashboard.professorLoad || []).length" class="text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_professor_load") }}
                            </p>
                        </div>
                    </article>

                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-4">
                        <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.needs_attention") }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.needs_attention_description") }}</p>

                        <div class="mt-5 space-y-3">
                            <div v-for="item in academicDashboard.attentionItems || []" :key="`${item.type}-${item.description}`" class="rounded-xl border border-border-light p-4 dark:border-border-dark">
                                <p class="text-sm font-semibold text-ink dark:text-white">{{ item.title }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ item.description }}</p>
                            </div>
                            <p v-if="!(academicDashboard.attentionItems || []).length" class="text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_attention_items") }}
                            </p>
                        </div>
                    </article>
                </section>

                <section class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.recent_events") }}</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.recent_events_description") }}</p>
                        </div>
                        <Link :href="route('academic-audit-logs.index')" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                            {{ t("dashboard.view_audit_logs") }}
                        </Link>
                    </div>

                    <div class="mt-5 divide-y divide-border-light dark:divide-border-dark">
                        <div v-for="event in academicDashboard.recentEvents || []" :key="event.id" class="grid gap-2 py-4 sm:grid-cols-[10rem_1fr_10rem] sm:items-center">
                            <p class="text-sm font-medium text-ink dark:text-white">{{ event.action }}</p>
                            <p class="text-sm text-slate-600 dark:text-zinc-300">{{ event.summary || t("dashboard.no_summary") }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 sm:text-right">{{ formatDateTime(event.created_at) }}</p>
                        </div>
                        <p v-if="!(academicDashboard.recentEvents || []).length" class="py-4 text-sm text-slate-500 dark:text-slate-400">
                            {{ t("dashboard.no_audited_events") }}
                        </p>
                    </div>
                </section>

                <section class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.student_assignments_overview") }}</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.student_assignments_description") }}
                            </p>
                        </div>
                        <Link :href="route('reports.student-assignments.index')" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                            {{ t("dashboard.open_full_report") }}
                        </Link>
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table v-if="(academicDashboard.assignmentPreview || []).length" class="min-w-full text-sm">
                            <thead class="border-b border-border-light text-left text-xs uppercase text-slate-500 dark:border-border-dark dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">{{ t("academic_requests.student") }}</th>
                                    <th class="px-4 py-3 font-semibold">{{ t("dashboard.document") }}</th>
                                    <th class="px-4 py-3 font-semibold">{{ t("common.credits") }}</th>
                                    <th class="px-4 py-3 font-semibold">{{ t("dashboard.recent_assignments") }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                                <tr v-for="student in academicDashboard.assignmentPreview" :key="student.id">
                                    <td class="px-4 py-4 font-medium text-ink dark:text-white">{{ student.name }}</td>
                                    <td class="px-4 py-4 text-slate-600 dark:text-zinc-300">{{ student.document }}</td>
                                    <td class="px-4 py-4">
                                        <span :class="[
                                            'inline-flex rounded-full border px-3 py-1 text-xs font-semibold',
                                            student.credits >= 7
                                                ? 'border-success/30 bg-success/10 text-emerald-800 dark:border-success/30 dark:bg-success/15 dark:text-success'
                                                : 'border-warning/30 bg-warning/10 text-amber-800 dark:border-warning/30 dark:bg-warning/15 dark:text-warning'
                                        ]">
                                            {{ t("dashboard.credits_count", null, { count: student.credits }) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="space-y-2">
                                            <div v-for="assignment in student.subjects.slice(0, 3)" :key="`${student.id}-${assignment.code}-${assignment.group}`"
                                                class="rounded-xl border border-border-light bg-slate-50 px-3 py-2 dark:border-border-dark dark:bg-dark-bg">
                                                <p class="font-medium text-ink dark:text-white">
                                                    {{ assignment.code }} - {{ assignment.subject }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ assignment.professor }} - {{ assignment.group || t("common.no_group") }}
                                                </p>
                                            </div>
                                            <details v-if="student.subjects.length > 3"
                                                class="rounded-xl border border-border-light bg-slate-50 px-3 py-2 dark:border-border-dark dark:bg-dark-bg">
                                                <summary class="cursor-pointer text-xs font-semibold text-brand-600 dark:text-brand-300">
                                                    {{ t("dashboard.more_in_full_report", null, { count: student.subjects.length - 3 }) }}
                                                </summary>
                                                <div class="mt-2 space-y-2">
                                                    <div v-for="assignment in student.subjects.slice(3)"
                                                        :key="`${student.id}-${assignment.code}-${assignment.group}-extra`"
                                                        class="rounded-xl border border-border-light bg-surface px-3 py-2 dark:border-border-dark dark:bg-surface-dark">
                                                        <p class="font-medium text-ink dark:text-white">
                                                            {{ assignment.code }} - {{ assignment.subject }}
                                                        </p>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                                            {{ assignment.professor }} - {{ assignment.group || t("common.no_group") }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </details>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p v-else class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                            {{ t("dashboard.no_assignments") }}
                        </p>
                    </div>
                </section>
            </div>

            <div v-else class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-12">
                    <article v-for="card in professorMetricCards" :key="card.key"
                        class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                                <p class="mt-3 text-3xl font-semibold text-ink dark:text-white">
                                    {{ professorDashboard.metrics?.[card.key] ?? 0 }}
                                </p>
                            </div>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                <i :class="card.icon"></i>
                            </span>
                        </div>
                    </article>
                </section>

                <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-6">
                        <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.students_by_group") }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.students_by_group_description") }}</p>

                        <div class="mt-5 h-72">
                            <Bar
                                v-if="(professorDashboard.charts?.students_by_group || []).length"
                                :data="simpleBarData(professorDashboard.charts.students_by_group, t('common.students'))"
                                :options="axisChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_student_load") }}
                            </p>
                        </div>
                    </article>

                    <article class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark xl:col-span-6">
                        <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.grading_progress") }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.grading_progress_description") }}</p>

                        <div class="mt-5 h-72">
                            <Bar
                                v-if="(professorDashboard.charts?.grading_progress || []).length"
                                :data="gradingProgressData(professorDashboard.charts.grading_progress)"
                                :options="axisChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                {{ t("dashboard.no_grading_workload") }}
                            </p>
                        </div>
                    </article>
                </section>

                <section class="rounded-xl border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-ink dark:text-white">{{ t("dashboard.assigned_groups") }}</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ t("dashboard.assigned_groups_description") }}</p>
                        </div>
                        <Link :href="route('professor.subjects')" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                            {{ t("dashboard.my_subjects") }}
                        </Link>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <article v-for="group in professorDashboard.groups || []" :key="group.id" class="rounded-xl border border-border-light p-4 dark:border-border-dark">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-ink dark:text-white">{{ group.code }}</p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ group.subject }}</p>
                                </div>
                                <span class="rounded-full border border-border-light bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 dark:border-border-dark dark:bg-dark-bg dark:text-zinc-300">
                                    {{ group.status }}
                                </span>
                            </div>
                            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-slate-500 dark:text-slate-400">{{ t("common.students") }}</dt>
                                    <dd class="font-semibold text-ink dark:text-white">{{ group.students }}</dd>
                                </div>
                                <div>
                                    <dt class="text-slate-500 dark:text-slate-400">{{ t("dashboard.metrics.pending_grades") }}</dt>
                                    <dd class="font-semibold text-ink dark:text-white">{{ group.pending_grades }}</dd>
                                </div>
                            </dl>
                        </article>
                    </div>
                </section>
            </div>
        </main>
    </AppLayout>
</template>
