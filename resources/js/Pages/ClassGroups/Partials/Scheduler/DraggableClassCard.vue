<script setup>
import {
    computed,
} from "vue";

import {
    useDragScheduler,
} from "./useDragScheduler";

import ResizeHandle
    from "./Resize/ResizeHandle.vue";

import {
    useResizeScheduler,
} from "./Resize/useResizeScheduler";

const props = defineProps({
    schedule: {
        type: Object,
        required: true,
    },

    conflict: {
        type: Boolean,
        default: false,
    },

    optimized: {
        type: Boolean,
        default: false,
    },

    selected: {
        type: Boolean,
        default: false,
    },

    collisionType: {
        type: String,
        default: null,
    },

    dragging: {
        type: Boolean,
        default: false,
    },

    editable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits([
    "resize",
    "select",
]);

/*
|--------------------------------------------------------------------------
| DRAG ENGINE
|--------------------------------------------------------------------------
*/

const {
    startDrag,
    stopDrag,
} = useDragScheduler();

/*
|--------------------------------------------------------------------------
| RESIZE ENGINE
|--------------------------------------------------------------------------
*/

const {
    resizing,
    startResize,
} = useResizeScheduler();

/*
|--------------------------------------------------------------------------
| TIME CALCULATIONS
|--------------------------------------------------------------------------
*/

const startHour = computed(() => {

    return Number(
        props.schedule
            .start_time
            .split(":")[0]
    );
});

const endHour = computed(() => {

    return Number(
        props.schedule
            .end_time
            .split(":")[0]
    );
});

const duration = computed(() => {

    return (
        endHour.value
        -
        startHour.value
    ) * 80;
});

/*
|--------------------------------------------------------------------------
| COLLISION STATES
|--------------------------------------------------------------------------
*/

const isProfessorConflict = computed(() => {

    return props.collisionType
        === "professor";
});

const isClassroomConflict = computed(() => {

    return props.collisionType
        === "classroom";
});

/*
|--------------------------------------------------------------------------
| VISUAL STATES
|--------------------------------------------------------------------------
*/

const blockClasses = computed(() => {

    /*
    |--------------------------------------------------------------------------
    | PROFESSOR CONFLICT
    |--------------------------------------------------------------------------
    */

    if (isProfessorConflict.value) {

        return `
            border-red-500
            bg-red-100
            dark:bg-red-900/40
            ring-2
            ring-red-400
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | CLASSROOM CONFLICT
    |--------------------------------------------------------------------------
    */

    if (isClassroomConflict.value) {

        return `
            border-amber-500
            bg-amber-100
            dark:bg-amber-900/30
            ring-2
            ring-amber-400
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | GENERAL CONFLICT
    |--------------------------------------------------------------------------
    */

    if (props.conflict) {

        return `
            border-rose-500
            bg-rose-100
            dark:bg-rose-900/30
            ring-2
            ring-rose-400
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIMIZED
    |--------------------------------------------------------------------------
    */

    if (props.optimized) {

        return `
            border-emerald-500
            bg-emerald-100
            dark:bg-emerald-900/30
            ring-2
            ring-emerald-300
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | SELECTED
    |--------------------------------------------------------------------------
    */

    if (props.selected) {

        return `
            border-indigo-500
            bg-indigo-100
            dark:bg-indigo-900/40
            ring-2
            ring-indigo-400
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | DEFAULT
    |--------------------------------------------------------------------------
    */

    return `
        border-indigo-300
        bg-indigo-100
        dark:bg-indigo-900/30
    `;
});

/*
|--------------------------------------------------------------------------
| DYNAMIC STYLES
|--------------------------------------------------------------------------
*/

const blockStyle = computed(() => {

    return {

        top:
            `${props.schedule.top ?? 2}px`,

        height:
            `${props.schedule.height ?? duration.value}px`,

        width:
            props.schedule.visual?.width
            ?? "calc(100% - 8px)",

        left:
            props.schedule.visual?.left
            ?? "4px",

        zIndex:
            props.schedule.visual?.zIndex
            ?? 20,

        transform:
            props.dragging
                ? "scale(1.02)"
                : "scale(1)",

        opacity:
            props.dragging
                ? 0.92
                : 1,
    };
});
</script>

<template>

    <div :draggable="editable" @dragstart="editable ? startDrag(schedule) : null" @dragend="stopDrag"
        @click="$emit('select', schedule)" class="
            absolute
            overflow-hidden
            rounded-xl
            border
            p-2
            shadow-sm
            select-none
            transition-all
            duration-300
            ease-in-out
            hover:shadow-lg
            hover:z-50
            active:scale-[0.98]
        " :class="[
            blockClasses,

            editable
                ? 'cursor-move'
                : 'cursor-default',

            optimized
                ? 'animate-pulse'
                : '',

            resizing?.schedule?.id === schedule.id
                ? 'ring-4 ring-indigo-300'
                : '',
        ]" :style="blockStyle">

        <!-- GHOST PREVIEW -->

        <div v-if="dragging" class="
                absolute
                inset-0
                rounded-xl
                border-2
                border-dashed
                border-indigo-400
                bg-indigo-200/20
                pointer-events-none
            " />

        <!-- HEADER -->

        <div class="
                flex
                items-start
                justify-between
                gap-2
            ">

            <div class="min-w-0">

                <!-- SUBJECT -->

                <div class="
                        truncate
                        text-xs
                        font-semibold
                        text-gray-900
                        dark:text-white
                    ">
                    {{ schedule.subject ?? "Class" }}
                </div>

                <!-- TIME -->

                <div class="
                        mt-1
                        text-[11px]
                        text-gray-600
                        dark:text-gray-300
                    ">
                    {{ schedule.start_time }}
                    —
                    {{ schedule.end_time }}
                </div>

            </div>

            <!-- BADGES -->

            <div class="flex flex-col gap-1">

                <!-- PROFESSOR CONFLICT -->

                <div v-if="isProfessorConflict" class="
                        rounded-full
                        bg-red-500
                        px-2
                        py-0.5
                        text-[10px]
                        font-semibold
                        text-white
                    ">
                    Professor
                </div>

                <!-- CLASSROOM CONFLICT -->

                <div v-if="isClassroomConflict" class="
                        rounded-full
                        bg-amber-500
                        px-2
                        py-0.5
                        text-[10px]
                        font-semibold
                        text-white
                    ">
                    Room
                </div>

                <!-- OPTIMIZED -->

                <div v-if="optimized" class="
                        rounded-full
                        bg-emerald-500
                        px-2
                        py-0.5
                        text-[10px]
                        font-semibold
                        text-white
                    ">
                    Optimized
                </div>

            </div>

        </div>

        <!-- PROFESSOR -->

        <div v-if="schedule.professor" class="
                mt-2
                truncate
                text-[10px]
                text-gray-500
                dark:text-gray-400
            ">
            👨‍🏫 {{ schedule.professor }}
        </div>

        <!-- CLASSROOM -->

        <div v-if="schedule.classroom" class="
                mt-1
                truncate
                text-[10px]
                text-gray-500
                dark:text-gray-400
            ">
            🏫 {{ schedule.classroom }}
        </div>

        <!-- LOAD -->

        <div v-if="schedule.load" class="
                mt-1
                text-[10px]
                text-gray-400
            ">
            Load:
            {{ schedule.load }}
        </div>

        <!-- OVERLAY -->

        <div v-if="selected" class="
                absolute
                inset-0
                rounded-xl
                ring-2
                ring-indigo-400/40
                pointer-events-none
            " />

        <!-- COLLISION OVERLAY -->

        <div v-if="conflict" class="
                absolute
                inset-0
                bg-red-500/5
                pointer-events-none
            " />

        <!-- RESIZE HANDLE -->

        <ResizeHandle v-if="editable" :active="resizing?.schedule?.id
            ===
            schedule.id
            " @mousedown.stop="
                startResize(
                    $event,
                    schedule,
                    (resizedSchedule) => emit(
                        'resize',
                        resizedSchedule
                    )
                )
                " />

    </div>

</template>
