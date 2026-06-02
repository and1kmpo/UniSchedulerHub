<script setup>
import GroupAnalytics from "../GroupAnalytics.vue";
import ProfessorCard from "../ProfessorCard.vue";
import AcademicPeriodCard from "../AcademicPeriodCard.vue";
import EnrollmentStats from "../Enrollment/EnrollmentStats.vue";
import EnrollmentCapacityCard from "../Enrollment/EnrollmentCapacityCard.vue";

defineProps({
    classGroup: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <section class="space-y-6">
        <GroupAnalytics :class-group="classGroup" />

        <EnrollmentStats :class-group="classGroup" />

        <div class="grid gap-6 lg:grid-cols-2">
            <ProfessorCard :professor="classGroup.professor" />

            <AcademicPeriodCard :period="classGroup.academicPeriod" />
        </div>

        <EnrollmentCapacityCard :class-group="classGroup" />
    </section>
</template>
