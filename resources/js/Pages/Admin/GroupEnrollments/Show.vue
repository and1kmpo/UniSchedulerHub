<template>
    <AppLayout :title="`Manage Enrollments — ${group.code}`">
        <template #header>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Manage Enrollments
            </h1>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- ─── INFO DEL GRUPO ─── -->
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded shadow">
                <h2 class="text-xl font-semibold mb-4">Group Info</h2>
                <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                    <li><strong>Code:</strong> {{ group.code }}</li>
                    <li><strong>Subject:</strong> {{ group.subject }}</li>
                    <li><strong>Professor:</strong> {{ group.professor }}</li>
                    <li><strong>Modality:</strong> {{ group.modality }}</li>
                    <li><strong>Shift:</strong> {{ group.shift }}</li>
                    <li>
                        <strong>Capacity:</strong>
                        {{ group.subject_enrollments_count }} / {{ group.capacity }}
                    </li>
                </ul>
            </div>

            <!-- ─── LISTA DE INSCRIPTOS ─── -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded shadow">
                <h2 class="text-xl font-semibold mb-4">Enrolled Students</h2>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                        <tr>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2 hidden md:table-cell">Document</th>
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="stu in enrollments" :key="stu.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2">{{ stu.student_name }}</td>
                            <td class="px-4 py-2 hidden md:table-cell">{{ stu.document }}</td>
                            <td class="px-4 py-2 text-center">
                                <button @click="removeEnrollment(stu.id)"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200"
                                    title="Remove">
                                    <i class="fa-solid fa-user-minus"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="enrollments.length === 0">
                            <td colspan="3" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                No students enrolled yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ─── FORM INSCRIPCIÓN ─── -->
            <div class="lg:col-span-3 bg-white dark:bg-gray-800 p-6 rounded shadow">
                <h2 class="text-xl font-semibold mb-4">Enroll New Student 1</h2>
                <div class="flex flex-col md:flex-row gap-4">
                    <select v-model="selectedStudentId" class="input">
                        <option disabled value="">Select a student…</option>
                        <option v-for="s in available" :key="s.id" :value="s.id">
                            {{ s.name }} ({{ s.document }})
                        </option>
                    </select>
                    <button :disabled="!selectedStudentId || enrolling" @click="enrollStudent" class="btn-primary">
                        <span v-if="!enrolling">Enroll</span>
                        <span v-else>…</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useAlert } from '@/Components/Composables/useAlert'

const { toastSuccess, toastError, confirm } = useAlert()

// Props enviado por el servidor en show()
const props = defineProps({
    classGroup: Object,
    allStudents: Array,
    enrollments: Array,
})

// Refs locales
const group = ref({ ...props.classGroup })
const enrollments = ref([...props.enrollments])
const selectedStudentId = ref(null)
const enrolling = ref(false)

console.log('🚀 initial enrollments prop:', props.enrollments)

// Lista de estudiantes disponibles (no inscritos aún)
const available = computed(() =>
    props.allStudents.filter(s =>
        !enrollments.value.some(e => e.id === s.id)
    )
)

async function enrollStudent() {
    if (!selectedStudentId.value) return
    enrolling.value = true

    try {
        await axios.post(route('class-groups.enroll', group.value.id), {
            student_id: selectedStudentId.value
        })
        toastSuccess('Student enrolled successfully')
        // Recargar la página Inertia para obtener nuevas props
        Inertia.reload({ preserveState: true, preserveScroll: true })
    } catch (e) {
        toastError(e.response?.data?.message || 'Enrollment failed')
    } finally {
        enrolling.value = false
        selectedStudentId.value = null
    }
}

async function removeEnrollment(studentId) {
    const ok = await confirm('Are you sure you want to remove this student?', 'Confirm removal')
    if (!ok) return

    try {
        await axios.delete(route('class-groups.unenroll', [group.value.id, studentId]))
        toastSuccess('Student removed successfully')
        Inertia.reload({ preserveState: true, preserveScroll: true })
    } catch (e) {
        toastError(e.response?.data?.error || 'Could not remove enrollment')
    }
}
</script>

<style scoped>
.input {
    @apply w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white;
}

.btn-primary {
    @apply bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded;
}
</style>
