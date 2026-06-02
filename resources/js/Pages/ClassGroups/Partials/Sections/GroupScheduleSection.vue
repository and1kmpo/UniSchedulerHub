<script setup>
import { Link } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { route } from "ziggy-js";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

import ScheduleTimeline from "../ScheduleTimeline.vue";
import StudentLoadChart from "../Schedule/StudentLoadChart.vue";
import SmartSchedulerBoard from "../Scheduler/SmartSchedulerBoard.vue";

const props = defineProps({
    classGroup: {
        type: Object,
        required: true,
    },
});

const canManageSchedules = props.classGroup.can_manage_schedules !== false;

const localSchedules = ref([
    ...(props.classGroup.schedules || []),
]);

watch(
    () => props.classGroup.schedules,
    (schedules) => {
        localSchedules.value = [
            ...(schedules || []),
        ];
    },
    { deep: true }
);

const replaceSchedule = (updatedSchedule) => {
    localSchedules.value = localSchedules.value.map((schedule) => {
        if (schedule.id !== updatedSchedule.id) {
            return schedule;
        }

        return updatedSchedule;
    });
};
</script>

<template>
    <section class="space-y-6">
        <SectionCard class="p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Schedule Planning
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage official blocks, conflicts and weekly distribution.
                    </p>
                </div>

                <Link v-if="canManageSchedules" :href="route('class-schedules.create', classGroup.id)">
                    <BaseButton variant="primary">
                        <i class="fa-solid fa-calendar-plus mr-2" />
                        Add Schedule
                    </BaseButton>
                </Link>

                <div v-else class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-800">
                    Schedule changes are locked for this group or academic period.
                </div>
            </div>
        </SectionCard>

        <SmartSchedulerBoard :class-group-id="classGroup.id" :schedules="localSchedules"
            :can-edit="canManageSchedules" @schedule-updated="replaceSchedule" />

        <ScheduleTimeline :schedules="localSchedules" />

        <StudentLoadChart :students="classGroup.students" />
    </section>
</template>
