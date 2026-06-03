import { ref } from "vue";

import {
    parseMinutes,
    formatMinutes,
    calculateResizeMinutes,
    limitResize,
} from "./resizeUtils";

export function useResizeScheduler() {
    const resizing = ref(null);

    const previewHeight = ref(null);

    const onStop = ref(null);

    const startResize = (event, schedule, callback = null) => {
        resizing.value = {
            schedule,

            startY: event.clientY,

            originalEnd: parseMinutes(schedule.end_time),
        };

        onStop.value = callback;

        window.addEventListener("mousemove", resizeMove);

        window.addEventListener("mouseup", stopResize);
    };

    const resizeMove = (event) => {
        if (!resizing.value) {
            return;
        }

        const deltaY = event.clientY - resizing.value.startY;

        previewHeight.value = deltaY;

        const startMinutes = parseMinutes(resizing.value.schedule.start_time);

        let newEnd = calculateResizeMinutes(resizing.value.originalEnd, deltaY);

        newEnd = limitResize(startMinutes, newEnd);

        resizing.value.schedule.end_time = formatMinutes(newEnd);
    };

    const stopResize = () => {
        if (resizing.value?.schedule && onStop.value) {
            onStop.value(resizing.value.schedule);
        }

        resizing.value = null;

        previewHeight.value = null;

        onStop.value = null;

        window.removeEventListener("mousemove", resizeMove);

        window.removeEventListener("mouseup", stopResize);
    };

    return {
        resizing,

        previewHeight,

        startResize,

        stopResize,
    };
}
