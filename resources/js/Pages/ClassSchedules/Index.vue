<script setup>
import Layout from "@/Layouts/AppLayout.vue";
import ScheduleCalendar from "./ScheduleCalendar.vue";

defineProps({
    classGroup: Object,
    schedules: Array,
});
</script>

<template>
    <Layout title="Class Schedule">
        <template #header>
            <div>
                <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    <span class="uppercase">Schedule</span> for {{ classGroup.subject.name }}
                </h1>
                <span class="block text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Group {{ classGroup.code }}
                </span>
            </div>
        </template>

        <div class="max-w-6xl mx-auto py-8">
            <ScheduleCalendar :classGroup="classGroup" :schedules="classGroup.schedules" />
        </div>
    </Layout>
</template>
