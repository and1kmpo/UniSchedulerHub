<script setup>
import { computed, ref } from "vue";
import axios from "axios";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },

    summary: {
        type: Object,
        default: () => ({
            current_credits: 0,
            active_subjects: 0,
            graded_subjects: 0,
        }),
    },

    currentPeriod: {
        type: Object,
        default: null,
    },

});

const columns = [
    { key: "name", label: "Subject" },
    { key: "credits", label: "Credits" },
    { key: "group", label: "Group" },
    { key: "professor_name", label: "Professor" },
    { key: "schedule_summary", label: "Schedule" },
    { key: "status", label: "Status" },
    { key: "final_grade", label: "Final Grade" },
];

const selectedGrade = ref(null);
const selectedSubject = ref(null);
const isGradeModalOpen = ref(false);
const showSummary = ref(false);
const allGrades = ref([]);
const gradesLoading = ref(false);
const gradesError = ref(null);
const summaryLoading = ref(false);
const summaryError = ref(null);

const rows = computed(() =>
    props.subjects.map((subject) => ({
        ...subject,
        schedule_summary: formatSchedules(subject.schedules),
        final_grade: subject.grade?.final_grade ?? "Pending",
        grade_state_label: subject.grade_state?.label ?? "Pending",
    }))
);

const statusVariant = (status) => ({
    enrolled: "success",
    pre_enrolled: "warning",
    approved: "success",
    failed: "danger",
    cancelled: "gray",
    withdrawn: "gray",
}[status] || "gray");

const gradeVariant = (state) => ({
    passed: "success",
    failed: "danger",
    failed_attendance: "warning",
}[state] || "gray");

function formatStatus(status) {
    return status ? status.replaceAll("_", " ").toUpperCase() : "PENDING";
}

function formatDay(day) {
    return day ? day.charAt(0).toUpperCase() + day.slice(1) : "";
}

