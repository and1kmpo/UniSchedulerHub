<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import ScheduleModal from './ScheduleModal.vue'
import dayjs from 'dayjs'
import tippy from 'tippy.js'
import 'tippy.js/dist/tippy.css'
import 'tippy.js/animations/scale-extreme.css';
import Swal from 'sweetalert2'
import { useAlert } from '@/Components/Composables/useAlert'
import axios from 'axios'


const props = defineProps({
    classGroup: Object,
    schedules: Array,
    classrooms: Array
})

const { toastSuccess, toastError, confirmWithPreConfirm } = useAlert()

console.log(props);

const showModal = ref(false)
const modalInitial = ref(null)
const modalSchedule = ref(null)
const modalMode = ref('create')
const calendarRef = ref(null)

// Eventos
const eventSources = computed(() => {
    return props.schedules.map((s) => {
        const subjectName = props.classGroup.subject.name
        const subjectIcons = {
            Math: '📐',
            Physics: '🔬',
            Programming: '💻',
            English: '📘',
            History: '🏰'
        }

        return {
            id: s.id,
            title: `${subjectIcons[subjectName] || '📚'} ${subjectName}`,
            daysOfWeek: [['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'].indexOf(s.day.toLowerCase()) + 1],
            startTime: s.start_time,
            endTime: s.end_time,
            startRecur: '2025-01-01',
            endRecur: '2025-12-31',
            color: props.classGroup.code.endsWith('G1') ? '#4F46E5' : '#059669',
            borderColor: props.classGroup.code.endsWith('G1') ? '#4338ca' : '#047857',
            textColor: '#fff',
            extendedProps: {
                classroom: s.classroom ? s.classroom.name : 'No assigned',
                teacher: s.teacher || 'N/A',
                scheduleData: s,
            }
        }
    })
})


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
    firstDay: 1, // Monday
    editable: false,
    selectable: true,
    selectMirror: true,
    dayMaxEvents: true,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    events: (fetchInfo, successCallback, failureCallback) => {
        axios.get(route('class-schedules.json', props.classGroup.id))
            .then((res) => {
                const events = res.data.map((s) => {
                    const dayIndex = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'].indexOf(s.day)

                    return {
                        id: s.id,
                        title: '📚 ' + props.classGroup.subject.name,
                        startTime: s.start_time,
                        endTime: s.end_time,
                        daysOfWeek: [dayIndex],
                        startRecur: '2025-01-01',
                        endRecur: '2025-12-31',
                        color: props.classGroup.code.endsWith('G1') ? '#4F46E5' : '#059669',
                        borderColor: props.classGroup.code.endsWith('G1') ? '#4338ca' : '#047857',
                        textColor: '#fff',
                        extendedProps: {
                            classroom: s.classroom?.name ?? 'No assigned',
                            teacher: s.teacher ?? 'N/A',
                            scheduleData: s,
                        }
                    }
                })

                successCallback(events)
            })
            .catch(() => {
                failureCallback()
            })
    },
    dateClick: (info) => {
        showModal.value = true;
        modalInitial.value = {
            day: ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][info.date.getDay()],
            start_time: info.dateStr.slice(11, 16),
            end_time: dayjs(info.date).add(1, 'hour').format('HH:mm'),
        };
        modalSchedule.value = null;
        modalMode.value = 'create';
    },
    eventClick: (clickInfo) => {
        showModal.value = true;
        modalSchedule.value = clickInfo.event.extendedProps.scheduleData;
        modalInitial.value = null;
        modalMode.value = 'edit';
    },
    eventDidMount: function (info) {
        const { classroom, teacher } = info.event.extendedProps
        const el = info.el

        // Tooltip enriquecido
        tippy(el, {
            content: `
      <strong>Room:</strong> ${classroom}<br>
      <strong>Teacher:</strong> ${teacher}
    `,
            allowHTML: true,
            placement: 'top',
            arrow: true,
            theme: 'light-border',
            animation: 'scale-extreme'
        })

        el.classList.add('custom-fc-event')
    }

});

// Cierre modal
const handleClose = () => {
    showModal.value = false
}

// Envío del formulario
const handleSubmit = (formData) => {
    const payload = {
        day: formData.day,
        start_time: formData.start_time.slice(0, 5),
        end_time: formData.end_time.slice(0, 5),
        classroom_id: formData.classroom_id,
    }

    if (modalMode.value === 'create') {
        router.post(route('class-schedules.store', [props.classGroup.id]), payload, {
            onSuccess: () => {
                showModal.value = false
                calendarRef.value.getApi().refetchEvents()

                toastSuccess('The schedule was created successfully.')
            },
            onError: (errors) => {
                console.error(errors)
                toastError('Could not create the schedule. Please check the data.');
            }
        })
    } else if (modalMode.value === 'edit') {
        axios.post(
            route('class-schedules.update', [props.classGroup.id, modalSchedule.value.id]),
            {
                ...payload,
                _method: 'PUT'
            }
        ).then(() => {
            showModal.value = false
            calendarRef.value.getApi().refetchEvents()
            toastSuccess('The schedule was updated successfully.');
        }).catch((error) => {
            console.error(error)
            toastError('Could not update the schedule. Please check the data.');
        })
    }
}

async function handleDelete() {
    try {
        const result = await confirmWithPreConfirm('Delete schedule?', 'Are you sure you want to delete this schedule? This action cannot be undone.', () => {
            return axios.post(route('class-schedules.destroy', [props.classGroup.id, modalSchedule.value.id]), {
                _method: 'DELETE'
            })
        }
        )

        if (result.isConfirmed) {
            showModal.value = false
            calendarRef.value.getApi().refetchEvents()
            toastSuccess('The schedule was deleted successfully.')
        }
    } catch (error) {
        toastError('The schedule could not be deleted.')
    }
}


</script>

<template>
    <div class="rounded-lg shadow-lg bg-white dark:bg-gray-800 overflow-hidden p-4">
        <FullCalendar ref="calendarRef" :options="calendarOptions" />
        <ScheduleModal :show="showModal" :schedule="modalSchedule" :initial="modalInitial"
            :classGroupId="props.classGroup.id" :classrooms="props.classrooms" :mode="modalMode" :onClose="handleClose"
            :onSubmit="handleSubmit" @delete="handleDelete" />

    </div>
</template>