<!-- resources/js/Pages/ClassSchedules/Calendar.vue -->
<script setup>
import Layout from "@/Layouts/AppLayout.vue";
import ScheduleCalendar from "./ScheduleCalendar.vue";

const props = defineProps({
    classGroup: Object,
    schedules: Array,
    classrooms: Array,
    editable: {
        type: Boolean,
        default: false
    }
})
</script>

<template>
    <Layout :title="`Schedules for ${classGroup.code}`">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-2xl font-semibold mb-4">
                Schedule: {{ classGroup.name }} ({{ classGroup.code }})
            </h2>
            <ScheduleCalendar :classGroup="classGroup" :schedules="schedules" :classrooms="classrooms"
                :editable="editable" />
        </div>
    </Layout>
</template>
