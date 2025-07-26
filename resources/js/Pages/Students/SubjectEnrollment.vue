<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useAlert } from '@/Components/Composables/useAlert'
import axios from 'axios'
import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'

dayjs.extend(utc)
dayjs.extend(timezone)

const props = defineProps({
    subjects: Array,
    enrollmentDeadline: String,
    unenrollmentDeadline: String,
    currentSchedules: Array
})

const currentSchedules = ref([...props.currentSchedules])

const enrolling = ref(null)
const selectedSubject = ref(null)
const showModal = ref(false)
const enrollmentAlertVisible = ref(true)
const unenrollmentAlertVisible = ref(true)

const canStillChangeGroup = computed(() => {
    if (!props.enrollmentDeadline) return true
    return dayjs().isBefore(dayjs(props.enrollmentDeadline).endOf('day'))
})

const { toastSuccess, toastError } = useAlert()

const formatDate = (date) => {
    return dayjs(date).tz('America/Bogota').format('DD/MM/YYYY')
}

const hasScheduleConflict = (schedules) => {
    return currentSchedules.value.some(current =>
        schedules.some(sched =>
            sched.day === current.day &&
            sched.start_time < current.end_time &&
            sched.end_time > current.start_time
        )
    )
}


const selectGroup = (subject) => {
    selectedSubject.value = subject
    showModal.value = true
}

const closeModal = () => {
    selectedSubject.value = null
    showModal.value = false
}


const enrollInGroup = async (groupId) => {
    const subject = selectedSubject.value
    const group = subject.groups.find(g => g.id === groupId)

    if (hasScheduleConflict(group.schedules)) {
        toastError('Schedule conflict with another subject.')
        return
    }

    enrolling.value = subject.id

    try {
        // 🔸 Eliminar horarios anteriores antes de inscribir
        currentSchedules.value = currentSchedules.value.filter(
            sched => !subject.schedules.some(s => s.day === sched.day && s.start_time === sched.start_time)
        )

        const { data } = await axios.post(route('student.subject-enrollment.enroll', subject.id), {
            class_group_id: groupId
        })

        // 🔸 Actualizar estado del subject
        Object.assign(subject, {
            alreadyEnrolled: true,
            status: data.status.code,
            statusColor: data.status.color,
            schedules: [...group.schedules],
            currentGroupId: group.id
        })

        // 🔸 Agregar nuevos horarios
        group.schedules.forEach(schedule => currentSchedules.value.push(schedule))

        // 🔸 Marcar grupo actual
        subject.groups.forEach(g => {
            g.isCurrent = g.id === subject.currentGroupId
        })

        toastSuccess(data.message)
        closeModal()

        // 🔸 Refrescar grupos si es necesario
        await refreshGroups(subject)

    } catch (e) {
        toastError(e.response?.data?.error || 'Enrollment error')
    } finally {
        enrolling.value = null
    }
}


const refreshGroups = async (subject) => {
    try {
        const { data } = await axios.get(route('student.subject-enrollment.groups', subject.id))
        subject.groups = data.groups

        subject.groups.forEach(group => {
            group.isCurrent = group.id === subject.currentGroupId
        })
    } catch (e) {
        toastError('Could not refresh groups')
    }
}

const unenrollFromSubject = async (subject) => {
    try {
        enrolling.value = subject.id

        // 🔸 Guardar los horarios actuales antes de limpiar
        const oldSchedules = [...subject.schedules]

        const { data } = await axios.post(route('student.subject-enrollment.unenroll', subject.id))

        // 🔸 Actualizar estado local del subject
        Object.assign(subject, {
            alreadyEnrolled: false,
            status: null,
            statusColor: null,
            currentGroupId: null,
            schedules: []
        })

        // 🔸 Limpiar horarios del currentSchedules reactivo
        currentSchedules.value = currentSchedules.value.filter(
            sched => !oldSchedules.some(s => s.day === sched.day && s.start_time === sched.start_time)
        )

        toastSuccess(data.message)
        closeModal()
    } catch (e) {
        toastError(e.response?.data?.error || 'Unenrollment failed')
    } finally {
        enrolling.value = null
    }
}



</script>


