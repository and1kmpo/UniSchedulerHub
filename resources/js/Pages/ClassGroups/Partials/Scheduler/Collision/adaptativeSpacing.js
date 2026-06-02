export function applyAdaptiveSpacing(schedules) {
    return schedules.map((schedule) => {
        return {
            ...schedule,

            visual: {
                ...schedule.visual,

                top: (schedule.visual?.top ?? 0) + 2,

                padding: 2,
            },
        };
    });
}
