<!-- resources/js/Pages/ClassSchedules/Calendar.vue -->
<script setup>
import Layout from "@/Layouts/AppLayout.vue";
import ScheduleCalendar from "./ScheduleCalendar.vue";

defineProps({
    classGroup: Object,
});
</script>

<template>
    <Layout :title="`Schedules for ${classGroup.code}`">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-2xl font-semibold mb-4">
                Horario: {{ classGroup.name }} ({{ classGroup.code }})
            </h2>
            <ScheduleCalendar :classGroupId="classGroup.id" />
        </div>
    </Layout>
</template>
