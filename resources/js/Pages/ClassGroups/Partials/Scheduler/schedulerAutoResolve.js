export function normalizeHour(hour) {
    return String(hour).padStart(2, "0") + ":00";
}

export function parseHour(time) {
    return Number(String(time).split(":")[0]);
}

export function detectCollisions(schedules = []) {
    const collisions = [];

    schedules.forEach((a) => {
        schedules.forEach((b) => {
            if (a.id === b.id) {
                return;
            }

            if (a.day !== b.day) {
                return;
            }

            const aStart = parseHour(a.start_time);
            const aEnd = parseHour(a.end_time);

            const bStart = parseHour(b.start_time);
            const bEnd = parseHour(b.end_time);

            const overlap = aStart < bEnd && aEnd > bStart;

            if (overlap) {
                collisions.push([a.id, b.id]);
            }
        });
    });

    return collisions;
}

export function findNextAvailableSlot(
    schedule,
    schedules,
    minHour = 6,
    maxHour = 21,
) {
    const duration =
        parseHour(schedule.end_time) - parseHour(schedule.start_time);

    for (let hour = minHour; hour <= maxHour - duration; hour++) {
        const candidate = {
            ...schedule,
            start_time: normalizeHour(hour),
            end_time: normalizeHour(hour + duration),
        };

        const collision = schedules.some((s) => {
            if (s.id === schedule.id) {
                return false;
            }

            if (s.day !== candidate.day) {
                return false;
            }

            return (
                parseHour(candidate.start_time) < parseHour(s.end_time) &&
                parseHour(candidate.end_time) > parseHour(s.start_time)
            );
        });

        if (!collision) {
            return candidate;
        }
    }

    return schedule;
}

export function autoResolveSchedules(schedules = []) {
    const resolved = structuredClone(schedules);

    resolved.sort((a, b) => {
        return parseHour(a.start_time) - parseHour(b.start_time);
    });

    resolved.forEach((schedule, index) => {
        const others = resolved.filter((_, i) => i !== index);

        const collision = others.some((s) => {
            if (s.day !== schedule.day) {
                return false;
            }

            return (
                parseHour(schedule.start_time) < parseHour(s.end_time) &&
                parseHour(schedule.end_time) > parseHour(s.start_time)
            );
        });

        if (collision) {
            const moved = findNextAvailableSlot(schedule, others);

            schedule.start_time = moved.start_time;

            schedule.end_time = moved.end_time;

            schedule.optimized = true;
        }
    });

    return resolved;
}
