<script setup>
import { ref, computed, watch } from 'vue'
import { useAlert } from '@/Components/Composables/useAlert'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'


const { toastSuccess, toastError, confirm } = useAlert()

const props = defineProps({
    classGroup: Object,
    allStudents: Array,
    enrolledIds: Array
})

const enrolling = ref(false)
const selectedStudentId = ref(null)
const canEnrollResult = ref(null)
const checking = ref(false)

// reactivo de inscritos
const enrolled = ref([...props.classGroup.students])
const enrolledIds = ref([...props.enrolledIds])

// lista de disponibles = todos menos los ya inscritos
const available = computed(() =>
    props.allStudents.filter(s =>
        !enrolled.value.find(e => e.id === s.id)
    )
)

// Inscribir un estudiante
const enrollStudent = async () => {
    if (!selectedStudentId.value) return
    enrolling.value = true
    try {
        const { data } = await axios.post(
            route('class-groups.enroll', props.classGroup.id),
            { student_id: selectedStudentId.value }
        )
        // actualizar lista local
        const stu = props.allStudents.find(s => s.id === selectedStudentId.value)
        enrolled.value.push(stu)
        enrolledIds.value.push(stu.id)
        toastSuccess(data.message || 'Student enrolled')
        selectedStudentId.value = null
    } catch (e) {
        toastError(e.response?.data?.message || 'Enrollment failed')
    } finally {
        enrolling.value = false
    }
}

// Eliminar inscripción
const removeEnrollment = async (studentId) => {
    const ok = await confirm(
        'Are you sure you want to remove this student?',
        'Confirm removal'
    )
    if (!ok) return

    try {
        await axios.delete(
            route('class-groups.unenroll', [props.classGroup.id, studentId])
        )
        enrolled.value = enrolled.value.filter(s => s.id !== studentId)
        enrolledIds.value = enrolledIds.value.filter(id => id !== studentId)
        toastSuccess('Student removed')
    } catch (e) {
        toastError(e.response?.data?.error || 'Could not remove')
    }
}

watch(selectedStudentId, async (studentId) => {
    canEnrollResult.value = null
    if (!studentId) return

    checking.value = true
    try {
        const { data } = await axios.get(
            route('class-groups.can-enroll', [
                props.classGroup.id,
                studentId
            ])
        )
        canEnrollResult.value = data
    } catch (e) {
        toastError('Could not validate enrollment')
    } finally {
        checking.value = false
    }
})

const cannotEnroll = computed(() =>
    checking.value ||
    !canEnrollResult.value ||
    !canEnrollResult.value.allowed
)

</script>

<template>
    <AppLayout :title="`Manage Enrollments — ${classGroup.code}`">
        <template #header>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white leading-tight">
                Manage Enrollments
            </h1>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 m-6">
            <!-- ─── GROUP INFO ─── -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Group Info</h2>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>Code:</strong> {{ classGroup.code }}</p>
                    <p><strong>Subject:</strong> {{ classGroup.subject.name }}</p>
                    <p><strong>Professor:</strong> {{ classGroup.professor.name }}</p>
                    <p><strong>Modality:</strong> {{ classGroup.modality }}</p>
                    <p><strong>Shift:</strong> {{ classGroup.shift }}</p>
                    <p><strong>Capacity:</strong> {{ enrolled.length }} / {{ classGroup.capacity }}
                    </p>
                </div>
            </div>

            <!-- ─── STUDENTS TABLE ─── -->
            <div
                class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Enrolled Students</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Total: {{ enrolled.length }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                            <tr>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2 hidden md:table-cell">Document</th>
                                <th class="px-4 py-2 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="stu in enrolled" :key="stu.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-2">{{ stu.name }}</td>
                                <td class="px-4 py-2 hidden md:table-cell">{{ stu.document }}</td>
                                <td class="px-4 py-2 text-center">
                                    <button @click="removeEnrollment(stu.id)"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200"
                                        title="Remove">
                                        <i class="fa-solid fa-user-minus"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="enrolled.length === 0">
                                <td colspan="3" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No students enrolled yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ─── ENROLL FORM ─── -->
            <div
                class="lg:col-span-3 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Enroll a New Student</h2>
                <div class="flex flex-col md:flex-row gap-4">
                    <select v-model="selectedStudentId" class="input">
                        <option disabled value="">Select a student…</option>
                        <option v-for="stu in available" :key="stu.id" :value="stu.id">
                            {{ stu.name }} ({{ stu.document }})
                        </option>
                    </select>
                    <p v-if="canEnrollResult && !canEnrollResult.allowed" class="text-sm text-red-600 mt-2">
                        {{ canEnrollResult.message }}

                    </p>

                    <button :disabled="!selectedStudentId || enrolling || cannotEnroll" @click="enrollStudent"
                        class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="checking">Checking...</span>
                        <span v-else-if="canEnrollResult && !canEnrollResult.allowed">
                            {{ canEnrollResult.message }}
                        </span>
                        <span v-else-if="enrolling">
                            Enrolling...
                        </span>
                        <span v-else>
                            Enroll
                        </span>

                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


<style scoped>
.input {
    @apply w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500;
}

.btn-primary {
    @apply bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-lg transition;
}
</style>