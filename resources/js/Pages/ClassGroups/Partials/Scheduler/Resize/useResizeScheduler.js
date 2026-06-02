import { ref } from "vue";

import {
    parseHour,
    formatHour,
    calculateResizeHour,
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

            originalEnd: parseHour(schedule.end_time),
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

        const startHour = parseHour(resizing.value.schedule.start_time);

        let newEnd = calculateResizeHour(resizing.value.originalEnd, deltaY);

        newEnd = limitResize(startHour, newEnd);

        resizing.value.schedule.end_time = formatHour(newEnd);
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
