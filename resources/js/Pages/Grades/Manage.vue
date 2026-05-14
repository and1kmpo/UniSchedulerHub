<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                <span class="uppercase">Grades</span> for {{ subject.name }}
            </h1>
        </template>
        <div class="max-w-6xl mx-auto py-8">
            <form @submit.prevent="submitGrades">
                <table class="w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border">Student</th>
                            <th class="p-2 border">1st Exam <span title="First partial evaluation (25%)">
                                    <i class="fas fa-question-circle text-xs text-gray-400 ml-1"></i>
                                </span></th>
                            <th class="p-2 border">2nd Exam <span title="Second partial evaluation (25%)">
                                    <i class="fas fa-question-circle text-xs text-gray-400 ml-1"></i>
                                </span></th>
                            <th class="p-2 border">3rd Exam <span title="Third partial evaluation (30%)">
                                    <i class="fas fa-question-circle text-xs text-gray-400 ml-1"></i>
                                </span></th>
                            <th class="p-2 border">Activities <span title="Activities (20%)">
                                    <i class="fas fa-question-circle text-xs text-gray-400 ml-1"></i>
                                </span></th>
                            <th class="p-2 border">Attendance % <span
                                    title="Class attendance percentage. This does not count toward the final grade, but the student must have a minimum of 80% class attendance.">
                                    <i class="fas fa-question-circle text-xs text-gray-400 ml-1"></i>
                                </span></th>
                            <th class="p-2 border">Final grade</th>
                            <th class="p-2 border">State</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="enrollment in enrollments" :key="enrollment.id" :class="{
                            'bg-yellow-100': isModified(enrollment.id),
                            'odd:bg-white even:bg-gray-50': true
                        }">
                            <td class="p-2 border">{{ enrollment.student.name }}</td>
                            <td class="p-2 border">
                                <input v-model="grades[enrollment.id].partial_1" type="number" step="0.1" min="0"
                                    max="5" class="border rounded px-2 py-1 w-20 focus:ring focus:outline-none" />
                            </td>
                            <td class="p-2 border">
                                <input v-model="grades[enrollment.id].partial_2" type="number" step="0.1" min="0"
                                    max="5" class="border rounded px-2 py-1 w-20 focus:ring focus:outline-none" />
                            </td>
                            <td class="p-2 border">
                                <input v-model="grades[enrollment.id].partial_3" type="number" step="0.1" min="0"
                                    max="5" class="border rounded px-2 py-1 w-20 focus:ring focus:outline-none" />
                            </td>
                            <td class="p-2 border">
                                <input v-model="grades[enrollment.id].activities" type="number" step="0.1" min="0"
                                    max="5" class="border rounded px-2 py-1 w-20 focus:ring focus:outline-none" />
                            </td>
                            <td class="p-2 border">
                                <input v-model="grades[enrollment.id].attendance" type="number" step="1" min="0"
                                    max="100" class="border rounded px-2 py-1 w-20 focus:ring focus:outline-none" />
                            </td>
                            <!-- Final grade -->
                            <td class="p-2 border">
                                <input
                                    :value="isNumeric(grades[enrollment.id].final_grade) ? Number(grades[enrollment.id].final_grade).toFixed(2) : '—'"
                                    type="text" readonly
                                    class="inline-block w-16 text-center px-2 py-1 rounded text-sm font-medium" :class="{
                                        'bg-green-200 text-green-800': isNumeric(grades[enrollment.id].final_grade) && grades[enrollment.id].final_grade >= 3.0,
                                        'bg-yellow-200 text-yellow-800': isNumeric(grades[enrollment.id].final_grade) && grades[enrollment.id].final_grade >= 2.5 && grades[enrollment.id].final_grade < 3.0,
                                        'bg-red-200 text-red-800': isNumeric(grades[enrollment.id].final_grade) && grades[enrollment.id].final_grade < 2.5,
                                        'bg-gray-200 text-gray-600': !isNumeric(grades[enrollment.id].final_grade)
                                    }" />
                            </td>

                            <!-- Grade state -->
                            <td class="p-2 border text-center">
                                <div class="relative group inline-block">
                                    <span class="inline-flex items-center gap-2 cursor-pointer" :class="{
                                        'text-green-600': grades[enrollment.id].state?.code === 'passed',
                                        'text-red-600': grades[enrollment.id].state?.code === 'failed',
                                        'text-yellow-600': grades[enrollment.id].state?.code === 'failed_attendance',
                                        'text-gray-500': !grades[enrollment.id].state
                                    }">
                                        <i v-if="grades[enrollment.id].state?.code === 'passed'"
                                            class="fas fa-check-circle"></i>
                                        <i v-else-if="grades[enrollment.id].state?.code === 'failed'"
                                            class="fas fa-times-circle"></i>
                                        <i v-else-if="grades[enrollment.id].state?.code === 'failed_attendance'"
                                            class="fas fa-exclamation-triangle"></i>
                                        <i v-else class="fas fa-clock"></i>

                                        {{
                                            {
                                                passed: 'Passed',
                                                failed: 'Failed',
                                                failed_attendance: 'Failed - Attendance'
                                            }[grades[enrollment.id].state?.code] || 'Pending'
                                        }}
                                    </span>

                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 px-2 py-1 text-sm text-white bg-gray-800 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-pre z-20">
                                        {{ grades[enrollment.id].state?.label || 'Waiting for complete grades' }}
                                    </div>
                                </div>
                            </td>

                        </tr>
                    </tbody>
                </table>

                <button :disabled="!hasChanges || isSubmitting" type="submit"
                    class="mt-6 px-5 py-2 rounded text-white flex items-center gap-2 transition-all duration-200"
                    :class="[
                        (hasChanges && !isSubmitting) ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed',
                        isSubmitting ? 'opacity-75' : ''
                    ]">
                    <i class="fas fa-save"></i>
                    <span>{{ isSubmitting ? 'Saving...' : 'Save Grades' }}</span>
                </button>

            </form>
        </div>
    </AppLayout>

