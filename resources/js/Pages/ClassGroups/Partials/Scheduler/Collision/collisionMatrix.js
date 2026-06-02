export function buildCollisionMatrix(schedules) {
    const collisions = [];

    for (let i = 0; i < schedules.length; i++) {
        for (let j = i + 1; j < schedules.length; j++) {
            const a = schedules[i];
            const b = schedules[j];

            /*
            |--------------------------------------------------------------------------
            | SAME DAY
            |--------------------------------------------------------------------------
            */

            if (a.day !== b.day) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | OVERLAP
            |--------------------------------------------------------------------------
            */

            const overlap =
                a.start_time < b.end_time && a.end_time > b.start_time;

            if (!overlap) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | TYPE
            |--------------------------------------------------------------------------
            */

            let type = "general";

            if (a.professor_id && a.professor_id === b.professor_id) {
                type = "professor";
            }

            if (a.classroom_id && a.classroom_id === b.classroom_id) {
                type = "classroom";
            }

            collisions.push({
                overlap: true,

                type,

                schedules: [a.id, b.id],
            });
        }
    }

    return collisions;
}
