<script setup>
import { ref, computed, onMounted } from 'vue'
import { Inertia } from '@inertiajs/inertia'
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
    currentSchedules: Array,
    systemState: String
})


const currentSchedules = ref([...props.currentSchedules])
const enrolling = ref(null)
const selectedSubject = ref(null)
const showModal = ref(false)
const loadingGroups = ref(false)
const groupsError = ref(null)
const enrollmentAlertVisible = ref(true)
const unenrollmentAlertVisible = ref(true)

const canStillChangeGroup = computed(() => {
    if (!props.enrollmentDeadline) return true
    return dayjs().isBefore(dayjs(props.enrollmentDeadline).endOf('day'))
})

const { toastSuccess, toastError } = useAlert()

onMounted(() => {
    const msg = localStorage.getItem('enrollFlash')
    if (msg) {
        toastSuccess(msg)
        localStorage.removeItem('enrollFlash')
    }
})

const formatDate = (date) =>
    dayjs(date).tz('America/Bogota').format('DD/MM/YYYY')

const hasScheduleConflict = (schedules, subjectId = null) =>
    currentSchedules.value.some((current) =>
        schedules.some(
            (sched) =>
                sched.day === current.day &&
                sched.start_time < current.end_time &&
                sched.end_time > current.start_time &&
                current.subject_id !== subjectId
        )
    )
// 1️⃣ Al abrir siempre recargamos los grupos
const selectGroup = async (subject) => {
    selectedSubject.value = {
        ...subject,
        groups: []
    }

    groupsError.value = null
    showModal.value = true

    await refreshGroups(selectedSubject.value)
}

const closeModal = () => {
    selectedSubject.value = null
    showModal.value = false
}

const enrollInGroup = async (groupId) => {
    const subject = selectedSubject.value
    const group = subject.groups.find((g) => g.id === groupId)

    if (hasScheduleConflict(group.schedules, subject.id)) {
        toastError('Schedule conflict with another subject.')
        return
    }

    enrolling.value = subject.id

    try {
        const { data } = await axios.post(
            route('student.subject-enrollment.enroll', { classGroup: groupId })
        )

        toastSuccess(data.message)
        localStorage.setItem('enrollFlash', data.message)
        window.location.href = route('student.subject-enrollment.index')

    } catch (e) {
        toastError(e.response?.data?.error || 'Enrollment error')
        console.log(e.response?.data?.error);
    } finally {
        enrolling.value = null
    }
}

// Refresca los grupos del subject, marcando el current
const refreshGroups = async (subject) => {
    loadingGroups.value = true
    groupsError.value = null

    try {
        const { data } = await axios.get(
            route('student.subject-enrollment.groups', subject.id)
        )

        subject.groups = data.groups || []

        const current = subject.groups.find((g) => g.isCurrent)

        subject.currentGroupId = current?.id || null

    } catch (error) {

        groupsError.value =
            error.response?.data?.error ||
            'Error fetching groups.'

        console.error(error)

    } finally {
        loadingGroups.value = false
    }
}

const unenrollFromSubject = async (subject) => {
    if (!subject.enrollmentId) {
        toastError('Invalid enrollment.')
        return
    }

    enrolling.value = subject.id

    try {
        await axios.delete(
            route('student.subject-enrollment.unenroll', { enrollment: subject.enrollmentId })
        )

        toastSuccess('Unenrollment successful.')
        localStorage.setItem('enrollFlash', 'Unenrollment successful.')
        window.location.href = route('student.subject-enrollment.index')

    } catch (e) {
        toastError(e.response?.data?.error || 'Unenrollment failed')
    } finally {
        enrolling.value = null
    }
}