</template>

<script setup>
import { reactive, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { ref } from 'vue';
import { useAlert } from "@/Components/Composables/useAlert";

const {
    success,
    error,
    info,
    confirm,
    toastSuccess,
    toastError,
    custom,
    loading,
    close,
} = useAlert();

const props = defineProps({
    group: Object,
    subject: Object,
    academicPeriod: Object,
    canEdit: Boolean,
    enrollments: {
        type: Array,
        default: () => [],
    },
});

const grades = reactive({});
const originalGrades = reactive({});
const isSubmitting = ref(false);
const isNumeric = (val) => !isNaN(parseFloat(val)) && isFinite(val);


// Inicializar notas actuales y originales
props.enrollments.forEach(enrollment => {
    const original = {
        partial_1: enrollment.grade?.partial_1 ?? '',
        partial_2: enrollment.grade?.partial_2 ?? '',
        partial_3: enrollment.grade?.partial_3 ?? '',
        activities: enrollment.grade?.activities ?? '',
        attendance: enrollment.grade?.attendance ?? '',
        final_grade: enrollment.grade?.final_grade ?? '',
        state: enrollment.grade?.state ?? null
    };

    grades[enrollment.id] = { ...original };
    originalGrades[enrollment.id] = { ...original };
});

const getChangedGrades = () => {
    const changed = {};
    for (const [enrollmentId, grade] of Object.entries(grades)) {
        const original = originalGrades[enrollmentId];
        const hasChanges = Object.keys(grade).some(key => grade[key] !== original[key]);
        if (hasChanges) {
            changed[enrollmentId] = grade;
        }
    }
    return changed;
};

const submitGrades = async () => {
    const changedGrades = getChangedGrades();

    if (Object.keys(changedGrades).length === 0) {
        info(
            "There are no modified grades to save.",
            "No changes detected"
        );
        return;
    }

    const confirmed = await confirm(
        "Grades will be updated for the selected students.",
        "Save changes?"
    );

    if (!confirmed) return;

    isSubmitting.value = true;

    const formattedGrades = {};
    for (const [enrollmentId, grade] of Object.entries(changedGrades)) {
        formattedGrades[enrollmentId] = {
            first_exam: grade.partial_1 ?? null,
            second_exam: grade.partial_2 ?? null,
            third_exam: grade.partial_3 ?? null,
            activities: grade.activities ?? null,
            attendance: grade.attendance ?? null,
        };
    }

    loading(
        "Saving grades...",
        "Please wait while the grades are being updated."
    );


    axios.post(route('groups.grades.store', props.group.id), {
        grades: formattedGrades,
    }).then(response => {
        const updated = response.data.updated_grades;

        Object.entries(updated).forEach(([enrollmentId, updatedGrade]) => {
            grades[enrollmentId] = {
                partial_1: updatedGrade.partial_1 ?? '',
                partial_2: updatedGrade.partial_2 ?? '',
                partial_3: updatedGrade.partial_3 ?? '',
                activities: updatedGrade.activities ?? '',
                attendance: updatedGrade.attendance ?? '',
                final_grade: updatedGrade.final_grade ?? '',
                state: updatedGrade.state ?? '',
            };

            originalGrades[enrollmentId] = { ...grades[enrollmentId] };

        });
    }).catch(err => {
        console.error('❌ Error al guardar calificaciones:', err.response?.data);
        error(
            "An unexpected error occurred while saving the grades. Please try again.",
            "Error saving grades"
        );
    }).finally(() => {

        isSubmitting.value = false;
        close();

        setTimeout(() => {
            toastSuccess("Grades saved successfully");
        }, 150);
    });
};

function isGradeComplete(grade) {
    return ['partial_1', 'partial_2', 'partial_3', 'activities', 'attendance'].every(key => {
        const val = grade[key];
        return val !== '' && val !== null && !isNaN(val);
    });
}

const hasChanges = computed(() => {
    return Object.keys(getChangedGrades()).length > 0;
});

const isModified = (studentId) => {
    const grade = grades[studentId];
    const original = originalGrades[studentId];
    return Object.keys(grade).some(key => grade[key] !== original[key]);
};

</script>
