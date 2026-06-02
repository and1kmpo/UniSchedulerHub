<script setup>
import {
    computed,
    ref,
} from "vue";

import DraggableClassCard from "./DraggableClassCard.vue";

import ScheduleDropZone from "./ScheduleDropZone.vue";

import ScheduleConflictOverlay from "./ScheduleConflictOverlay.vue";

import { useDragScheduler } from "./useDragScheduler";

import { buildVisualLayout, } from "./SchedulerCollisionEngine";

const props = defineProps({
    day: String,

    hours: Array,

    schedules: {
        type: Array,
        default: () => [],
    },

    conflicts: {
        type: Array,
        default: () => [],
    },

    selectedSchedule: Object,

    draggingBlock: Object,

    ghostPosition: Object,

    hasConflict: Function,
});

const emit = defineEmits([
    "update",
    "resize",
    "select",
]);

const {
    dropSchedule,
    calculateTop,
} = useDragScheduler();

const columnRef = ref(null);

const hourHeight = 80;

const positionedSchedules = computed(() => {

    const visualSchedules =
        buildVisualLayout(
            props.schedules
        );

    return visualSchedules.map(
        (schedule) => {

            const startHour =
                Number(
                    schedule.start_time
                        .split(":")[0]
                );

            const endHour =
                Number(
                    schedule.end_time
                        .split(":")[0]
                );

            return {
                ...schedule,

                top:
                    ((startHour - 6) * hourHeight),

                height:
                    ((endHour - startHour)
                        * hourHeight),
            };
        }
    );
});

const onDrop = (event) => {

    event.preventDefault();

    if (!columnRef.value) {
        return;
    }

    const rect =
        columnRef.value
            .getBoundingClientRect();

    const y =
        event.clientY - rect.top;

    const hour =
        calculateTop(y);

    const updated =
        dropSchedule(
            props.day,
            hour
        );

    if (!updated) {
        return;
    }

    emit(
        "update",
        updated
    );
};

const handleResize = (
    schedule,
    event
) => {

    const movement =
        Math.round(
            event.movementY / hourHeight
        );

    const endHour =
        Number(
            schedule.end_time
                .split(":")[0]
        );

    const newEnd =
        Math.max(
            endHour + movement,
            Number(
                schedule.start_time
                    .split(":")[0]
            ) + 1
        );

    emit(
        "resize",
        {
            id: schedule.id,

            end_time:
                `${newEnd}:00`,
        }
    );
};

const isSelected = (schedule) => {

    return (
        props.selectedSchedule?.id
        === schedule.id
    );
};
</script>

<template>

    <div ref="columnRef" class="
            relative
            border-r
            bg-white
            dark:bg-gray-900
        " :style="{
            height:
                `${hours.length * 80}px`
        }" @dragover.prevent @drop="onDrop">

        <!-- GRID LINES -->

        <div v-for="hour in hours" :key="hour" class="
                border-b
                border-gray-100
                dark:border-gray-800
            " :style="{
                height: '80px'
            }" />

        <!-- GHOST PREVIEW -->

        <div v-if="
            ghostPosition
            &&
            ghostPosition.day === day
        " class="
                absolute
                left-1
                right-1
                z-10
                rounded-xl
                border-2
                border-dashed
                border-indigo-400
                bg-indigo-100/50
                dark:bg-indigo-900/30
            " :style="{
                top:
                    `${ghostPosition.top}px`,
                height: '80px',
            }" />

        <!-- CONFLICT OVERLAYS -->

        <ScheduleConflictOverlay v-for="schedule in positionedSchedules" :key="`overlay-${schedule.id}`" v-if="
            hasConflict(schedule.id)
        " :schedule="schedule" />

        <!-- BLOCKS -->

        <DraggableClassCard v-for="schedule in positionedSchedules" :key="schedule.id" :schedule="schedule" :conflict="hasConflict(schedule.id)
            " :selected="isSelected(schedule)
                " @click="
                    emit(
                        'select',
                        schedule
                    )
                    " />

        <!-- RESIZE HANDLES -->

        <div v-for="schedule in positionedSchedules" :key="`resize-${schedule.id}`" class="
                absolute
                left-1
                right-1
                z-40
                h-2
                cursor-row-resize
            " :style="{
                top:
                    `${schedule.top + schedule.height - 6}px`
            }" @mousedown.prevent="
                handleResize(
                    schedule,
                    $event
                )
                " />

        <!-- DROP ZONES -->

        <ScheduleDropZone v-for="hour in hours" :key="`drop-${hour}`" :day="day" :hour="hour" />

    </div>

</template>