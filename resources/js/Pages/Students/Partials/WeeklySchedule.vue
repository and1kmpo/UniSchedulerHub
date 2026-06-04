<script setup>
import { computed, ref } from "vue";
import FullCalendar from "@fullcalendar/vue3";
import timeGridPlugin from "@fullcalendar/timegrid";

import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

const props = defineProps({
    schedules: {
        type: Array,
        default: () => [],
    },
});

const selectedSchedule = ref(null);

const referenceWeek = {
    monday: "2026-01-05",
    tuesday: "2026-01-06",
    wednesday: "2026-01-07",
    thursday: "2026-01-08",
    friday: "2026-01-09",
    saturday: "2026-01-10",
};

const orderedDays = Object.keys(referenceWeek);

const calendarEvents = computed(() =>
    props.schedules
        .filter((schedule) => referenceWeek[String(schedule.day || "").toLowerCase()])
        .map((schedule, index) => {
            const day = String(schedule.day || "").toLowerCase();
            const subject = schedule.subject?.name || schedule.subject_name || "Class";
            const code = schedule.subject?.code || schedule.group?.code || "";

            return {
                id: String(schedule.id || `${day}-${schedule.start_time}-${index}`),
                title: code ? `${code} - ${subject}` : subject,
                start: `${referenceWeek[day]}T${normalizeTime(schedule.start_time)}:00`,
                end: `${referenceWeek[day]}T${normalizeTime(schedule.end_time)}:00`,
                classNames: ["uh-student-schedule-event"],
                extendedProps: {
                    raw: schedule,
                },
            };
        })
);

const groupedSchedules = computed(() =>
    orderedDays
        .map((day) => ({
            day,
            schedules: props.schedules
                .filter((schedule) => String(schedule.day || "").toLowerCase() === day)
                .sort((a, b) => normalizeTime(a.start_time).localeCompare(normalizeTime(b.start_time))),
        }))
        .filter((group) => group.schedules.length)
);

const calendarOptions = computed(() => ({
    plugins: [timeGridPlugin],
    locale: "en",
    initialView: "timeGridWeek",
    initialDate: "2026-01-05",
    firstDay: 1,
    hiddenDays: [0],
    allDaySlot: false,
    nowIndicator: false,
    expandRows: true,
    height: "auto",
    slotMinTime: "06:00:00",
    slotMaxTime: "22:00:00",
    slotDuration: "00:30:00",
    slotLabelInterval: "01:00:00",
    slotLabelFormat: {
        hour: "numeric",
        meridiem: "short",
        hour12: true,
    },
    eventTimeFormat: {
        hour: "numeric",
        minute: "2-digit",
        meridiem: "short",
        hour12: true,
    },
    dayHeaderFormat: {
        weekday: "short",
    },
    headerToolbar: false,
    editable: false,
    eventStartEditable: false,
    eventDurationEditable: false,
    eventMinHeight: 58,
    events: calendarEvents.value,
    eventContent: renderEventContent,
    eventClick: (info) => {
        selectedSchedule.value = readableSchedule(info.event.extendedProps.raw);
    },
    eventDidMount: (info) => {
        const schedule = readableSchedule(info.event.extendedProps.raw);
        info.el.setAttribute(
            "title",
            `${schedule.subject}\n${schedule.time}\n${schedule.group}\n${schedule.classroom}\n${schedule.professor}`
        );
    },
}));

function normalizeTime(time) {
    const [hours = "00", minutes = "00"] = String(time || "").split(":");

    return `${hours.padStart(2, "0")}:${minutes.padStart(2, "0")}`;
}

function formatTime(time) {
    const [hours = "0", minutes = "0"] = normalizeTime(time).split(":");
    const date = new Date(2026, 0, 5, Number(hours), Number(minutes));

    return new Intl.DateTimeFormat("en-US", {
        hour: "numeric",
        minute: Number(minutes) === 0 ? undefined : "2-digit",
        hour12: true,
    }).format(date);
}

