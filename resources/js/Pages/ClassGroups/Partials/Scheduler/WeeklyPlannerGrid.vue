<script setup>
import { computed } from "vue";

import DraggableClassCard
    from "./DraggableClassCard.vue";

import ScheduleDropZone
    from "./ScheduleDropZone.vue";

import { hasOverlap }
    from "./schedulerUtils";

const props = defineProps({
    schedules: {
        type: Array,
        default: () => [],
    },

    editable: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "move",
    "resize",
]);

const days = [
    "monday",
    "tuesday",
    "wednesday",
    "thursday",
    "friday",
    "saturday",
];

const hours = [
    "06:00",
    "07:00",
    "08:00",
    "09:00",
    "10:00",
    "11:00",
    "12:00",
    "13:00",
    "14:00",
    "15:00",
    "16:00",
    "17:00",
    "18:00",
    "19:00",
    "20:00",
    "21:00",
];

const groupedSchedules = computed(() => {

    return props.schedules.map(schedule => {

        const conflict = props.schedules.some(
            other =>
                other !== schedule &&
                hasOverlap(schedule, other)
        );

        return {
            ...schedule,
            conflict,
        };
    });
});

function scheduleSlotHour(schedule) {
    const [hours = "00"] = String(schedule.start_time).split(":");

    return `${hours.padStart(2, "0")}:00`;
}

const handleDrop = (payload) => {
    if (!props.editable) {
        return;
    }

    emit("move", payload);
};
</script>

<template>

    <div class="overflow-x-auto">

        <div class="
                grid
                min-w-[1200px]
                grid-cols-7
                border
                border-gray-200
                dark:border-gray-800
            ">

            <!-- HEADER -->

            <div class="border-b border-r p-3 bg-gray-50 dark:bg-gray-800" />

            <div v-for="day in days" :key="day" class="
                    border-b
                    border-r
                    p-3
                    text-center
                    text-sm
                    font-semibold
                    bg-gray-50
                    dark:bg-gray-800
                    dark:text-white
                ">
                {{ day.charAt(0).toUpperCase() + day.slice(1) }}
            </div>

            <!-- BODY -->

            <template v-for="hour in hours" :key="hour">

                <!-- HOUR -->

                <div class="
                        border-b
                        border-r
                        p-3
                        text-xs
                        bg-gray-50
                        dark:bg-gray-800
                        dark:text-gray-400
                    ">
                    {{ hour }}
                </div>

                <!-- CELLS -->

                <div v-for="day in days" :key="`${day}-${hour}`" class="
                        relative
                        h-20
                        border-b
                        border-r
                        dark:border-gray-800
                    ">

                    <!-- DROP ZONE -->

                    <ScheduleDropZone :day="day" :hour="hour" :disabled="!editable" @drop="handleDrop" />

                    <!-- SCHEDULES -->

                    <template v-for="schedule in groupedSchedules" :key="schedule.id">

                        <DraggableClassCard v-if="
                            schedule.day === day &&
                            scheduleSlotHour(schedule) === hour
                        " :schedule="schedule" :conflict="schedule.conflict" :editable="editable"
                            @resize="editable ? $emit('resize', $event) : null" />

                    </template>

                </div>

            </template>

        </div>

    </div>

</template>
