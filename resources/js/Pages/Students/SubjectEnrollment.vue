<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useAlert } from '@/Components/Composables/useAlert'
import axios from 'axios'

const props = defineProps({
    subjects: Array,
    enrollmentDeadline: String,
    unenrollmentDeadline: String,
    currentSchedules: Array
})

const enrolling = ref(null)

const { toastSuccess, toastError } = useAlert();

const toggleEnrollment = async (subject) => {
    enrolling.value = subject.id

    const wasEnrolled = subject.alreadyEnrolled

    try {
        if (wasEnrolled) {
            const response = await axios.post(route('student.subject-enrollment.unenroll', subject.id))
            subject.alreadyEnrolled = false
            toastSuccess(response.data.message)
        } else {
            // Aquí deberías tener subject.schedules dentro de cada asignatura
            if (hasScheduleConflict(subject.schedules)) {
                toastError('This subject has a schedule conflict with your current enrollments.')
                return
            }

            const response = await axios.post(route('student.subject-enrollment.enroll', subject.id))
            subject.alreadyEnrolled = true
            subject.status = response.data.status?.label
            subject.statusColor = response.data.status?.color
            toastSuccess(response.data.message)
        }
    } catch (error) {
        const msg = error.response?.data?.error || 'An unexpected error occurred'
        toastError(msg)
    } finally {
        enrolling.value = null
    }
}


const hasScheduleConflict = (subjectSchedule) => {
    return props.currentSchedules.some(current => {
        return subjectSchedule.some(sched => {
            return sched.day === current.day &&
                sched.start_time < current.end_time &&
                sched.end_time > current.start_time
        });
    });
}

const selectedSubject = ref(null)
const showModal = ref(false)

const selectGroup = (subject) => {
    console.log('Selected subject groups:', subject.groups)

    selectedSubject.value = subject
    showModal.value = true
}

const closeModal = () => {
    selectedSubject.value = null
    showModal.value = false
}