function formatDay(day) {
    return String(day || "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function readableSchedule(schedule) {
    const subjectName = schedule?.subject?.name || schedule?.subject_name || "Class";
    const subjectCode = schedule?.subject?.code;

    return {
        subject: subjectCode ? `${subjectCode} - ${subjectName}` : subjectName,
        time: `${formatTime(schedule?.start_time)} - ${formatTime(schedule?.end_time)}`,
        day: formatDay(schedule?.day),
        group: schedule?.group?.code || schedule?.group_code || "Group pending",
        professor: schedule?.professor || "Professor pending",
        classroom: schedule?.classroom || "Room pending",
        status: schedule?.status || "enrolled",
    };
}

function statusLabel(status) {
    return String(status || "enrolled").replaceAll("_", " ").toUpperCase();
}

function statusVariant(status) {
    return {
        enrolled: "success",
        pre_enrolled: "warning",
        approved: "success",
        withdrawn: "gray",
        cancelled: "gray",
    }[status] || "gray";
}

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function renderEventContent(arg) {
    const schedule = readableSchedule(arg.event.extendedProps.raw);

    return {
        html: `
            <div class="uh-student-event-body">
                <div class="uh-student-event-time">${escapeHtml(schedule.time)}</div>
                <div class="uh-student-event-title">${escapeHtml(schedule.subject)}</div>
                <div class="uh-student-event-meta">${escapeHtml(schedule.group)} &middot; ${escapeHtml(schedule.classroom)}</div>
            </div>
        `,
    };
}
</script>

<template>
    <div class="space-y-5">
        <EmptyState
            v-if="!schedules.length"
            title="No schedule yet"
            description="Selected subjects with published schedules will appear here as your enrollment takes shape."
            icon="fa-solid fa-calendar-week"
        />

        <template v-else>
            <FullCalendar class="uh-student-schedule-calendar" :options="calendarOptions" />

            <div v-if="selectedSchedule" class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ selectedSchedule.subject }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ selectedSchedule.day }} &middot; {{ selectedSchedule.time }}
                        </p>
                    </div>

                    <StatusBadge :label="statusLabel(selectedSchedule.status)" :variant="statusVariant(selectedSchedule.status)" />
                </div>

                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-400">Group</dt>
                        <dd class="mt-1 text-gray-700 dark:text-gray-200">{{ selectedSchedule.group }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-400">Professor</dt>
                        <dd class="mt-1 text-gray-700 dark:text-gray-200">{{ selectedSchedule.professor }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-gray-400">Classroom</dt>
                        <dd class="mt-1 text-gray-700 dark:text-gray-200">{{ selectedSchedule.classroom }}</dd>
                    </div>
                </dl>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="group in groupedSchedules"
                    :key="group.day"
                    class="rounded-lg border border-gray-200 p-4 dark:border-gray-800"
                >
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ formatDay(group.day) }}
                    </h3>

                    <div class="mt-3 space-y-3">
                        <div
                            v-for="schedule in group.schedules"
                            :key="`${schedule.id}-${schedule.start_time}`"
                            class="rounded-md bg-gray-50 p-3 text-sm dark:bg-gray-800"
                        >
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ readableSchedule(schedule).subject }}
                            </p>
                            <p class="mt-1 text-gray-600 dark:text-gray-300">
                                {{ readableSchedule(schedule).time }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ readableSchedule(schedule).group }} &middot; {{ readableSchedule(schedule).classroom }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<style>
.uh-student-schedule-calendar {
    --fc-border-color: rgb(229 231 235);
    --fc-page-bg-color: transparent;
    --fc-neutral-bg-color: rgb(249 250 251);
    --fc-today-bg-color: rgb(238 242 255);
    --fc-event-bg-color: rgb(37 99 235);
    --fc-event-border-color: rgb(29 78 216);
    --fc-event-text-color: white;
    color: rgb(17 24 39);
}

.dark .uh-student-schedule-calendar {
    --fc-border-color: rgb(31 41 55);
    --fc-neutral-bg-color: rgb(17 24 39);
    --fc-today-bg-color: rgb(30 41 59);
    color: rgb(243 244 246);
}

.uh-student-schedule-calendar .fc-scrollgrid {
    border-radius: 0.75rem;
    overflow: hidden;
}

.uh-student-schedule-calendar .fc-col-header-cell {
    padding: 0.5rem 0;
}

.uh-student-schedule-calendar .fc-timegrid-slot {
    height: 3rem;
}

.uh-student-schedule-calendar .fc-timegrid-event {
    border-radius: 0.5rem;
    box-shadow: 0 10px 24px rgb(37 99 235 / 16%);
    overflow: hidden;
}

.uh-student-schedule-calendar .uh-student-schedule-event {
    background: linear-gradient(135deg, rgb(14 116 144), rgb(37 99 235));
    border: 1px solid rgb(14 116 144);
    border-left: 4px solid rgb(165 243 252);
}

.uh-student-event-body {
    display: flex;
    min-height: 100%;
    flex-direction: column;
    gap: 0.15rem;
    padding: 0.35rem 0.45rem;
    line-height: 1.2;
}

.uh-student-event-time {
    font-size: 0.68rem;
    font-weight: 700;
    opacity: 0.95;
}

.uh-student-event-title {
    font-size: 0.78rem;
    font-weight: 800;
    white-space: normal;
}

.uh-student-event-meta {
    font-size: 0.68rem;
    opacity: 0.92;
    white-space: normal;
}
</style>
