<script setup>
import { computed, ref, watch, } from "vue";

import SchedulerColumn from "./SchedulerColumn.vue";
import SchedulerTimeline from "./SchedulerTimeline.vue";

import { useDragScheduler } from "./useDragScheduler";

import { autoResolveSchedules, } from "./schedulerAutoResolve";

const props = defineProps({
    schedules: {
        type: Array,
        default: () => [],
    },

    conflicts: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    "update",
    "optimize",
]);

const {
    draggingBlock,
    ghostPosition,
    detectOverlap,
    buildCollisionMatrix,
    calculateVisualLayout,
} = useDragScheduler();

const days = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
];

const autoResolving = ref(false);

const hours = Array.from(
    { length: 15 },
    (_, i) => i + 6
);

const selectedSchedule = ref(null);

const localSchedules = ref(
    structuredClone(props.schedules)
);

watch(
    () => props.schedules,
    (value) => {

        localSchedules.value =
            structuredClone(value);

        recomputeVisuals();
    },
    {
        deep: true,
        immediate: true,
    }
);

const autoResolve = () => {

    autoResolving.value = true;

    const optimized =
        autoResolveSchedules(
            localSchedules.value
        );

    localSchedules.value = optimized;

    emit(
        "update",
        optimized
    );

    setTimeout(() => {

        autoResolving.value = false;

    }, 600);
};

/*
|--------------------------------------------------------------------------
| COLLISION MATRIX
|--------------------------------------------------------------------------
*/

const collisionMatrix = computed(() => {

    return buildCollisionMatrix(
        localSchedules.value
    );
});

/*
|--------------------------------------------------------------------------
| VISUAL RECALCULATION
|--------------------------------------------------------------------------
*/

const recomputeVisuals = () => {

    localSchedules.value =
        calculateVisualLayout(
            localSchedules.value
        );
};

/*
|--------------------------------------------------------------------------
| OVERLAP MAP
|--------------------------------------------------------------------------
*/

const overlapMap = computed(() => {

    return localSchedules.value.map(
        (schedule) => {

            const overlap =
                detectOverlap(
                    schedule,
                    localSchedules.value
                );

            return {
                id: schedule.id,
                overlap,
            };
        }
    );
});

/*
|--------------------------------------------------------------------------
| LIVE CONFLICTS
|--------------------------------------------------------------------------
*/

const professorConflicts = computed(() => {

    return collisionMatrix.value.filter(
        c => c.type === "professor"
    );
});

const classroomConflicts = computed(() => {

    return collisionMatrix.value.filter(
        c => c.type === "classroom"
    );
});

/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

const updateSchedule = (payload) => {

    localSchedules.value = payload;

    recomputeVisuals();

    emit(
        "update",
        localSchedules.value
    );

    triggerOptimization();
};

/*
|--------------------------------------------------------------------------
| RESIZE
|--------------------------------------------------------------------------
*/

const handleResize = (payload) => {

    const schedule =
        localSchedules.value.find(
            s => s.id === payload.id
        );

    if (!schedule) {
        return;
    }

    schedule.end_time =
        payload.end_time;

    recomputeVisuals();

    emit(
        "update",
        [...localSchedules.value]
    );

    triggerOptimization();
};

/*
|--------------------------------------------------------------------------
| SELECT
|--------------------------------------------------------------------------
*/

const selectSchedule = (schedule) => {

    selectedSchedule.value =
        schedule;
};

/*
|--------------------------------------------------------------------------
| OPTIMIZATION
|--------------------------------------------------------------------------
*/

const triggerOptimization = () => {

    autoResolve();

    emit(
        "optimize",
        localSchedules.value
    );
};

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

const hasConflict = (scheduleId) => {

    return overlapMap.value.some(
        overlap =>
            overlap.id === scheduleId
            && overlap.overlap
    );
};

const getConflictType = (scheduleId) => {

    const found =
        collisionMatrix.value.find(
            c =>
                c.schedule_ids.includes(
                    scheduleId
                )
        );

    return found?.type ?? null;
};



/*
|--------------------------------------------------------------------------
| INITIAL RECALCULATION
|--------------------------------------------------------------------------
*/

recomputeVisuals();
</script>

<template>

    <div class="
            overflow-hidden
            rounded-2xl
            border
            bg-white
            shadow-sm
            transition-all
            dark:border-gray-800
            dark:bg-gray-900
        ">

        <!-- HEADER -->

        <div class="grid border-b bg-gray-50 dark:bg-gray-800" style="
                grid-template-columns:
                80px repeat(5, 1fr)
            ">

            <div class="border-r p-4"></div>

            <div v-for="day in days" :key="day" class="
                    border-r
                    p-4
                    text-center
                    text-sm
                    font-semibold
                    text-gray-700
                    dark:text-gray-200
                ">
                {{ day }}
            </div>

        </div>

        <!-- BODY -->

        <div class="grid" style="
                grid-template-columns:
                80px repeat(5, 1fr)
            ">

            <!-- TIMELINE -->

            <SchedulerTimeline :hours="hours" />

            <!-- COLUMNS -->

            <SchedulerColumn v-for="day in days" :key="day" :day="day" :hours="hours" :schedules="localSchedules.filter(
                s => s.day === day
            )
                " :selected-schedule="selectedSchedule
                    " :dragging-block="draggingBlock
                        " :ghost-position="ghostPosition
                            " :conflicts="conflicts" :collision-matrix="collisionMatrix
                                " :professor-conflicts="professorConflicts
                                    " :classroom-conflicts="classroomConflicts
                                    " :has-conflict="hasConflict
                                    " :get-conflict-type="getConflictType
                                    " @update="updateSchedule" @resize="handleResize" @select="selectSchedule" />

        </div>

        <!-- FOOTER -->

        <div class="
                flex
                flex-wrap
                items-center
                gap-4
                border-t
                bg-gray-50
                px-4
                py-3
                text-xs
                text-gray-600
                dark:border-gray-800
                dark:bg-gray-800
                dark:text-gray-300
            ">

            <div class="flex items-center gap-2">
                <div class="
                        h-3
                        w-3
                        rounded-full
                        bg-red-500
                    " />
                Professor Conflicts
            </div>

            <div class="flex items-center gap-2">
                <div class="
                        h-3
                        w-3
                        rounded-full
                        bg-amber-500
                    " />
                Classroom Conflicts
            </div>

            <div class="flex items-center gap-2">
                <div class="
                        h-3
                        w-3
                        rounded-full
                        bg-emerald-500
                    " />
                Optimized Blocks
            </div>

        </div>

    </div>

</template>