<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                Subject Enrollment
            </h1>
        </template>

        <div class="max-w-5xl mx-auto p-6 space-y-6">

            <!-- Alertas de fechas -->
            <transition name="fade">
                <div v-if="enrollmentDeadline && enrollmentAlertVisible"
                    class="alert bg-yellow-100 border-yellow-500 text-yellow-800" role="alert">
                    <strong>Important:</strong> Enroll before
                    <span class="font-semibold">{{ formatDate(enrollmentDeadline) }}</span>
                    <button @click="enrollmentAlertVisible = false" class="close-btn text-yellow-800">✕</button>
                </div>
            </transition>

            <transition name="fade">
                <div v-if="unenrollmentDeadline && unenrollmentAlertVisible"
                    class="alert bg-red-100 border-red-500 text-red-800" role="alert">
                    <strong>Important:</strong> Unenroll before
                    <span class="font-semibold">{{ formatDate(unenrollmentDeadline) }}</span>
                    <button @click="unenrollmentAlertVisible = false" class="close-btn text-red-800">✕</button>
                </div>
            </transition>

            <!-- Tabla -->
            <div class="overflow-x-auto shadow border rounded-lg">
                <table class="w-full text-sm text-left text-gray-800 dark:text-gray-100 bg-white dark:bg-gray-900">
                    <thead class="bg-gray-100 dark:bg-gray-800 uppercase text-xs text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-3">Subject</th>
                            <th class="p-3">Credits</th>
                            <th class="p-3">Semester</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="subject in subjects" :key="subject.id"
                            class="border-t hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="p-3 font-medium">{{ subject.name }}</td>
                            <td class="p-3">{{ subject.credits }}</td>
                            <td class="p-3">{{ subject.semester }}</td>
                            <td class="p-3">
                                <template v-if="subject.alreadyEnrolled">
                                    <span :class="[
                                        'px-2 py-0.5 rounded-full text-xs font-medium',
                                        {
                                            'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-300': subject.statusColor === 'blue',
                                            'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-300': subject.statusColor === 'green',
                                            'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-300': subject.statusColor === 'red',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-800/20 dark:text-gray-300': subject.statusColor === 'gray',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-800/20 dark:text-purple-300': subject.statusColor === 'purple'
                                        }
                                    ]">{{ subject.status }}</span>
                                </template>
                                <span v-else-if="!subject.hasAllPrerequisites" class="text-red-600 font-semibold">
                                    Missing Prerequisites
                                </span>
                                <span v-else class="text-green-600 font-semibold">Available</span>

                                <div v-if="subject.alreadyEnrolled && subject.schedules?.length"
                                    class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    <p v-for="(s, idx) in subject.schedules" :key="idx">
                                        {{ s.day }}: {{ s.start_time }} - {{ s.end_time }}
                                    </p>
                                </div>

                            </td>
                            <td class="p-3 text-center">
                                <button v-if="subject.hasAllPrerequisites && canStillChangeGroup"
                                    @click="selectGroup(subject)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md">
                                    {{ subject.alreadyEnrolled ? 'Change Group' : 'Select Group' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="subjects.length === 0">
                            <td colspan="5" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                No subjects found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal de selección de grupo -->
        <div v-if="showModal && selectedSubject"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-lg z-50">
                <h2 class="text-lg font-semibold mb-4">
                    {{ selectedSubject.alreadyEnrolled ? 'Change group for' : 'Select a group for' }} {{
                        selectedSubject.name }}
                </h2>

                <!-- Botón para desinscribirse del grupo actual -->
                <div v-if="selectedSubject.alreadyEnrolled && selectedSubject.currentGroupId" class="mb-4">
                    <button @click="unenrollFromSubject(selectedSubject)"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm w-full">
                        Withdraw from current group
                    </button>
                </div>

                <!-- Listado de grupos -->
                <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                    <div v-for="group in selectedSubject.groups" :key="group.id"
                        class="border p-3 rounded transition cursor-pointer" :class="{
                            'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 border-blue-500': group.isCurrent,
                            'hover:bg-gray-100 dark:hover:bg-gray-700': !group.isCurrent,
                            'opacity-70 cursor-not-allowed': group.isCurrent
                        }" @click="!group.isCurrent && enrollInGroup(group.id)">
                        <div class="flex justify-between items-center">
                            <p class="font-medium">
                                Group: {{ group.code }} ({{ group.name }})
                            </p>

                            <!-- Badge de estado -->
                            <span v-if="group.isCurrent"
                                class="bg-blue-200 text-blue-800 dark:bg-blue-700/30 dark:text-blue-300 text-xs font-semibold px-2 py-0.5 rounded">
                                Enrolled
                            </span>
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Professor: {{ group.professor || 'TBD' }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Capacity: {{ group.enrolled }}/{{ group.capacity }}
                        </p>
                        <div class="text-xs mt-2 text-gray-700 dark:text-gray-300">
                            <p v-for="schedule in group.schedules" :key="schedule.day + schedule.start_time">
                                {{ schedule.day }}: {{ schedule.start_time }} - {{ schedule.end_time }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-right">
                    <button @click="closeModal" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<style scoped>
.alert {
    @apply w-full mb-4 p-4 border-l-4 rounded flex items-center justify-between;
}

.close-btn {
    @apply hover:text-opacity-70 focus:outline-none;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease-in-out;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>
