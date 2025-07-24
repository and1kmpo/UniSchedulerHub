<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useAlert } from '@/Components/Composables/useAlert'
import axios from 'axios'

defineProps({ subjects: Array })

const enrolling = ref(null)

const { toastSuccess, toastError } = useAlert();

const toggleEnrollment = async (subject) => {
    enrolling.value = subject.id

    const wasEnrolled = subject.alreadyEnrolled

    try {
        if (wasEnrolled) {
            const response = await axios.post(route('student.subject-enrollment.unenroll', subject.id))
            subject.alreadyEnrolled = false // ← cambio reactivo inmediato
            toastSuccess(response.data.message)
        } else {
            const response = await axios.post(route('student.subject-enrollment.enroll', subject.id))
            subject.alreadyEnrolled = true // ← cambio reactivo inmediato
            toastSuccess(response.data.message)
        }
    } catch (error) {
        const msg = error.response?.data?.error || 'An unexpected error occurred'
        toastError(msg)
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
                            <td class="p-3">
                                <span v-if="subject.alreadyEnrolled"
                                    class="text-blue-600 dark:text-blue-400 font-semibold">
                                    Enrolled / Completed
                                </span>
                                <span v-else-if="!subject.hasAllPrerequisites"
                                    class="text-red-600 dark:text-red-400 font-semibold">
                                    Missing Prerequisites
                                </span>
                                <span v-else class="text-green-600 dark:text-green-400 font-semibold">
                                    Available
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <button v-if="subject.hasAllPrerequisites" @click="toggleEnrollment(subject)"
                                    :disabled="enrolling === subject.id"
                                    class="px-4 py-1.5 rounded-md transition text-white focus:outline-none focus:ring-2 disabled:opacity-50"
                                    :class="{
                                        'bg-green-600 hover:bg-green-700 focus:ring-green-300': !subject.alreadyEnrolled,
                                        'bg-red-600 hover:bg-red-700 focus:ring-red-300': subject.alreadyEnrolled
                                    }">
                                    <span v-if="enrolling === subject.id">
                                        {{ subject.alreadyEnrolled ? 'Unenrolling...' : 'Enrolling...' }}
                                    </span>
                                    <span v-else>
                                        {{ subject.alreadyEnrolled ? 'Unenroll' : 'Enroll' }}
                                    </span>
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
</template>
