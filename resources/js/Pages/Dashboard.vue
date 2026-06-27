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

const metricCards = [
    {
        key: "active_enrollments",
        label: "Active enrollments",
        icon: "fa-solid fa-user-check",
        tone: "text-success bg-success/10 dark:bg-success/10 dark:text-success",
    },
    {
        key: "published_groups",
        label: "Published groups",
        icon: "fa-solid fa-layer-group",
        tone: "text-brand-600 bg-brand-50 dark:bg-brand-500/10 dark:text-brand-300",
    },
    {
        key: "schedule_conflicts",
        label: "Schedule conflicts",
        icon: "fa-solid fa-triangle-exclamation",
        tone: "text-warning bg-warning/10 dark:bg-warning/10 dark:text-warning",
    },
    {
        key: "capacity_utilization",
        label: "Seat utilization",
        icon: "fa-solid fa-chart-simple",
        suffix: "%",
        tone: "text-sky-600 bg-sky-50 dark:bg-sky-500/10 dark:text-sky-300",
    },
];

const professorMetricCards = [
    { key: "assigned_groups", label: "Assigned groups", icon: "fa-solid fa-layer-group" },
    { key: "active_students", label: "Active students", icon: "fa-solid fa-users" },
    { key: "pending_grades", label: "Pending grades", icon: "fa-solid fa-clipboard-check" },
    { key: "scheduled_blocks", label: "Scheduled blocks", icon: "fa-solid fa-calendar-days" },
];

const palette = ["#4f46e5", "#059669", "#f59e0b", "#dc2626", "#0891b2", "#7c3aed", "#475569", "#16a34a"];

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
                backgroundColor: palette[0],
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
                label: "Used seats",
                data: (items || []).map((item) => item.used),
                backgroundColor: "#4f46e5",
                borderRadius: 8,
                maxBarThickness: 34,
            },
            {
                label: "Capacity",
                data: (items || []).map((item) => item.capacity),
                backgroundColor: "#cbd5e1",
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
                label: "Enrollments",
                data: (items || []).map((item) => item.value),
                borderColor: "#4f46e5",
                backgroundColor: "rgba(79, 70, 229, 0.14)",
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
                label: "Graded",
                data: (items || []).map((item) => item.graded),
                backgroundColor: "#059669",
                borderRadius: 8,
            },
            {
                label: "Pending",
                data: (items || []).map((item) => item.pending),
                backgroundColor: "#f59e0b",
                borderRadius: 8,
            },
        ],
    };
}
</script>

