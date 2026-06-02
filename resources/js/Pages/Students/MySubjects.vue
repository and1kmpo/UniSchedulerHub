<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";
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

const rows = computed(() =>
    props.subjects.map((subject) => ({
        ...subject,
        schedule_summary: formatSchedules(subject.schedules),
        final_grade: subject.grade?.final_grade ?? "Pending",
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

function formatStatus(status) {
    return status ? status.replaceAll("_", " ").toUpperCase() : "PENDING";
}

function formatDay(day) {
    return day ? day.charAt(0).toUpperCase() + day.slice(1) : "";
}

function formatSchedules(schedules = []) {
    if (!schedules.length) {
        return "Pending";
    }

    return schedules
        .map((schedule) => {
            const room = schedule.classroom ? ` - ${schedule.classroom}` : "";

            return `${formatDay(schedule.day)} ${schedule.start_time}-${schedule.end_time}${room}`;
        })
        .join("; ");
}

const viewGrades = async (subject) => {
    selectedSubject.value = subject;

    try {
        const response = await axios.get(route("student.subject.grades.json", subject.id));
        selectedGrade.value = response.data.grade;
    } catch (error) {
        selectedGrade.value = null;
    }

    isGradeModalOpen.value = true;
};

const closeGradeModal = () => {
    isGradeModalOpen.value = false;
    selectedGrade.value = null;
    selectedSubject.value = null;
};

const loadAllGrades = async () => {
    const response = await axios.get(route("student.grades.summary"));
    allGrades.value = response.data.grades;
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
                <Link v-if="currentPeriod?.can_enroll" :href="route('student.subject-enrollment.index')">
                    <BaseButton variant="primary">
                        <i class="fa-solid fa-user-plus mr-2" />
                        Enroll Subjects
                    </BaseButton>
                </Link>

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
                                {{ currentPeriod.enrollment_deadline ?? "Not defined" }}
                            </p>
                        </div>

                        <div>
                            <p class="font-medium text-gray-500 dark:text-gray-400">
                                Unenrollment deadline
                            </p>

                            <p class="mt-1 text-gray-900 dark:text-white">
                                {{ currentPeriod.unenrollment_deadline ?? "Not defined" }}
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

                            <template #cell-schedule_summary="{ value }">
                                <span class="block max-w-md whitespace-normal text-sm text-gray-700 dark:text-gray-300">
                                    {{ value }}
                                </span>
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

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Partial 1</dt>
                        <dd>{{ selectedGrade?.partial_1 ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Partial 2</dt>
                        <dd>{{ selectedGrade?.partial_2 ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Partial 3</dt>
                        <dd>{{ selectedGrade?.partial_3 ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Activities</dt>
                        <dd>{{ selectedGrade?.activities ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Final Grade</dt>
                        <dd>{{ selectedGrade?.final_grade ?? "Pending" }}</dd>
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

                <DataTable :columns="[
                    { key: 'subject_name', label: 'Subject' },
                    { key: 'partial_1', label: 'P1' },
                    { key: 'partial_2', label: 'P2' },
                    { key: 'partial_3', label: 'P3' },
                    { key: 'activities', label: 'Activities' },
                    { key: 'final_grade', label: 'Final' },
                    { key: 'state', label: 'Status' },
                ]" :rows="allGrades.map((grade) => ({
                    id: grade.subject.id,
                    subject_name: grade.subject.name,
                    partial_1: grade.partial_1 ?? '-',
                    partial_2: grade.partial_2 ?? '-',
                    partial_3: grade.partial_3 ?? '-',
                    activities: grade.activities ?? '-',
                    final_grade: grade.final_grade ?? '-',
                    state: grade.state?.label ?? 'Pending',
                }))" />
            </div>
        </div>
    </CrudPageLayout>
</template>
