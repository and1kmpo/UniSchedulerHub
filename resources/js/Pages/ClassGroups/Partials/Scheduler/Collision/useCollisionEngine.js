import { computed } from "vue";

import { buildCollisionMatrix } from "./collisionMatrix";

import { resolveVisualOverlaps } from "./visualStacking";

import { applyAdaptiveSpacing } from "./adaptiveSpacing";

export function useCollisionEngine(schedules) {
    /*
    |--------------------------------------------------------------------------
    | COLLISION MATRIX
    |--------------------------------------------------------------------------
    */

    const collisionMatrix = computed(() => {
        return buildCollisionMatrix(schedules.value);
    });

    /*
    |--------------------------------------------------------------------------
    | VISUAL RESOLUTION
    |--------------------------------------------------------------------------
    */

    const resolvedSchedules = computed(() => {
        const resolved = resolveVisualOverlaps(
            schedules.value,
            collisionMatrix.value,
        );

        return applyAdaptiveSpacing(resolved);
    });

    /*
    |--------------------------------------------------------------------------
    | CONFLICTS
    |--------------------------------------------------------------------------
    */

    const conflicts = computed(() => {
        return collisionMatrix.value.filter((c) => c.overlap);
    });

    /*
    |--------------------------------------------------------------------------
    | PROFESSOR CONFLICTS
    |--------------------------------------------------------------------------
    */

    const professorConflicts = computed(() => {
        return collisionMatrix.value.filter((c) => c.type === "professor");
    });

    /*
    |--------------------------------------------------------------------------
    | CLASSROOM CONFLICTS
    |--------------------------------------------------------------------------
    */

    const classroomConflicts = computed(() => {
        return collisionMatrix.value.filter((c) => c.type === "classroom");
    });

    return {
        collisionMatrix,

        resolvedSchedules,

        conflicts,

        professorConflicts,

        classroomConflicts,
    };
}