<template>
    <AppLayout :title="dashboardType === 'professor' ? 'Teaching Dashboard' : 'Academic Dashboard'">
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-brand-600 dark:text-brand-300">
                        TARRAYA
                    </p>
                    <h1 class="text-2xl font-semibold text-ink dark:text-white">
                        {{ dashboardType === "professor" ? "Teaching Dashboard" : "Academic Operations Dashboard" }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ dashboardType === "professor"
                            ? "Your assigned groups, students and grading workload."
                            : "Operational health for enrollment, capacity, schedules and academic activity." }}
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
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article v-for="card in metricCards" :key="card.key"
                        class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
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

                <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-base font-semibold text-ink dark:text-white">Capacity Overview</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Current seat usage across published groups.</p>
                            </div>
                            <Link :href="route('class-groups.index')" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                                View groups
                            </Link>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-lg bg-slate-50 p-4 dark:bg-zinc-900">
                                <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Used seats</p>
                                <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ academicDashboard.capacity?.used_seats ?? 0 }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-4 dark:bg-zinc-900">
                                <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Available seats</p>
                                <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ academicDashboard.capacity?.available_seats ?? 0 }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-4 dark:bg-zinc-900">
                                <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Full groups</p>
                                <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ academicDashboard.metrics?.full_groups ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            <div v-for="group in academicDashboard.capacity?.high_occupancy_groups || []" :key="group.id">
                                <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                    <span class="font-medium text-slate-800 dark:text-zinc-200">{{ group.code }} - {{ group.subject }}</span>
                                    <span class="text-slate-500 dark:text-slate-400">{{ group.enrollments }}/{{ group.capacity }} · {{ group.occupancy }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100 dark:bg-zinc-900">
                                    <div class="h-2 rounded-full" :class="occupancyColor(group.occupancy)" :style="{ width: `${Math.min(group.occupancy, 100)}%` }"></div>
                                </div>
                            </div>
                            <p v-if="!(academicDashboard.capacity?.high_occupancy_groups || []).length" class="text-sm text-slate-500 dark:text-slate-400">
                                No high-occupancy groups detected.
                            </p>
                        </div>
                    </article>

                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <h2 class="text-base font-semibold text-ink dark:text-white">Enrollment Status</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Distribution by academic enrollment state.</p>

                        <div class="mt-5 space-y-3">
                            <div v-for="status in academicDashboard.enrollmentStatus || []" :key="status.label" class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3 dark:bg-zinc-900">
                                <span class="text-sm font-medium text-slate-700 dark:text-zinc-300">{{ status.label }}</span>
                                <span class="text-sm font-semibold text-ink dark:text-white">{{ status.value }}</span>
                            </div>
                            <p v-if="!(academicDashboard.enrollmentStatus || []).length" class="text-sm text-slate-500 dark:text-slate-400">
                                No enrollment records yet.
                            </p>
                        </div>
                    </article>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-base font-semibold text-ink dark:text-white">Enrollment Trend</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">New enrollment activity for the active academic period.</p>
                            </div>
                        </div>

                        <div class="mt-5 h-72">
                            <Line
                                v-if="(academicDashboard.charts?.enrollment_trend || []).length"
                                :data="trendData(academicDashboard.charts.enrollment_trend)"
                                :options="axisChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                No enrollment activity to chart yet.
                            </p>
                        </div>
                    </article>

                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <h2 class="text-base font-semibold text-ink dark:text-white">Status Mix</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Enrollment states at a glance.</p>

                        <div class="mt-5 h-72">
                            <Doughnut
                                v-if="(academicDashboard.charts?.status_distribution || []).length"
                                :data="doughnutData(academicDashboard.charts.status_distribution, 'Enrollments')"
                                :options="baseChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                No enrollment status data yet.
                            </p>
                        </div>
                    </article>
                </section>

                <section class="grid gap-6 xl:grid-cols-2">
                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <h2 class="text-base font-semibold text-ink dark:text-white">Capacity By Group</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Used seats compared with configured group capacity.</p>

                        <div class="mt-5 h-72">
                            <Bar
                                v-if="(academicDashboard.charts?.capacity_by_group || []).length"
                                :data="capacityChartData(academicDashboard.charts.capacity_by_group)"
                                :options="axisChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                No published group capacity data yet.
                            </p>
                        </div>
                    </article>

                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <h2 class="text-base font-semibold text-ink dark:text-white">Subject Areas</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Academic offering distribution by knowledge area.</p>

                        <div class="mt-5 h-72">
                            <Bar
                                v-if="(academicDashboard.charts?.subject_areas || []).length"
                                :data="simpleBarData(academicDashboard.charts.subject_areas, 'Subjects')"
                                :options="axisChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                No subject area data yet.
                            </p>
                        </div>
                    </article>
                </section>

                <section class="grid gap-6 xl:grid-cols-3">
                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <h2 class="text-base font-semibold text-ink dark:text-white">Schedule Conflicts</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Overlaps by classroom or professor.</p>

                        <div class="mt-5 space-y-4">
                            <div v-for="conflict in academicDashboard.scheduleConflicts || []" :key="conflict.id" class="rounded-lg border border-warning/30 bg-warning/10 p-4 dark:border-warning/30 dark:bg-warning/10">
                                <p class="text-sm font-semibold text-amber-900 dark:text-warning">{{ conflict.day }} · {{ conflict.time }}</p>
                                <p class="mt-1 text-sm text-amber-800 dark:text-warning">{{ conflict.first_group }} conflicts with {{ conflict.second_group }}</p>
                                <p class="mt-1 text-xs text-amber-700 dark:text-warning">{{ conflict.classroom || "Professor overlap" }}</p>
                            </div>
                            <p v-if="!(academicDashboard.scheduleConflicts || []).length" class="text-sm text-slate-500 dark:text-slate-400">
                                No schedule conflicts detected.
                            </p>
                        </div>
                    </article>

                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <h2 class="text-base font-semibold text-ink dark:text-white">Professor Load</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Groups and active students by professor.</p>

                        <div class="mt-5 space-y-3">
                            <div v-for="professor in academicDashboard.professorLoad || []" :key="professor.name" class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3 dark:bg-zinc-900">
                                <div>
                                    <p class="text-sm font-medium text-slate-800 dark:text-zinc-200">{{ professor.name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ professor.groups }} groups</p>
                                </div>
                                <span class="text-sm font-semibold text-ink dark:text-white">{{ professor.students }} students</span>
                            </div>
                            <p v-if="!(academicDashboard.professorLoad || []).length" class="text-sm text-slate-500 dark:text-slate-400">
                                No assigned professor load yet.
                            </p>
                        </div>
                    </article>

                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <h2 class="text-base font-semibold text-ink dark:text-white">Needs Attention</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Operational issues to resolve first.</p>

                        <div class="mt-5 space-y-3">
                            <div v-for="item in academicDashboard.attentionItems || []" :key="`${item.type}-${item.description}`" class="rounded-lg border border-border-light p-4 dark:border-border-dark">
                                <p class="text-sm font-semibold text-ink dark:text-white">{{ item.title }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ item.description }}</p>
                            </div>
                            <p v-if="!(academicDashboard.attentionItems || []).length" class="text-sm text-slate-500 dark:text-slate-400">
                                No critical attention items found.
                            </p>
                        </div>
                    </article>
                </section>

                <section class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-ink dark:text-white">Recent Academic Events</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Latest audited operations across the academic workflow.</p>
                        </div>
                        <Link :href="route('academic-audit-logs.index')" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                            View audit logs
                        </Link>
                    </div>

                    <div class="mt-5 divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="event in academicDashboard.recentEvents || []" :key="event.id" class="grid gap-2 py-4 sm:grid-cols-[10rem_1fr_10rem] sm:items-center">
                            <p class="text-sm font-medium text-ink dark:text-white">{{ event.action }}</p>
                            <p class="text-sm text-slate-600 dark:text-zinc-300">{{ event.summary || "No summary available" }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 sm:text-right">{{ formatDateTime(event.created_at) }}</p>
                        </div>
                        <p v-if="!(academicDashboard.recentEvents || []).length" class="py-4 text-sm text-slate-500 dark:text-slate-400">
                            No audited events yet.
                        </p>
                    </div>
                </section>

                <section class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-ink dark:text-white">Student Assignments Overview</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Quick view of active student loads and assigned groups. Use the full report for filtering and exports.
                            </p>
                        </div>
                        <Link :href="route('reports.student-assignments.index')" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                            Open full report
                        </Link>
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table v-if="(academicDashboard.assignmentPreview || []).length" class="min-w-full text-sm">
                            <thead class="border-b border-slate-100 text-left text-xs uppercase text-slate-500 dark:border-border-dark dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Student</th>
                                    <th class="px-4 py-3 font-semibold">Document</th>
                                    <th class="px-4 py-3 font-semibold">Credits</th>
                                    <th class="px-4 py-3 font-semibold">Recent assignments</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
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
                                            {{ student.credits }} credits
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="space-y-2">
                                            <div v-for="assignment in student.subjects.slice(0, 3)" :key="`${student.id}-${assignment.code}-${assignment.group}`"
                                                class="rounded-lg border border-border-light bg-slate-50 px-3 py-2 dark:border-border-dark dark:bg-zinc-900/80">
                                                <p class="font-medium text-ink dark:text-white">
                                                    {{ assignment.code }} - {{ assignment.subject }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ assignment.professor }} - {{ assignment.group || "No group" }}
                                                </p>
                                            </div>
                                            <details v-if="student.subjects.length > 3"
                                                class="rounded-lg border border-border-light bg-slate-50 px-3 py-2 dark:border-border-dark dark:bg-zinc-900/80">
                                                <summary class="cursor-pointer text-xs font-semibold text-brand-600 dark:text-brand-300">
                                                    {{ student.subjects.length - 3 }} more in full report
                                                </summary>
                                                <div class="mt-2 space-y-2">
                                                    <div v-for="assignment in student.subjects.slice(3)"
                                                        :key="`${student.id}-${assignment.code}-${assignment.group}-extra`"
                                                        class="rounded-lg border border-border-light bg-surface px-3 py-2 dark:border-border-dark dark:bg-surface-dark">
                                                        <p class="font-medium text-ink dark:text-white">
                                                            {{ assignment.code }} - {{ assignment.subject }}
                                                        </p>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                                            {{ assignment.professor }} - {{ assignment.group || "No group" }}
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
                            No student assignments available yet.
                        </p>
                    </div>
                </section>
            </div>

            <div v-else class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article v-for="card in professorMetricCards" :key="card.key"
                        class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
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

                <section class="grid gap-6 xl:grid-cols-2">
                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <h2 class="text-base font-semibold text-ink dark:text-white">Students By Group</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Active students across your assigned groups.</p>

                        <div class="mt-5 h-72">
                            <Bar
                                v-if="(professorDashboard.charts?.students_by_group || []).length"
                                :data="simpleBarData(professorDashboard.charts.students_by_group, 'Students')"
                                :options="axisChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                No assigned student load yet.
                            </p>
                        </div>
                    </article>

                    <article class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <h2 class="text-base font-semibold text-ink dark:text-white">Grading Progress</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Completed and pending grades by group.</p>

                        <div class="mt-5 h-72">
                            <Bar
                                v-if="(professorDashboard.charts?.grading_progress || []).length"
                                :data="gradingProgressData(professorDashboard.charts.grading_progress)"
                                :options="axisChartOptions"
                            />
                            <p v-else class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                                No grading workload to chart yet.
                            </p>
                        </div>
                    </article>
                </section>

                <section class="rounded-lg border border-border-light bg-surface p-5 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-ink dark:text-white">Assigned Groups</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Your current teaching workload and grading queue.</p>
                        </div>
                        <Link :href="route('professor.subjects')" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                            My subjects
                        </Link>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <article v-for="group in professorDashboard.groups || []" :key="group.id" class="rounded-lg border border-border-light p-4 dark:border-border-dark">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-ink dark:text-white">{{ group.code }}</p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ group.subject }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-zinc-900 dark:text-zinc-300">
                                    {{ group.status }}
                                </span>
                            </div>
                            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-slate-500 dark:text-slate-400">Students</dt>
                                    <dd class="font-semibold text-ink dark:text-white">{{ group.students }}</dd>
                                </div>
                                <div>
                                    <dt class="text-slate-500 dark:text-slate-400">Pending grades</dt>
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
