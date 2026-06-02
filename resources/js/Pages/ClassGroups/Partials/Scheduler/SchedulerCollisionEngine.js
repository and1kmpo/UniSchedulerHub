export function buildCollisionGroups(schedules) {
    const groups = [];

    schedules.forEach((schedule) => {
        let added = false;

        for (const group of groups) {
            const collides = group.some((item) => overlaps(item, schedule));

            if (collides) {
                group.push(schedule);

                added = true;

                break;
            }
        }

        if (!added) {
            groups.push([schedule]);
        }
    });

    return groups;
}

export function overlaps(a, b) {
    if (a.id === b.id) {
        return false;
    }

    if (a.day !== b.day) {
        return false;
    }

    const aStart = parseHour(a.start_time);

    const aEnd = parseHour(a.end_time);

    const bStart = parseHour(b.start_time);

    const bEnd = parseHour(b.end_time);

    return aStart < bEnd && aEnd > bStart;
}

export function parseHour(time) {
    return Number(time.split(":")[0]);
}

export function buildVisualLayout(schedules) {
    const groups = buildCollisionGroups(schedules);

    const layout = [];

    groups.forEach((group) => {
        const width = 100 / group.length;

        group.forEach((schedule, index) => {
            layout.push({
                ...schedule,

                collisionGroup: group.map((g) => g.id),

                visual: {
                    width: `calc(${width}% - 6px)`,

                    left: `calc(${index * width}% + 3px)`,

                    zIndex: 20 + index,

                    overlapCount: group.length,

                    overlapIndex: index,
                },
            });
        });
    });

    return layout;
}

export function detectProfessorConflicts(schedules) {
    const conflicts = [];

    schedules.forEach((a) => {
        schedules.forEach((b) => {
            if (a.id === b.id) {
                return;
            }

            if (a.professor_id !== b.professor_id) {
                return;
            }

            if (overlaps(a, b)) {
                conflicts.push({
                    type: "professor",

                    schedules: [a.id, b.id],
                });
            }
        });
    });

    return conflicts;
}

export function detectClassroomConflicts(schedules) {
    const conflicts = [];

    schedules.forEach((a) => {
        schedules.forEach((b) => {
            if (a.id === b.id) {
                return;
            }

            if (a.classroom_id !== b.classroom_id) {
                return;
            }

            if (overlaps(a, b)) {
                conflicts.push({
                    type: "classroom",

                    schedules: [a.id, b.id],
                });
            }
        });
    });

    return conflicts;
}
