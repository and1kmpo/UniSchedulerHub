<script setup>
import { computed, ref, watch } from "vue";
import axios from "axios";
import FullCalendar from "@fullcalendar/vue3";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import esLocale from "@fullcalendar/core/locales/es";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

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

const referenceWeek = {
    monday: "2026-01-05",
    tuesday: "2026-01-06",
    wednesday: "2026-01-07",
    thursday: "2026-01-08",
    friday: "2026-01-09",
    saturday: "2026-01-10",
};

const dayNames = {
    1: "monday",
    2: "tuesday",
    3: "wednesday",
    4: "thursday",
    5: "friday",
    6: "saturday",
};

const localSchedules = ref([
    ...props.schedules,
]);

const editable = ref(false);
const saving = ref(false);
const saveError = ref("");
const selectedSchedule = ref(null);

watch(
    () => props.schedules,
    (schedules) => {
        localSchedules.value = [
            ...(schedules || []),
        ];
    },
    { deep: true }
);

watch(
    () => props.canEdit,
    (canEdit) => {
        if (!canEdit) {
            editable.value = false;
        }
    }
);

const conflicts = computed(() => {
    return localSchedules.value.filter((schedule) =>
        localSchedules.value.some((other) => (
            other.id !== schedule.id &&
            other.day === schedule.day &&
            normalizeTime(other.start_time) < normalizeTime(schedule.end_time) &&
            normalizeTime(other.end_time) > normalizeTime(schedule.start_time)
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

const calendarEvents = computed(() => {
    return localSchedules.value
        .filter((schedule) => referenceWeek[schedule.day])
        .map((schedule) => ({
            id: String(schedule.id),
            title: schedule.subject || schedule.name || "Class",
            start: `${referenceWeek[schedule.day]}T${normalizeTime(schedule.start_time)}:00`,
            end: `${referenceWeek[schedule.day]}T${normalizeTime(schedule.end_time)}:00`,
            classNames: [
                schedule.status === "cancelled"
                    ? "uh-calendar-event-cancelled"
                    : "uh-calendar-event",
            ],
            extendedProps: {
                raw: schedule,
                classroom: schedule.classroom || "No classroom",
                professor: schedule.professor || "No professor",
                status: schedule.status || "published",
            },
        }));
});

const calendarOptions = computed(() => ({
    plugins: [
        timeGridPlugin,
        interactionPlugin,
    ],
    locales: [
        esLocale,
    ],
    locale: "es",
    initialView: "timeGridWeek",
    initialDate: "2026-01-05",
    firstDay: 1,
    hiddenDays: [
        0,
    ],
    allDaySlot: false,
    nowIndicator: false,
    expandRows: true,
    height: "auto",
    slotMinTime: "06:00:00",
    slotMaxTime: "22:00:00",
    slotDuration: "00:30:00",
    snapDuration: "00:30:00",
    slotLabelInterval: "01:00:00",
    dayHeaderFormat: {
        weekday: "short",
    },
    headerToolbar: false,
    editable: props.canEdit && editable.value,
    eventStartEditable: props.canEdit && editable.value,
    eventDurationEditable: props.canEdit && editable.value,
    eventResizableFromStart: true,
    dragRevertDuration: 180,
    eventMinHeight: 36,
    events: calendarEvents.value,
    eventAllow: (dropInfo) => {
        return minutesBetween(dropInfo.start, dropInfo.end) >= 30;
    },
    eventContent: renderEventContent,
    eventClick: handleEventClick,
    eventDrop: persistCalendarChange,
    eventResize: persistCalendarChange,
}));

function normalizeTime(time) {
    const [hours = "00", minutes = "00"] = String(time || "").split(":");

    return `${hours.padStart(2, "0")}:${minutes.padStart(2, "0")}`;
}

function formatDisplayTime(time) {
    return normalizeTime(time);
}

function timeFromDate(date) {
    return `${String(date.getHours()).padStart(2, "0")}:${String(date.getMinutes()).padStart(2, "0")}`;
}

function minutesBetween(start, end) {
    if (!start || !end) {
        return 0;
    }

    return Math.round((end.getTime() - start.getTime()) / 60000);
}

function findSchedule(id) {
    return localSchedules.value.find((schedule) => String(schedule.id) === String(id));
}

function replaceLocalSchedule(scheduleId, updates) {
    localSchedules.value = localSchedules.value.map((schedule) => {
        if (String(schedule.id) !== String(scheduleId)) {
            return schedule;
        }

        return {
            ...schedule,
            ...updates,
        };
    });
}

function readableSchedule(schedule) {
    return {
        subject: schedule?.subject || schedule?.name || "Class",
        professor: schedule?.professor || "No professor",
        classroom: schedule?.classroom || "No classroom",
        status: schedule?.status || "published",
        day: schedule?.day || "monday",
        start_time: formatDisplayTime(schedule?.start_time),
        end_time: formatDisplayTime(schedule?.end_time),
    };
}

function renderEventContent(arg) {
    const schedule = readableSchedule(arg.event.extendedProps.raw);

    return {
        html: `
            <div class="uh-calendar-event-body">
                <div class="uh-calendar-event-title">${escapeHtml(schedule.subject)}</div>
                <div class="uh-calendar-event-time">${escapeHtml(schedule.start_time)} - ${escapeHtml(schedule.end_time)}</div>
                <div class="uh-calendar-event-meta">${escapeHtml(schedule.classroom)}</div>
            </div>
        `,
    };
}

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function handleEventClick(info) {
    selectedSchedule.value = readableSchedule(info.event.extendedProps.raw);
}

async function persistCalendarChange(info) {
    if (!props.canEdit) {
        info.revert();
        return;
    }

    const current = findSchedule(info.event.id);
    const day = dayNames[info.event.start.getDay()];

    if (!current || !day || minutesBetween(info.event.start, info.event.end) < 30) {
        info.revert();
        saveError.value = "The selected schedule range is not valid.";
        return;
    }

    const nextSchedule = {
        ...current,
        day,
        start_time: timeFromDate(info.event.start),
        end_time: timeFromDate(info.event.end),
    };

    replaceLocalSchedule(current.id, nextSchedule);
    saveError.value = "";

    try {
        saving.value = true;

        const response = await axios.put(
            route("class-schedules.update", [
                props.classGroupId,
                current.id,
            ]),
            {
                day: nextSchedule.day,
                start_time: nextSchedule.start_time,
                end_time: nextSchedule.end_time,
                classroom_id: nextSchedule.classroom_id ?? null,
                status: nextSchedule.status ?? "published",
            }
        );

        const updatedSchedule = {
            ...nextSchedule,
            ...(response.data.schedule || {}),
        };

        replaceLocalSchedule(current.id, updatedSchedule);
        emit("schedule-updated", updatedSchedule);
        selectedSchedule.value = readableSchedule(updatedSchedule);
    } catch (exception) {
        info.revert();
        replaceLocalSchedule(current.id, current);

        saveError.value =
            exception.response?.data?.errors?.schedule?.[0] ||
            exception.response?.data?.message ||
            "The schedule change could not be saved.";
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Smart Scheduler
                    </h3>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ !canEdit ? "Schedule editing is locked" : editable ? "Drag or resize official blocks" : "Official academic week overview" }}
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

        <div class="space-y-4 p-6">
            <div v-if="saveError" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ saveError }}
            </div>

            <div v-if="saving" class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-700">
                Saving schedule change...
            </div>

            <FullCalendar class="uh-full-calendar" :options="calendarOptions" />

            <div v-if="selectedSchedule" class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ selectedSchedule.subject }}
                        </h4>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ selectedSchedule.start_time }} - {{ selectedSchedule.end_time }}
                        </p>
                    </div>

                    <StatusBadge :status="selectedSchedule.status" />
                </div>

                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Professor
                        </p>
                        <p class="mt-1 text-gray-700 dark:text-gray-200">
                            {{ selectedSchedule.professor }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Classroom
                        </p>
                        <p class="mt-1 text-gray-700 dark:text-gray-200">
                            {{ selectedSchedule.classroom }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.uh-full-calendar {
    --fc-border-color: rgb(229 231 235);
    --fc-page-bg-color: transparent;
    --fc-neutral-bg-color: rgb(249 250 251);
    --fc-today-bg-color: rgb(238 242 255);
    --fc-event-bg-color: rgb(79 70 229);
    --fc-event-border-color: rgb(67 56 202);
    --fc-event-text-color: white;
    color: rgb(17 24 39);
}

.dark .uh-full-calendar {
    --fc-border-color: rgb(31 41 55);
    --fc-neutral-bg-color: rgb(17 24 39);
    --fc-today-bg-color: rgb(30 41 59);
    color: rgb(243 244 246);
}

.uh-full-calendar .fc-scrollgrid {
    border-radius: 0.75rem;
    overflow: hidden;
}

.uh-full-calendar .fc-col-header-cell {
    padding: 0.5rem 0;
}

.uh-full-calendar .fc-timegrid-slot {
    height: 2.5rem;
}

.uh-full-calendar .fc-timegrid-event {
    border-radius: 0.5rem;
    box-shadow: 0 8px 18px rgb(79 70 229 / 18%);
}

.uh-full-calendar .uh-calendar-event {
    background: rgb(79 70 229);
    border-color: rgb(67 56 202);
}

.uh-full-calendar .uh-calendar-event-cancelled {
    background: rgb(107 114 128);
    border-color: rgb(75 85 99);
    opacity: 0.75;
}

.uh-calendar-event-body {
    display: grid;
    gap: 0.125rem;
    padding: 0.125rem;
    line-height: 1.15;
}

.uh-calendar-event-title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.75rem;
    font-weight: 700;
}

.uh-calendar-event-time,
.uh-calendar-event-meta {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.68rem;
    opacity: 0.92;
}
</style>