function formatDate(date) {
    if (!date) {
        return "Not defined";
    }

    return new Intl.DateTimeFormat("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(new Date(date));
}

function formatTime(time) {
    const [hours = "0", minutes = "0"] = String(time || "00:00").split(":");
    const date = new Date(2026, 0, 5, Number(hours), Number(minutes));

    return new Intl.DateTimeFormat("en-US", {
        hour: "numeric",
        minute: Number(minutes) === 0 ? undefined : "2-digit",
        hour12: true,
    }).format(date);
}

function formatSchedules(schedules = []) {
    if (!schedules.length) {
        return "Pending";
    }

    return schedules
        .map((schedule) => {
            const room = schedule.classroom ? ` - ${schedule.classroom}` : "";

            return `${formatDay(schedule.day)} ${formatTime(schedule.start_time)}-${formatTime(schedule.end_time)}${room}`;
        })
        .join("; ");
}

const viewGrades = async (subject) => {
    selectedSubject.value = subject;
    selectedGrade.value = null;
    gradesError.value = null;
    gradesLoading.value = true;

    try {
        const response = await axios.get(route("student.subject.grades.json", subject.id));
        selectedGrade.value = response.data.grade;
    } catch (error) {
        selectedGrade.value = null;
        gradesError.value = "Grades could not be loaded. Try again later.";
    } finally {
        gradesLoading.value = false;
    }

    isGradeModalOpen.value = true;
};

const closeGradeModal = () => {
    isGradeModalOpen.value = false;
    selectedGrade.value = null;
    selectedSubject.value = null;
    gradesError.value = null;
};

const loadAllGrades = async () => {
    summaryLoading.value = true;
    summaryError.value = null;

    try {
        const response = await axios.get(route("student.grades.summary"));
        allGrades.value = response.data.grades;
    } catch (error) {
        allGrades.value = [];
        summaryError.value = "Grade summary could not be loaded. Try again later.";
    } finally {
        summaryLoading.value = false;
    }
};

const handleOpenSummary = async () => {
    await loadAllGrades();
    showSummary.value = true;
};
</script>

<template>
    <CrudPageLayout title="My Subjects" subtitle="Current enrollments, schedule and academic progress">
        <template #actions>
            <div class="flex flex-col gap-2 sm:flex-row">
                <BaseButton variant="secondary" @click="handleOpenSummary">
                    <i class="fa-solid fa-list mr-2" />
                    Grade Summary
                </BaseButton>
            </div>
        </template>

        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <StatCard title="Active Subjects" :value="summary.active_subjects" icon="fa-solid fa-book-open" />
                    <StatCard title="Current Credits" :value="summary.current_credits" icon="fa-solid fa-layer-group" />
                    <StatCard title="With Grades" :value="summary.graded_subjects" icon="fa-solid fa-chart-line" />
                </section>

                <SectionCard>
                    <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Current Academic Period
                            </h2>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ currentPeriod?.name ?? "No active academic period" }}
                            </p>
                        </div>

                        <StatusBadge :label="currentPeriod?.state ? formatStatus(currentPeriod.state) : 'NOT ACTIVE'"
                            :variant="currentPeriod?.can_enroll ? 'success' : 'gray'" />
                    </div>

                    <div v-if="currentPeriod" class="grid gap-4 border-t border-gray-200 p-6 text-sm dark:border-gray-800 md:grid-cols-2">
                        <div>
                            <p class="font-medium text-gray-500 dark:text-gray-400">
                                Enrollment deadline
                            </p>

                            <p class="mt-1 text-gray-900 dark:text-white">
                                {{ formatDate(currentPeriod.enrollment_deadline) }}
                            </p>
                        </div>

                        <div>
                            <p class="font-medium text-gray-500 dark:text-gray-400">
                                Unenrollment deadline
                            </p>

                            <p class="mt-1 text-gray-900 dark:text-white">
                                {{ formatDate(currentPeriod.unenrollment_deadline) }}
                            </p>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Enrolled Subjects
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Subjects assigned to your current academic period.
                        </p>
                    </div>

                    <div class="p-6">
                        <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                            <template #cell-status="{ value }">
                                <StatusBadge :label="formatStatus(value)" :variant="statusVariant(value)" />
                            </template>

                            <template #cell-group="{ row }">
                                <div class="space-y-1">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ row.group || "Pending" }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ row.modality || "TBD" }} / {{ row.shift || "TBD" }}
                                    </p>
                                </div>
                            </template>

                            <template #cell-schedule_summary="{ value }">
                                <span class="block max-w-md whitespace-normal text-sm text-gray-700 dark:text-gray-300">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-final_grade="{ row }">
                                <div class="space-y-1">
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ row.final_grade }}
                                    </p>
                                    <StatusBadge
                                        v-if="row.grade_state?.code"
                                        :label="row.grade_state_label"
                                        :variant="gradeVariant(row.grade_state.code)"
                                    />
                                </div>
                            </template>

                            <template #actions="{ row }">
                                <BaseButton size="sm" variant="secondary" @click="viewGrades(row)">
                                    <i class="fa-solid fa-eye mr-2" />
                                    Grades
                                </BaseButton>
                            </template>
                        </DataTable>

                        <EmptyState v-else title="No subjects enrolled"
                            description="When enrollment is open, use the enrollment workflow to select available subjects and groups."
                            icon="fa-solid fa-book" />
                    </div>
                </SectionCard>
            </div>
        </CrudContainer>

        <div v-if="isGradeModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            role="dialog" aria-label="Subject grades modal">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900 dark:text-gray-200">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ selectedSubject?.name }}
                    </h2>

                    <button class="text-gray-500 hover:text-red-500" @click="closeGradeModal" aria-label="Close">
                        <i class="fa-solid fa-xmark" />
                    </button>
                </div>

                <div v-if="gradesLoading" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Loading grades...
                </div>

                <div v-else-if="gradesError" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                    {{ gradesError }}
                </div>

                <dl v-else class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">First Exam</dt>
                        <dd>{{ selectedGrade?.partial_1 ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Second Exam</dt>
                        <dd>{{ selectedGrade?.partial_2 ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Third Exam</dt>
                        <dd>{{ selectedGrade?.partial_3 ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Activities</dt>
                        <dd>{{ selectedGrade?.activities ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Attendance</dt>
                        <dd>{{ selectedGrade?.attendance ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Final Grade</dt>
                        <dd>{{ selectedGrade?.final_grade ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Status</dt>
                        <dd>{{ selectedGrade?.state?.label ?? "Pending" }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div v-if="showSummary" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            role="dialog" aria-label="Grades summary modal">
            <div class="w-full max-w-5xl rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900 dark:text-gray-200">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Grade Summary
                    </h2>

                    <button class="text-gray-500 hover:text-red-500" @click="showSummary = false" aria-label="Close">
                        <i class="fa-solid fa-xmark" />
                    </button>
                </div>

                <div v-if="summaryLoading" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Loading grade summary...
                </div>

                <div v-else-if="summaryError" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                    {{ summaryError }}
                </div>

                <DataTable v-else :columns="[
                    { key: 'subject_name', label: 'Subject' },
                    { key: 'group', label: 'Group' },
                    { key: 'partial_1', label: 'First Exam' },
                    { key: 'partial_2', label: 'Second Exam' },
                    { key: 'partial_3', label: 'Third Exam' },
                    { key: 'activities', label: 'Activities' },
                    { key: 'attendance', label: 'Attendance' },
                    { key: 'final_grade', label: 'Final' },
                    { key: 'state', label: 'Status' },
                ]" :rows="allGrades.map((grade) => ({
                    id: grade.subject.id,
                    subject_name: grade.subject.name,
                    group: grade.group ?? '-',
                    partial_1: grade.partial_1 ?? '-',
                    partial_2: grade.partial_2 ?? '-',
                    partial_3: grade.partial_3 ?? '-',
                    activities: grade.activities ?? '-',
                    attendance: grade.attendance ?? '-',
                    final_grade: grade.final_grade ?? '-',
                    state: grade.state?.label ?? 'Pending',
                }))" />
            </div>
        </div>
    </CrudPageLayout>
</template>
