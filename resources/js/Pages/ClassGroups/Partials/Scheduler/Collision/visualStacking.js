export function resolveVisualOverlaps(schedules, collisions) {
    return schedules.map((schedule) => {
        const related = collisions.filter((c) =>
            c.schedules.includes(schedule.id),
        );

        const overlapCount = related.length;

        /*
        |--------------------------------------------------------------------------
        | DYNAMIC WIDTH
        |--------------------------------------------------------------------------
        */

        const width =
            overlapCount > 0 ? `${100 / (overlapCount + 1)}%` : "100%";

        /*
        |--------------------------------------------------------------------------
        | POSITION INDEX
        |--------------------------------------------------------------------------
        */

        const position =
            overlapCount > 0
                ? related.findIndex((c) => c.schedules[0] === schedule.id)
                : 0;

        return {
            ...schedule,

            visual: {
                width,

                left: `calc(${(position * 100) / (overlapCount + 1)}% + 4px)`,

                zIndex: 20 + overlapCount,
            },
        };
    });
}