const getBlockReason = (subject) => {
    if (subject.audit.status === 'blocked') {
        return 'Missing prerequisites'
    }

    if (subject.status === 'enrolled') {
        return 'Already enrolled'
    }

    // opcional: si luego agregas reason desde backend
    if (subject.blockReason) {
        return subject.blockReason
    }

    return 'Not eligible'
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
            <div v-if="systemState === 'no_curriculum'"
                class="p-6 bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500 rounded">
                <strong>Academic setup pending.</strong><br>
                Your program does not have an active curriculum yet.
                Please contact academic administration.
            </div>

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
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="{

                                    // Enrollment statuses
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-300':
                                        subject.status === 'pre_enrolled',

                                    'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-300':
                                        subject.status === 'enrolled',

                                    'bg-neutral-800 text-white':
                                        subject.status === 'cancelled',

                                    'bg-gray-100 text-gray-800 dark:bg-gray-800/20 dark:text-gray-300':
                                        subject.status === 'withdrawn',

                                    'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-300':
                                        subject.status === 'failed',

                                    'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-300':
                                        subject.status === 'approved',

                                    'bg-purple-100 text-purple-800 dark:bg-purple-800/20 dark:text-purple-300':
                                        subject.status === 'revalidation',

                                    // Academic audit fallback
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-800/20 dark:text-emerald-300':
                                        !subject.status && subject.audit.status === 'available',

                                    'bg-cyan-100 text-cyan-800 dark:bg-cyan-800/20 dark:text-cyan-300':
                                        !subject.status && subject.audit.status === 'in_progress',

                                    'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-300':
                                        !subject.status && subject.audit.status === 'blocked',

                                    'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-300':
                                        !subject.status && subject.audit.status === 'approved',

                                    'bg-orange-100 text-orange-800 dark:bg-orange-800/20 dark:text-orange-300':
                                        !subject.status && subject.audit.status === 'available_but_repeating',
                                }">
                                    {{

                                        // Enrollment labels
                                        {
                                            pre_enrolled: 'Pre-Enrolled',
                                            enrolled: 'Enrolled',
                                            cancelled: 'Cancelled',
                                            withdrawn: 'Withdrawn',
                                            failed: 'Failed',
                                            approved: 'Approved',
                                            revalidation: 'Revalidation'
                                        }[subject.status]

                                        ||

                                        // Academic labels fallback
                                        {
                                            approved: 'Approved',
                                            in_progress: 'In Progress',
                                            available: 'Available',
                                            blocked: 'Blocked',
                                            available_but_repeating: 'Repeating Available'
                                        }[subject.audit.status]

                                    }}
                                </span>

                                <!-- Block reason -->
                                <div v-if="subject.audit.status === 'blocked'"
                                    class="mt-1 text-xs text-red-600 dark:text-red-400">
                                    Requires:
                                    <ul class="list-disc ml-4">
                                        <li v-for="name in subject.audit.blockedBy" :key="name">
                                            {{ name }}
                                        </li>
                                    </ul>
                                </div>


                                <div v-if="subject.alreadyEnrolled && subject.schedules?.length"
                                    class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    <p v-for="(s, idx) in subject.schedules" :key="idx">
                                        {{ s.day }}: {{ s.start_time }} - {{ s.end_time }}
                                    </p>
                                </div>

                            </td>
                            <td class="p-3 text-center">

                                <!-- ✅ BOTÓN -->
                                <button v-if="subject.canEnroll && canStillChangeGroup" @click="selectGroup(subject)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md">
                                    {{ subject.enrollmentId ? 'Change Group' : 'Select Group' }}
                                </button>

                                <!-- ❌ MENSAJES CUANDO NO SE PUEDE -->
                                <div v-else class="text-xs text-gray-500 dark:text-gray-400">

                                    <!-- 🚫 Fuera de fecha -->
                                    <span v-if="!canStillChangeGroup">
                                        Enrollment closed
                                    </span>

                                    <!-- 🚫 No cumple reglas académicas -->
                                    <span v-else-if="!subject.canEnroll">
                                        {{ getBlockReason(subject) }}
                                    </span>

                                    <!-- fallback -->
                                    <span v-else>
                                        Not available
                                    </span>

                                </div>

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
                    {{ selectedSubject.enrollmentId ? 'Change group for' : 'Select a group for' }} {{
                        selectedSubject.name }}
                </h2>

                <!-- Botón para desinscribirse del grupo actual -->
                <div v-if="selectedSubject.enrollmentId && selectedSubject.currentGroupId" class="mb-4">
                    <button @click="unenrollFromSubject(selectedSubject)"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm w-full">
                        Withdraw from current group
                    </button>
                </div>

                <!-- Listado de grupos -->
                <!-- Loading -->
                <div v-if="loadingGroups" class="py-10 text-center text-gray-500">
                    Loading groups...
                </div>

                <!-- Error -->
                <div v-else-if="groupsError" class="p-4 rounded bg-red-100 text-red-700 border border-red-300">
                    {{ groupsError }}
                </div>

                <!-- No groups -->
                <div v-else-if="!selectedSubject.groups?.length"
                    class="p-4 rounded bg-yellow-100 text-yellow-800 border border-yellow-300">

                    No groups available for this subject.

                    <div class="mt-2 text-sm text-yellow-700">
                        This subject may not have schedules assigned yet.
                    </div>
                </div>

                <!-- Groups -->
                <div v-else class="space-y-4 max-h-96 overflow-y-auto pr-2">

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

                                {{ schedule.day }}:
                                {{ schedule.start_time }}
                                -
                                {{ schedule.end_time }}
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