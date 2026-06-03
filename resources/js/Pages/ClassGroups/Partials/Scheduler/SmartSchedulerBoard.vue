<script setup>
import { computed, ref, watch } from "vue";
import axios from "axios";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";

import WeeklyPlannerGrid
    from "./WeeklyPlannerGrid.vue";

const props = defineProps({
    classGroupId: {
        type: [Number, String],
        required: true,
    },

    schedules: {
        type: Array,
        default: () => [],
    },

    canEdit: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits([
    "schedule-updated",
]);

const localSchedules = ref([
    ...props.schedules,
]);

watch(
    () => props.schedules,
    (schedules) => {
        localSchedules.value = [
            ...schedules,
        ];
    },
    {
        deep: true,
    }
);

watch(
    () => props.canEdit,
    (canEdit) => {
        if (!canEdit) {
            editable.value = false;
        }
    }
);

const editable = ref(false);

const saving = ref(false);

const saveError = ref("");

const conflicts = computed(() => {
    return localSchedules.value.filter((schedule) =>
        localSchedules.value.some((other) => (
            other !== schedule &&
            other.day === schedule.day &&
            other.start_time < schedule.end_time &&
            other.end_time > schedule.start_time
        ))
    );
});

const score = computed(() => {
    const value = Math.max(0, 100 - conflicts.value.length * 15);

    return {
        value,
        grade: value >= 90 ? "Excellent" : value >= 75 ? "Good" : value >= 60 ? "Average" : "Poor",
    };
});

function timeToMinutes(time) {
    const [hours = 0, minutes = 0] = String(time)
        .split(":")
        .map(Number);

    return hours * 60 + minutes;
}

function minutesToTime(totalMinutes) {
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;
}

const persistSchedule = async (schedule) => {
    if (!schedule.id) {
        return null;
    }

    const response = await axios.put(
        route("class-schedules.update", [
            props.classGroupId,
            schedule.id,
        ]),
        {
            day: schedule.day,
            start_time: schedule.start_time,
            end_time: schedule.end_time,
            classroom_id: schedule.classroom_id ?? null,
            status: schedule.status ?? "published",
        }
    );

    return response.data.schedule;
};

const replaceLocalSchedule = (scheduleId, updates) => {
    localSchedules.value = localSchedules.value.map((schedule) => {
        if (schedule.id !== scheduleId) {
            return schedule;
        }

        return {
            ...schedule,
            ...updates,
        };
    });
};

const moveSchedule = async ({
    schedule,
    day,
    hour,
}) => {
    if (!props.canEdit) {
        return;
    }

    const previous = {
        day: schedule.day,
        start_time: schedule.start_time,
        end_time: schedule.end_time,
    };

    const startMinutes = timeToMinutes(schedule.start_time);
    const endMinutes = timeToMinutes(schedule.end_time);
    const duration = Math.max(60, endMinutes - startMinutes);
    const nextStartMinutes = timeToMinutes(hour);
    const nextSchedule = {
        ...schedule,
        day,
        start_time: hour,
        end_time: minutesToTime(nextStartMinutes + duration),
    };

    replaceLocalSchedule(schedule.id, nextSchedule);

    try {
        saving.value = true;
        saveError.value = "";

        const persistedSchedule = await persistSchedule(nextSchedule);

        if (persistedSchedule) {
            replaceLocalSchedule(schedule.id, persistedSchedule);
            emit("schedule-updated", persistedSchedule);
        }
    } catch (exception) {
        replaceLocalSchedule(schedule.id, previous);

        saveError.value =
            exception.response?.data?.errors?.schedule?.[0] ||
            exception.response?.data?.message ||
            "The schedule change could not be saved.";
    } finally {
        saving.value = false;
    }
};

const resizeSchedule = async (schedule) => {
    if (!props.canEdit) {
        return;
    }

    const current = localSchedules.value.find((item) => item.id === schedule.id);
    const previous = current
        ? {
            end_time: current.end_time,
        }
        : null;

    replaceLocalSchedule(schedule.id, {
        end_time: schedule.end_time,
    });

    try {
        saving.value = true;
        saveError.value = "";

        const persistedSchedule = await persistSchedule({
            ...current,
            ...schedule,
        });

        if (persistedSchedule) {
            replaceLocalSchedule(schedule.id, persistedSchedule);
            emit("schedule-updated", persistedSchedule);
        }
    } catch (exception) {
        if (previous) {
            replaceLocalSchedule(schedule.id, previous);
        }

        saveError.value =
            exception.response?.data?.errors?.schedule?.[0] ||
            exception.response?.data?.message ||
            "The schedule resize could not be saved.";
    } finally {
        saving.value = false;
    }
};
</script>

<template>

    <div class="
            overflow-hidden
            rounded-2xl
            border
            border-gray-200
            bg-white
            shadow-sm
            dark:border-gray-800
            dark:bg-gray-900
        ">

        <!-- HEADER -->

        <div class="
                border-b
                border-gray-200
                px-6
                py-4
                dark:border-gray-800
            ">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Smart Scheduler
                    </h3>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ !canEdit ? "Schedule editing is locked" : editable ? "Visual editing is enabled" : "Official schedule overview" }}
                    </p>
                </div>

                <div v-if="canEdit" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <BaseButton type="button" :variant="editable ? 'secondary' : 'primary'" @click="editable = !editable">
                        <i :class="editable ? 'fa-solid fa-lock mr-2' : 'fa-solid fa-pen-to-square mr-2'" />
                        {{ editable ? "Done" : "Edit Layout" }}
                    </BaseButton>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:w-72">
                    <div class="rounded-xl border border-gray-200 p-3 text-center dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Score
                        </p>

                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ score.value }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-3 text-center dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Conflicts
                        </p>

                        <p class="text-xl font-bold" :class="conflicts.length ? 'text-red-600' : 'text-emerald-600'">
                            {{ conflicts.length }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- GRID -->

        <div class="p-6">

            <div v-if="saveError" class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ saveError }}
            </div>

            <div v-if="saving" class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-700">
                Saving schedule change...
            </div>

            <WeeklyPlannerGrid :schedules="localSchedules" :editable="canEdit && editable" @move="moveSchedule"
                @resize="resizeSchedule" />

        </div>

    </div>

</template>