const enrollInGroup = async (groupId) => {
    const subject = selectedSubject.value
    const selectedGroup = subject.groups.find(g => g.id === groupId)

    // Validar traslape
    if (hasScheduleConflict(selectedGroup.schedules)) {
        toastError('Schedule conflict with another subject.')
        return
    }

    enrolling.value = subject.id

    try {
        const response = await axios.post(route('student.subject-enrollment.enroll', subject.id), {
            class_group_id: groupId
        })

        subject.alreadyEnrolled = true
        subject.status = response.data.status.label
        subject.statusColor = response.data.status.color

        // Agregar horarios nuevos al currentSchedules global
        selectedGroup.schedules.forEach(schedule => props.currentSchedules.push(schedule))

        toastSuccess(response.data.message)
        closeModal()
    } catch (error) {
        toastError(error.response?.data?.error || 'Error during enrollment.')
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

        <div class="max-w-5xl mx-auto p-6">

            <!-- Alerta para fecha límite de inscripción -->
            <transition name="fade" @before-leave="beforeLeave" @leave="leave">
                <div v-if="enrollmentDeadline"
                    class="max-w-5xl w-full mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M8 3V2a1 1 0 112 0v1h8a1 1 0 011 1v12a1 1 0 01-1 1H2a1 1 0 01-1-1V4a1 1 0 011-1h6z"
                                clip-rule="evenodd" />
                        </svg>
                        <strong>Important:</strong> You can enroll in subjects until&nbsp;
                        <span class="font-semibold">{{ new Date(enrollmentDeadline).toLocaleDateString() }}</span>.
                    </div>
                    <button @click="closeEnrollmentAlert"
                        class="text-yellow-800 hover:text-yellow-600 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            </transition>

            <!-- Alerta para fecha límite de desinscripción -->
            <transition name="fade" @before-leave="beforeLeave" @leave="leave">
                <div v-if="unenrollmentDeadline"
                    class="max-w-5xl w-full mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm0 2a10 10 0 1110-10A10 10 0 0110 20z"
                                clip-rule="evenodd" />
                        </svg>
                        <strong>Important:</strong> You can unenroll from subjects until&nbsp;
                        <span class="font-semibold">{{ new Date(unenrollmentDeadline).toLocaleDateString() }}</span>.
                    </div>
                    <button @click="closeUnenrollmentAlert" class="text-red-800 hover:text-red-600 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            </transition>

            <!-- Tabla de asignaturas -->
            <div class="overflow-x-auto shadow border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="min-w-full bg-white dark:bg-gray-900 text-sm text-left text-gray-800 dark:text-gray-100">
                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="p-3">Subject Name</th>
                            <th class="p-3">Credits</th>
                            <th class="p-3">Semester</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="subject in subjects" :key="subject.id"
                            class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="p-3 font-medium">{{ subject.name }}</td>
                            <td class="p-3">{{ subject.credits }}</td>
                            <td class="p-3">{{ subject.semester }}</td>
                            <!-- Status -->
                            <td class="p-3">
                                <div v-if="subject.alreadyEnrolled" class="inline-flex items-center gap-2">
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="{
                                        'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-300': subject.statusColor === 'blue',
                                        'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-300': subject.statusColor === 'green',
                                        'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-300': subject.statusColor === 'red',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-800/20 dark:text-gray-300': subject.statusColor === 'gray',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-800/20 dark:text-purple-300': subject.statusColor === 'purple',
                                    }">
                                        {{ subject.status }}
                                    </span>
                                </div>

                                <span v-else-if="!subject.hasAllPrerequisites"
                                    class="text-red-600 dark:text-red-400 font-semibold">
                                    Missing Prerequisites
                                </span>

                                <span v-else class="text-green-600 dark:text-green-400 font-semibold">
                                    Available
                                </span>
                            </td>

                            <td class="p-3 text-center">
                                <button v-if="subject.hasAllPrerequisites && !subject.alreadyEnrolled"
                                    @click="selectGroup(subject)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md">
                                    Select Group
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
    </AppLayout>

    <template v-if="showModal && selectedSubject">
        <div class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-lg z-50">
                <h2 class="text-lg font-semibold mb-4">Select a group for {{ selectedSubject.name }}</h2>
                <div class="space-y-4">
                    <div v-for="group in selectedSubject.groups" :key="group.id"
                        class="border p-3 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer"
                        @click="enrollInGroup(group.id)">
                        <p class="font-medium">Group: {{ group.code }} ({{ group.name }})</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Professor: {{ group.professor }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Capacity: {{ group.enrolled }}/{{
                            group.capacity }}</p>
                        <div class="text-xs mt-2">
                            <p v-for="schedule in group.schedules" :key="schedule.day + schedule.start_time">
                                {{ schedule.day }}: {{ schedule.start_time }} - {{ schedule.end_time }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-right">
                    <button @click="closeModal"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded">Cancel</button>
                </div>
            </div>
        </div>
    </template>

</template>


<style scoped>
/* Transición de desaparición */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease-in-out;
}

.fade-enter,
.fade-leave-to

/* .fade-leave-active en <2.1.8 */
    {
    opacity: 0;
}
</style>

<script>
export default {
    data() {
        return {
            enrollmentDeadline: '2025-08-01',  // Ejemplo de fecha
            unenrollmentDeadline: '2025-08-15',  // Ejemplo de fecha
        };
    },
    methods: {
        closeEnrollmentAlert() {
            this.enrollmentDeadline = null;
        },
        closeUnenrollmentAlert() {
            this.unenrollmentDeadline = null;
        },
        beforeLeave(el) {
            // Si deseas hacer algo antes de que la alerta desaparezca, como añadir clases
        },
        leave(el, done) {
            // Aquí podrías agregar un retraso antes de que el elemento desaparezca completamente.
            setTimeout(() => {
                done();
            }, 500); // Espera 500ms (el mismo tiempo de la transición)
        },
    },
};</script>