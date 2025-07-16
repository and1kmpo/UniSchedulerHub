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


// Props
const props = defineProps({
    classGroup: Object,
    schedules: Array
})

// Estado
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
                classroom: s.classroom,
                teacher: s.teacher || 'N/A', // ejemplo adicional
                scheduleData: s,
            }
        }
    })
})


// Opciones de calendario

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
    events: eventSources,
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

        // Añade clase personalizada para más estilo (ver paso 3)
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
        start_time: formData.start_time,
        end_time: formData.end_time,
        classroom: formData.classroom,
    }

    if (modalMode.value === 'create') {
        router.post(route('class-schedules.store', [props.classGroup.id]), payload, {
            onSuccess: () => {
                showModal.value = false
                calendarRef.value.getApi().refetchEvents()

                Swal.fire({
                    icon: 'success',
                    title: 'Schedule created',
                    text: 'The schedule was created successfully.',
                    timer: 2500,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    position: 'top-end',
                    toast: true,
                    background: '#f0f9ff',
                    color: '#2c7be5',
                    iconColor: '#2c7be5',
                    customClass: {
                        popup: 'shadow-lg rounded-lg'
                    }
                })
            },
            onError: (errors) => {
                console.error(errors)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not create the schedule. Please check the data.',
                    timer: 3500,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    position: 'top-end',
                    toast: true,
                    background: '#fdecea',
                    color: '#d93025',
                    iconColor: '#d93025',
                    customClass: {
                        popup: 'shadow-lg rounded-lg'
                    }
                })
            }
        })
    } else if (modalMode.value === 'edit') {
        router.put(route('class-schedules.update', [props.classGroup.id, modalSchedule.value.id]), payload, {
            onSuccess: () => {
                showModal.value = false
                calendarRef.value.getApi().refetchEvents()

                Swal.fire({
                    icon: 'success',
                    title: 'Schedule updated',
                    text: 'The schedule was updated successfully.',
                    timer: 2500,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    position: 'top-end',
                    toast: true,
                    background: '#f0f9ff',
                    color: '#2c7be5',
                    iconColor: '#2c7be5',
                    customClass: {
                        popup: 'shadow-lg rounded-lg'
                    }
                })
            },
            onError: (errors) => {
                console.error(errors)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not update the schedule. Please check the data.',
                    timer: 3500,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    position: 'top-end',
                    toast: true,
                    background: '#fdecea',
                    color: '#d93025',
                    iconColor: '#d93025',
                    customClass: {
                        popup: 'shadow-lg rounded-lg'
                    }
                })
            }
        })
    }
}



const handleDelete = () => {
    Swal.fire({
        title: 'Delete schedule?',
        text: 'Are you sure you want to delete this schedule? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        focusCancel: true,
        showLoaderOnConfirm: true,
        preConfirm: () => {
            // Return the promise so SweetAlert shows loader until the delete is done
            return router.delete(route('class-schedules.destroy', [props.classGroup.id, modalSchedule.value.id]), {
                onSuccess: () => {
                    showModal.value = false
                    calendarRef.value.getApi().refetchEvents()
                    Swal.fire({
                        title: 'Deleted',
                        text: 'The schedule was deleted successfully.',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        position: 'top-end',
                        toast: true
                    })
                },
                onError: () => {
                    Swal.fire({
                        title: 'Error',
                        text: 'The schedule could not be deleted.',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        position: 'top-end',
                        toast: true
                    })
                }
            })
        }
    })
}


</script>

<template>
    <div class="rounded-lg shadow-lg bg-white dark:bg-gray-800 overflow-hidden p-4">
        <FullCalendar ref="calendarRef" :options="calendarOptions" />
        <ScheduleModal :show="showModal" :schedule="modalSchedule" :initial="modalInitial"
            :classGroupId="props.classGroup.id" :mode="modalMode" :onClose="handleClose" :onSubmit="handleSubmit"
            @delete="handleDelete" />
    </div>
</template>
