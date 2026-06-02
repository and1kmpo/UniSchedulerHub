<script setup>
import EnrollmentAssistantPanel from "../Enrollment/EnrollmentAssistantPanel.vue";
import EnrollmentConflictList from "../Enrollment/EnrollmentConflictList.vue";
import EnrollmentRecommendationCard from "../Enrollment/EnrollmentRecommendationCard.vue";
import EnrollmentStatusBanner from "../Enrollment/EnrollmentStatusBanner.vue";
import EnrollmentStudentPicker from "../Enrollment/EnrollmentStudentPicker.vue";
import EnrollmentValidatorPanel from "../Enrollment/EnrollmentValidatorPanel.vue";
import EnrollmentWarnings from "../Enrollment/EnrollmentWarnings.vue";
import StudentAcademicLoadCard from "../Enrollment/StudentAcademicLoadCard.vue";

defineProps({
    classGroup: {
        type: Object,
        required: true,
    },

    students: {
        type: Array,
        default: () => [],
    },

    enrolledIds: {
        type: Array,
        default: () => [],
    },

    selectedStudent: {
        type: Object,
        default: null,
    },

    validationResult: {
        type: Object,
        required: true,
    },

    validationLoading: {
        type: Boolean,
        default: false,
    },
});

defineEmits([
    "select-student",
    "enroll",
]);
</script>

<template>
    <section class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <EnrollmentStudentPicker :students="students" :enrolled-ids="enrolledIds"
                @select="$emit('select-student', $event)" />

            <EnrollmentStatusBanner :result="validationResult" :loading="validationLoading" />

            <EnrollmentValidatorPanel :result="validationResult" :loading="validationLoading" />

            <EnrollmentConflictList :conflicts="validationResult.conflicts" />

            <EnrollmentWarnings :warnings="validationResult.warnings" />

            <EnrollmentRecommendationCard :recommendations="validationResult.recommendations" />
        </div>

        <div class="space-y-6">
            <EnrollmentAssistantPanel :class-group="classGroup" :selected-student="selectedStudent"
                :validation-result="validationResult" :loading="validationLoading" @enroll="$emit('enroll')" />

            <StudentAcademicLoadCard :load="validationResult.load" />
        </div>
    </section>
</template>
