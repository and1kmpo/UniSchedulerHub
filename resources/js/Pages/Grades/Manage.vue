<script setup>
import { computed, reactive, ref } from "vue";
import axios from "axios";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { confirm, error, info, loading, close, toastSuccess } = useAlert();

const props = defineProps({
    group: {
        type: Object,
        required: true,
    },

    subject: {
        type: Object,
        required: true,
    },

    academicPeriod: {
        type: Object,
        default: null,
    },

    canEdit: {
        type: Boolean,
        default: false,
    },

    enrollments: {
        type: Array,
        default: () => [],
    },
});

const grades = reactive({});
const originalGrades = reactive({});
const isSubmitting = ref(false);

props.enrollments.forEach((enrollment) => {
    const original = {
        partial_1: enrollment.grade?.partial_1 ?? "",
        partial_2: enrollment.grade?.partial_2 ?? "",
        partial_3: enrollment.grade?.partial_3 ?? "",
        activities: enrollment.grade?.activities ?? "",
        attendance: enrollment.grade?.attendance ?? "",
        final_grade: enrollment.grade?.final_grade ?? "",
        state: enrollment.grade?.state ?? null,
    };

    grades[enrollment.id] = { ...original };
    originalGrades[enrollment.id] = { ...original };
});

const activeEnrollments = computed(() => props.enrollments);

const hasChanges = computed(() => Object.keys(getChangedGrades()).length > 0);

const completedGradesCount = computed(() =>
    Object.values(grades).filter((grade) => grade.state?.code).length
);

function isNumeric(value) {
    return value !== "" && value !== null && !Number.isNaN(Number(value));
}

function formatGrade(value) {
    return isNumeric(value) ? Number(value).toFixed(2) : "-";
}

function getChangedGrades() {
    const changed = {};

    Object.entries(grades).forEach(([enrollmentId, grade]) => {
        const original = originalGrades[enrollmentId];
        const dirty = ["partial_1", "partial_2", "partial_3", "activities", "attendance"]
            .some((key) => String(grade[key] ?? "") !== String(original[key] ?? ""));

        if (dirty) {
            changed[enrollmentId] = grade;
        }
    });

    return changed;
}

function isModified(enrollmentId) {
    return Boolean(getChangedGrades()[enrollmentId]);
}

function stateVariant(state) {
    return {
        passed: "success",
        failed: "danger",
        failed_attendance: "warning",
    }[state?.code] || "gray";
}

function stateLabel(state) {
    return state?.label || "Pending";
}

function finalGradeClass(value) {
    if (!isNumeric(value)) {
        return "bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300";
    }

    const number = Number(value);

    if (number >= 3) {
        return "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300";
    }

    if (number >= 2.5) {
        return "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300";
    }

    return "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300";
}

function normalizeGradeValue(value) {
    return value === "" ? null : value;
}

async function submitGrades() {
    const changedGrades = getChangedGrades();

    if (!Object.keys(changedGrades).length) {
        info("There are no modified grades to save.", "No changes detected");
        return;
    }

    const confirmed = await confirm(
        "Grades will be updated for the selected students.",
        "Save changes?"
    );

    if (!confirmed) {
        return;
    }

    const payload = {};

    Object.entries(changedGrades).forEach(([enrollmentId, grade]) => {
        payload[enrollmentId] = {
            first_exam: normalizeGradeValue(grade.partial_1),
            second_exam: normalizeGradeValue(grade.partial_2),
            third_exam: normalizeGradeValue(grade.partial_3),
            activities: normalizeGradeValue(grade.activities),
            attendance: normalizeGradeValue(grade.attendance),
        };
    });

    isSubmitting.value = true;
    loading("Saving grades...", "Please wait while the grades are updated.");

    try {
        const response = await axios.post(route("groups.grades.store", props.group.id), {
            grades: payload,
        });

        Object.entries(response.data.updated_grades).forEach(([enrollmentId, updatedGrade]) => {
            grades[enrollmentId] = {
                partial_1: updatedGrade.partial_1 ?? "",
                partial_2: updatedGrade.partial_2 ?? "",
                partial_3: updatedGrade.partial_3 ?? "",
                activities: updatedGrade.activities ?? "",
                attendance: updatedGrade.attendance ?? "",
                final_grade: updatedGrade.final_grade ?? "",
                state: updatedGrade.state ?? null,
            };

            originalGrades[enrollmentId] = { ...grades[enrollmentId] };
        });

        close();
        toastSuccess("Grades saved successfully");
    } catch (exception) {
        close();
        error(
            exception.response?.data?.errors?.grades?.[0] ||
            exception.response?.data?.message ||
            "An unexpected error occurred while saving grades.",
            "Error saving grades"
        );
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <CrudPageLayout :title="`Grades - ${subject.name}`" :subtitle="`${group.code} · ${academicPeriod?.name ?? 'No period'}`">
        <template #actions>
            <BaseButton type="button" variant="primary" :disabled="!canEdit || !hasChanges || isSubmitting"
                @click="submitGrades">
                <i v-if="isSubmitting" class="fa-solid fa-spinner fa-spin mr-2" />
                <i v-else class="fa-solid fa-floppy-disk mr-2" />
                Save Grades
            </BaseButton>
        </template>

        <CrudContainer>
            <div class="space-y-6">
                <SectionCard>
                    <div class="grid gap-4 p-6 md:grid-cols-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Subject
                            </p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                {{ subject.name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Group
                            </p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                {{ group.code }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Students
                            </p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                {{ activeEnrollments.length }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Completed
                            </p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                {{ completedGradesCount }}
                            </p>
                        </div>
                    </div>

                    <div v-if="!canEdit" class="border-t border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800/60 dark:bg-amber-900/20 dark:text-amber-200">
                        Grade editing is locked for this group or academic period. You can review current grades only.
                    </div>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Student Grades
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Partial grades are weighted 25%, 25%, 30% and 20% for activities.
                        </p>
                    </div>

                    <div v-if="activeEnrollments.length" class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/80">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Student
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        P1
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        P2
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        P3
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Activities
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Attendance
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Final
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        State
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
                                <tr v-for="enrollment in activeEnrollments" :key="enrollment.id"
                                    class="transition hover:bg-gray-50 dark:hover:bg-gray-800/70"
                                    :class="{ 'bg-amber-50 dark:bg-amber-900/10': isModified(enrollment.id) }">
                                    <td class="px-4 py-4 align-middle">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ enrollment.student.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ enrollment.student.document }}
                                        </p>
                                    </td>

                                    <td v-for="field in ['partial_1', 'partial_2', 'partial_3', 'activities']"
                                        :key="field" class="px-4 py-4 align-middle">
                                        <input v-model="grades[enrollment.id][field]" type="number" min="0" max="5"
                                            step="0.1" :disabled="!canEdit || !enrollment.can_edit"
                                            class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <input v-model="grades[enrollment.id].attendance" type="number" min="0" max="100"
                                            step="1" :disabled="!canEdit || !enrollment.can_edit"
                                            class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <span class="inline-flex min-w-16 justify-center rounded-lg px-3 py-2 text-sm font-semibold"
                                            :class="finalGradeClass(grades[enrollment.id].final_grade)">
                                            {{ formatGrade(grades[enrollment.id].final_grade) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 align-middle">
                                        <StatusBadge :label="stateLabel(grades[enrollment.id].state)"
                                            :variant="stateVariant(grades[enrollment.id].state)" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="p-6">
                        <EmptyState title="No active enrollments"
                            description="This group has no active enrolled students to grade."
                            icon="fa-solid fa-clipboard-list" />
                    </div>
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
