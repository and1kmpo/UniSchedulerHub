import { ref } from "vue";

/*
|--------------------------------------------------------------------------
| GLOBAL ENGINE STATE
|--------------------------------------------------------------------------
*/

const draggingBlock = ref(null);

const resizingBlock = ref(null);

const ghostPosition = ref(null);

const isDragging = ref(false);

const isResizing = ref(false);

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/

const START_HOUR = 6;

const END_HOUR = 21;

const HOUR_HEIGHT = 80;

const SNAP_MINUTES = 60;

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

const hourToPixels = (hour) => {
    return (hour - START_HOUR) * HOUR_HEIGHT;
};

const pixelsToHour = (pixels) => {
    return Math.round(pixels / HOUR_HEIGHT) + START_HOUR;
};

const normalizeHour = (hour) => {
    return Math.min(END_HOUR, Math.max(START_HOUR, hour));
};

const snapHour = (hour) => {
    return normalizeHour(Math.round(hour));
};

const parseHour = (time) => {
    return Number(time.split(":")[0]);
};

const buildTime = (hour) => {
    return `${String(hour).padStart(2, "0")}:00`;
};

/*
|--------------------------------------------------------------------------
| DRAG LIFECYCLE
|--------------------------------------------------------------------------
*/

const startDrag = (block) => {
    draggingBlock.value = block;

    isDragging.value = true;
};

const clearDrag = () => {
    draggingBlock.value = null;

    ghostPosition.value = null;

    isDragging.value = false;
};

const updateGhost = (day, top) => {
    ghostPosition.value = {
        day,

        top: snapPixels(top),
    };
};

/*
|--------------------------------------------------------------------------
| RESIZE LIFECYCLE
|--------------------------------------------------------------------------
*/

const startResize = (block) => {
    resizingBlock.value = block;

    isResizing.value = true;
};

const clearResize = () => {
    resizingBlock.value = null;

    isResizing.value = false;
};

/*
|--------------------------------------------------------------------------
| SNAP ENGINE
|--------------------------------------------------------------------------
*/

const snapPixels = (value) => {
    return Math.round(value / HOUR_HEIGHT) * HOUR_HEIGHT;
};

const calculateTop = (y) => {
    return snapHour(pixelsToHour(y));
};

/*
|--------------------------------------------------------------------------
| DROP ENGINE
|--------------------------------------------------------------------------
*/

const dropSchedule = (day, hour) => {
    if (!draggingBlock.value) {
        return null;
    }

    const block = draggingBlock.value;

    const start = parseHour(block.start_time);

    const end = parseHour(block.end_time);

    const duration = end - start;

    block.day = day;

    block.start_time = buildTime(hour);

    block.end_time = buildTime(hour + duration);

    clearDrag();

    return block;
};

/*
|--------------------------------------------------------------------------
| POSITION ENGINE
|--------------------------------------------------------------------------
*/

const calculateBlockTop = (schedule) => {
    return hourToPixels(parseHour(schedule.start_time));
};

const calculateBlockHeight = (schedule) => {
    const start = parseHour(schedule.start_time);

    const end = parseHour(schedule.end_time);

    return (end - start) * HOUR_HEIGHT;
};

/*
|--------------------------------------------------------------------------
| COLLISION ENGINE
|--------------------------------------------------------------------------
*/

const overlaps = (a, b) => {
    if (a.id === b.id) {
        return false;
    }

    if (a.day !== b.day) {
        return false;
    }

    return (
        parseHour(a.start_time) < parseHour(b.end_time) &&
        parseHour(a.end_time) > parseHour(b.start_time)
    );
};

const detectOverlap = (block, schedules) => {
    return schedules.some((schedule) => overlaps(block, schedule));
};

const buildCollisionMap = (schedules) => {
    return schedules.map((schedule) => {
        const collisions = schedules.filter((s) => overlaps(schedule, s));

        return {
            id: schedule.id,

            collisions,
        };
    });
};

/*
|--------------------------------------------------------------------------
| AUTO POSITIONING
|--------------------------------------------------------------------------
*/

const findNextAvailableSlot = (schedule, schedules) => {
    let start = parseHour(schedule.start_time);

    const duration = parseHour(schedule.end_time) - start;

    while (start <= END_HOUR) {
        const candidate = {
            ...schedule,

            start_time: buildTime(start),

            end_time: buildTime(start + duration),
        };

        const collision = detectOverlap(candidate, schedules);

        if (!collision) {
            return candidate;
        }

        start++;
    }

    return schedule;
};

/*
|--------------------------------------------------------------------------
| SMART REPOSITIONING
|--------------------------------------------------------------------------
*/

const autoResolveCollision = (schedule, schedules) => {
    return findNextAvailableSlot(schedule, schedules);
};

/*
|--------------------------------------------------------------------------
| EXPORT
|--------------------------------------------------------------------------
*/

export function useDragScheduler() {
    return {
        /*
        |--------------------------------------------------------------------------
        | state
        |--------------------------------------------------------------------------
        */

        draggingBlock,
        draggedSchedule: draggingBlock,
        resizingBlock,

        ghostPosition,

        isDragging,
        isResizing,

        /*
        |--------------------------------------------------------------------------
        | drag
        |--------------------------------------------------------------------------
        */

        startDrag,
        clearDrag,
        stopDrag: clearDrag,

        updateGhost,

        /*
        |--------------------------------------------------------------------------
        | resize
        |--------------------------------------------------------------------------
        */

        startResize,
        clearResize,

        /*
        |--------------------------------------------------------------------------
        | snapping
        |--------------------------------------------------------------------------
        */

        snapPixels,
        calculateTop,

        /*
        |--------------------------------------------------------------------------
        | drop
        |--------------------------------------------------------------------------
        */

        dropSchedule,

        /*
        |--------------------------------------------------------------------------
        | positioning
        |--------------------------------------------------------------------------
        */

        calculateBlockTop,
        calculateBlockHeight,

        /*
        |--------------------------------------------------------------------------
        | collision
        |--------------------------------------------------------------------------
        */

        overlaps,
        detectOverlap,
        buildCollisionMap,

        /*
        |--------------------------------------------------------------------------
        | smart engine
        |--------------------------------------------------------------------------
        */

        findNextAvailableSlot,
        autoResolveCollision,

        /*
        |--------------------------------------------------------------------------
        | utils
        |--------------------------------------------------------------------------
        */

        parseHour,
        buildTime,
    };
}
