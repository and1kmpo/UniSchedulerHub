export const PIXELS_PER_HOUR = 80;

export function parseHour(time) {
    return Number(time.split(":")[0]);
}

export function formatHour(hour) {
    return `${String(hour).padStart(2, "0")}:00`;
}

export function calculateDuration(start, end) {
    return parseHour(end) - parseHour(start);
}

export function snapHour(pixels) {
    return Math.round(pixels / PIXELS_PER_HOUR);
}

export function calculateResizeHour(startHour, deltaPixels) {
    const deltaHours = snapHour(deltaPixels);

    return startHour + deltaHours;
}

export function limitResize(start, end, min = 1, max = 14) {
    const duration = end - start;

    if (duration < min) {
        return start + min;
    }

    if (duration > max) {
        return start + max;
    }

    return end;
}
