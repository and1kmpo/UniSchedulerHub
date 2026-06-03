export const PIXELS_PER_HOUR = 80;

export const MINUTES_PER_HOUR = 60;

export function parseMinutes(time) {
    const [hours = 0, minutes = 0] = String(time)
        .split(":")
        .map(Number);

    return hours * MINUTES_PER_HOUR + minutes;
}

export function parseHour(time) {
    return Math.floor(parseMinutes(time) / MINUTES_PER_HOUR);
}

export function formatHour(hour) {
    return `${String(hour).padStart(2, "0")}:00`;
}

export function formatMinutes(totalMinutes) {
    const hours = Math.floor(totalMinutes / MINUTES_PER_HOUR);
    const minutes = totalMinutes % MINUTES_PER_HOUR;

    return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;
}

export function calculateDuration(start, end) {
    return parseMinutes(end) - parseMinutes(start);
}

export function snapMinutes(pixels, step = 30) {
    const minutes = (pixels / PIXELS_PER_HOUR) * MINUTES_PER_HOUR;

    return Math.round(minutes / step) * step;
}

export function calculateResizeMinutes(startMinutes, deltaPixels) {
    const deltaMinutes = snapMinutes(deltaPixels);

    return startMinutes + deltaMinutes;
}

export function limitResize(start, end, min = 60, max = 14 * MINUTES_PER_HOUR) {
    const duration = end - start;

    if (duration < min) {
        return start + min;
    }

    if (duration > max) {
        return start + max;
    }

    return end;
}
