export function timeToMinutes(time) {
    const [hours, minutes] = time.split(":").map(Number);

    return hours * 60 + minutes;
}

export function hasOverlap(a, b) {
    if (a.day !== b.day) {
        return false;
    }

    const startA = timeToMinutes(a.start_time);
    const endA = timeToMinutes(a.end_time);

    const startB = timeToMinutes(b.start_time);
    const endB = timeToMinutes(b.end_time);

    return startA < endB && endA > startB;
}
