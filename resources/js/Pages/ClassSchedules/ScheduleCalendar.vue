<script setup>
import { ref } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import ScheduleModal from './ScheduleModal.vue'
import dayjs from 'dayjs'
import tippy from 'tippy.js'
import 'tippy.js/dist/tippy.css'
import 'tippy.js/animations/scale-extreme.css'
import { useAlert } from '@/Components/Composables/useAlert'
import axios from 'axios'
import { route } from 'ziggy-js'

const props = defineProps({
    classGroup: Object,
    schedules: Array,
    classrooms: Array,
    editable: {
        type: Boolean,
        default: false
    }
})

const { toastSuccess, toastError, confirm } = useAlert()

const showModal = ref(false)
const modalInitial = ref(null)
const modalSchedule = ref(null)
const modalMode = ref('create')
const calendarRef = ref(null)

// Helper array to convert index to day name
const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']

/**
 * Persist event change (drag or resize)
 * - info: object provided by FullCalendar (contains event and revert())
 */
async function persistEventChange(info) {
    const ev = info.event
    const scheduleId = ev.id

    const start = ev.start
    const end = ev.end

    if (!start || !end) {
        toastError('Invalid dates for this change.')
        return info.revert()
    }

    const day = dayNames[start.getDay()]
    const start_time = dayjs(start).format('HH:mm')
    const end_time = dayjs(end).format('HH:mm')

    // Use classroom_id from extendedProps or scheduleData
    const classroom_id = ev.extendedProps?.classroom_id ?? ev.extendedProps?.scheduleData?.classroom_id ?? null

    const payload = {
        day,
        start_time,
        end_time,
        classroom_id
    }

    // Confirm using useAlert.confirm
    const confirmed = await confirm(
        `Update schedule to <strong>${day}</strong> from <strong>${start_time}</strong> to <strong>${end_time}</strong>?`,
        'Confirm change', true
    )

    if (!confirmed) {
        return info.revert()
    }

    try {
        await axios.put(route('class-schedules.update', [props.classGroup.id, scheduleId]), payload)
        try { calendarRef.value.getApi().refetchEvents() } catch (e) { /* ignore */ }
        toastSuccess('Schedule updated.')
    } catch (err) {
        info.revert()
        const msg = err.response?.data?.message || err.response?.data?.errors?.schedule?.[0] || 'Failed to update schedule.'
        toastError(msg)
        console.error('Error updating schedule:', err)
    }
}

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
    allDaySlot: false,
    nowIndicator: true,
    stickyHeaderDates: true,
    expandRows: true,
    slotMinTime: '07:00:00',
    slotMaxTime: '21:00:00',
    slotDuration: '00:30:00',
    firstDay: 1,
    dayMaxEvents: true,

    // editable control
    editable: props.editable,
    selectable: props.editable,
    selectMirror: props.editable,

    // create on selection / click (only if editable)
    dateClick: props.editable ? (info) => {
        showModal.value = true
        modalInitial.value = {
            day: ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][info.date.getDay()],
            start_time: info.dateStr.slice(11, 16),
            end_time: dayjs(info.date).add(1, 'hour').format('HH:mm'),
        }
        modalSchedule.value = null
        modalMode.value = 'create'
    } : null,

    // click on event → open modal (only if editable)
    eventClick: props.editable ? (clickInfo) => {
        showModal.value = true
        modalSchedule.value = clickInfo.event.extendedProps.scheduleData
        modalInitial.value = null
        modalMode.value = 'edit'
    } : null,

    // drag and resize handlers
    eventDrop: props.editable ? (info) => { persistEventChange(info) } : null,
    eventResize: props.editable ? (info) => { persistEventChange(info) } : null,

    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },

    events: (fetchInfo, successCallback, failureCallback) => {
        axios.get(route('class-schedules.json', props.classGroup.id))
            .then((res) => {
                const events = res.data.map((s) => {
                    const dayIndex = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']
                        .indexOf(s.day)
                    return {
                        id: s.id,
                        title: '📚 ' + (props.classGroup?.subject?.name ?? 'Subject'),
                        startTime: s.start_time,
                        endTime: s.end_time,
                        daysOfWeek: [dayIndex],
                        startRecur: '2025-01-01',
                        endRecur: '2025-12-31',
                        color: props.classGroup?.code?.endsWith?.('G1') ? '#4F46E5' : '#059669',
                        borderColor: props.classGroup?.code?.endsWith?.('G1') ? '#4338ca' : '#047857',
                        textColor: '#fff',
                        extendedProps: {
                            classroom: s.classroom?.name ?? 'No assigned',
                            professorName: s.class_group?.professor?.name ?? props.classGroup?.professor?.name ?? 'N/A',
                            scheduleData: s,
                            classroom_id: s.classroom_id ?? null
                        }
                    }
                })
                successCallback(events)
            })
            .catch(() => failureCallback())
    },

    eventDidMount: function (info) {
        const classroom = info.event.extendedProps.classroom ?? 'No assigned'
        const professorName = info.event.extendedProps.professorName ?? 'N/A'
        tippy(info.el, {
            content: `<strong>Room:</strong> ${classroom}<br><strong>Professor:</strong> ${professorName}`,
            allowHTML: true,
            placement: 'top',
            arrow: true,
            theme: 'light-border',
            animation: 'scale-extreme'
        })
        info.el.classList.add('custom-fc-event')
    }
})

function onModalSaved() {
    showModal.value = false
    try { calendarRef.value.getApi().refetchEvents() } catch (e) { }
    toastSuccess('Schedule saved successfully.')
}

function onModalDeleted() {
    showModal.value = false
    try { calendarRef.value.getApi().refetchEvents() } catch (e) { }
    toastSuccess('Schedule deleted successfully.')
}

const handleClose = () => {
    showModal.value = false
}
</script>



<template>
    <div class="rounded-lg shadow-lg bg-white dark:bg-gray-800 overflow-hidden p-4">
        <FullCalendar ref="calendarRef" :options="calendarOptions" />
        <ScheduleModal :show="showModal" :schedule="modalSchedule" :initial="modalInitial"
            :classGroupId="props.classGroup.id" :classrooms="props.classrooms" :mode="modalMode" @close="handleClose"
            @saved="onModalSaved" @deleted="onModalDeleted" />
    </div>
</template>
