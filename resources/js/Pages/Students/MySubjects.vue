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
import { formatDate, formatTime } from "@/Components/Composables/useDateTimeFormatter";
import { useTranslations } from "@/Components/Composables/useTranslations";

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

const { t } = useTranslations();

const columns = computed(() => [
    { key: "name", label: t("common.subject") },
    { key: "credits", label: t("common.credits") },
    { key: "group", label: t("common.group") },
    { key: "professor_name", label: t("common.professor") },
    { key: "schedule_summary", label: t("common.schedule") },
    { key: "status", label: t("common.status") },
    { key: "final_grade", label: t("student_portal.final_grade") },
]);

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
        final_grade: subject.grade?.final_grade ?? t("common.pending"),
        grade_state_label: subject.grade_state?.label ?? t("common.pending"),
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
    return status ? status.replaceAll("_", " ").toUpperCase() : t("common.pending");
}

function formatDay(day) {
    return day ? day.charAt(0).toUpperCase() + day.slice(1) : "";
}

function formatSchedules(schedules = []) {
    if (!schedules.length) {
        return t("common.pending");
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
        gradesError.value = t("student_portal.grades_load_error");
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
        summaryError.value = t("student_portal.summary_load_error");
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
    <CrudPageLayout
        :title="t('student_portal.my_subjects_title')"
        :subtitle="t('student_portal.my_subjects_subtitle')"
    >
        <template #actions>
            <div class="flex flex-col gap-2 sm:flex-row">
                <BaseButton variant="secondary" @click="handleOpenSummary">
                    <i class="fa-solid fa-list mr-2" />
                    {{ t("student_portal.grade_summary") }}
                </BaseButton>
            </div>
        </template>

        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <StatCard :title="t('student_portal.active_subjects')" :value="summary.active_subjects" icon="fa-solid fa-book-open" />
                    <StatCard :title="t('student_portal.current_credits')" :value="summary.current_credits" icon="fa-solid fa-layer-group" />
                    <StatCard :title="t('student_portal.with_grades')" :value="summary.graded_subjects" icon="fa-solid fa-chart-line" />
                </section>

                <SectionCard>
                    <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-ink dark:text-white">
                                {{ t("student_portal.current_period") }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                                {{ currentPeriod?.name ?? t("student_portal.no_active_period") }}
                            </p>
                        </div>

                        <StatusBadge :label="currentPeriod?.state ? formatStatus(currentPeriod.state) : t('common.not_active')"
                            :variant="currentPeriod?.can_enroll ? 'success' : 'gray'" />
                    </div>

                    <div v-if="currentPeriod" class="grid gap-4 border-t border-border-light p-6 text-sm dark:border-border-dark md:grid-cols-2">
                        <div>
                            <p class="font-medium text-slate-500 dark:text-zinc-400">
                                {{ t("student_portal.enrollment_deadline") }}
                            </p>

                            <p class="mt-1 text-ink dark:text-white">
                            {{ formatDate(currentPeriod.enrollment_deadline, t("common.not_defined")) }}
                            </p>
                        </div>

                        <div>
                            <p class="font-medium text-slate-500 dark:text-zinc-400">
                                {{ t("student_portal.unenrollment_deadline") }}
                            </p>

                            <p class="mt-1 text-ink dark:text-white">
                                {{ formatDate(currentPeriod.unenrollment_deadline, t("common.not_defined")) }}
                            </p>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-border-light p-6 dark:border-border-dark">
                        <h2 class="text-lg font-semibold text-ink dark:text-white">
                            {{ t("student_portal.enrolled_subjects") }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                            {{ t("student_portal.enrolled_subjects_description") }}
                        </p>
                    </div>

                    <div class="p-6">
                        <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                            <template #cell-status="{ value }">
                                <StatusBadge :label="formatStatus(value)" :variant="statusVariant(value)" />
                            </template>

                            <template #cell-group="{ row }">
                                <div class="space-y-1">
                                    <p class="font-medium text-ink dark:text-white">
                                        {{ row.group || t("common.pending") }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-zinc-400">
                                        {{ row.modality || t("common.tbd") }} / {{ row.shift || t("common.tbd") }}
                                    </p>
                                </div>
                            </template>

                            <template #cell-schedule_summary="{ value }">
                                <span class="block max-w-md whitespace-normal text-sm text-slate-700 dark:text-zinc-300">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-final_grade="{ row }">
                                <div class="space-y-1">
                                    <p class="font-medium text-ink dark:text-white">
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
                                    {{ t("common.grades") }}
                                </BaseButton>
                            </template>
                        </DataTable>

                        <EmptyState v-else :title="t('student_portal.no_subjects_title')"
                            :description="t('student_portal.no_subjects_description')"
                            icon="fa-solid fa-book" />
                    </div>
                </SectionCard>
            </div>
        </CrudContainer>

        <div v-if="isGradeModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            role="dialog" :aria-label="t('student_portal.subject_grades_modal')">
            <div class="w-full max-w-md rounded-lg border border-border-light bg-surface p-6 shadow-sm dark:border-border-dark dark:bg-surface-dark dark:text-zinc-200">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-ink dark:text-white">
                        {{ selectedSubject?.name }}
                    </h2>

                    <button class="text-slate-500 hover:text-danger" @click="closeGradeModal" :aria-label="t('common.close')">
                        <i class="fa-solid fa-xmark" />
                    </button>
                </div>

                <div v-if="gradesLoading" class="py-8 text-center text-sm text-slate-500 dark:text-zinc-400">
                    {{ t("student_portal.loading_grades") }}
                </div>

                <div v-else-if="gradesError" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                    {{ gradesError }}
                </div>

                <dl v-else class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-slate-500 dark:text-zinc-400">{{ t("student_portal.first_exam") }}</dt>
                        <dd>{{ selectedGrade?.partial_1 ?? t("common.pending") }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-slate-500 dark:text-zinc-400">{{ t("student_portal.second_exam") }}</dt>
                        <dd>{{ selectedGrade?.partial_2 ?? t("common.pending") }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-slate-500 dark:text-zinc-400">{{ t("student_portal.third_exam") }}</dt>
                        <dd>{{ selectedGrade?.partial_3 ?? t("common.pending") }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-slate-500 dark:text-zinc-400">{{ t("student_portal.activities") }}</dt>
                        <dd>{{ selectedGrade?.activities ?? t("common.pending") }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-slate-500 dark:text-zinc-400">{{ t("student_portal.attendance") }}</dt>
                        <dd>{{ selectedGrade?.attendance ?? t("common.pending") }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-slate-500 dark:text-zinc-400">{{ t("student_portal.final_grade") }}</dt>
                        <dd>{{ selectedGrade?.final_grade ?? t("common.pending") }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-slate-500 dark:text-zinc-400">{{ t("common.status") }}</dt>
                        <dd>{{ selectedGrade?.state?.label ?? t("common.pending") }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div v-if="showSummary" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            role="dialog" :aria-label="t('student_portal.grades_summary_modal')">
            <div class="w-full max-w-5xl rounded-lg border border-border-light bg-surface p-6 shadow-sm dark:border-border-dark dark:bg-surface-dark dark:text-zinc-200">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-ink dark:text-white">
                        {{ t("student_portal.grade_summary") }}
                    </h2>

                    <button class="text-slate-500 hover:text-danger" @click="showSummary = false" :aria-label="t('common.close')">
                        <i class="fa-solid fa-xmark" />
                    </button>
                </div>

                <div v-if="summaryLoading" class="py-8 text-center text-sm text-slate-500 dark:text-zinc-400">
                    {{ t("student_portal.loading_summary") }}
                </div>

                <div v-else-if="summaryError" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                    {{ summaryError }}
                </div>

                <DataTable v-else :columns="[
                    { key: 'subject_name', label: t('common.subject') },
                    { key: 'group', label: t('common.group') },
                    { key: 'partial_1', label: t('student_portal.first_exam') },
                    { key: 'partial_2', label: t('student_portal.second_exam') },
                    { key: 'partial_3', label: t('student_portal.third_exam') },
                    { key: 'activities', label: t('student_portal.activities') },
                    { key: 'attendance', label: t('student_portal.attendance') },
                    { key: 'final_grade', label: t('student_portal.final_grade') },
                    { key: 'state', label: t('common.status') },
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
                    state: grade.state?.label ?? t('common.pending'),
                }))" />
            </div>
        </div>
    </CrudPageLayout>
</template>


