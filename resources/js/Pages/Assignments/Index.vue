<script>
export default {
    name: "AssignmentsIndex",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, onMounted, ref } from "vue";
import Modal from '@/Components/Modal.vue';

// Props recibidas desde el controlador
const props = defineProps({
    assignments: {
        type: Array,
        required: true,
    },
    role: {
        type: String,
        required: true,
    },
    totalCredits: {
        type: Number,
        required: false,
        default: 0,
    },
});

const isModalOpen = ref(false);
const selectedAssignment = ref(null);

// Abrir el modal de asignación de materias
const openStudentsModal = async (assignment) => {
    selectedAssignment.value = assignment;
    isModalOpen.value = true;
};

// Cerrar el modal de asignación de materias
const closeStudentsModal = () => {
    isModalOpen.value = false;
    selectedAssignment.value = null;
};

// Verificar datos en la consola
onMounted(() => {
    console.log(props);
});

</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Assignments
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full bg-white shadow-md rounded-xl text-center">
                            <thead>
                                <tr class="bg-blue-gray-100 text-gray-700">
                                    <th class="py-3 px-4 hidden sm:table-cell">#</th>
                                    <th class="py-3 px-4">Subject ID</th>
                                    <th class="py-3 px-4">Subject Name</th>
                                    <th v-if="role === 'student'" class="py-3 px-4">Knowledge Area</th>
                                    <th class="py-3 px-4">Subject Credits</th>
                                    <th v-if="role === 'student'" class="py-3 px-4">Elective</th>
                                    <th v-if="role === 'student'" class="py-3 px-4">Professor Name</th>
                                    <th class="py-3 px-4">Enrolled Students</th>
                                </tr>
                            </thead>
                            <tbody class="text-blue-gray-900 divide-y divide-blue-gray-200">
                                <tr v-for="(assignment, index) in assignments" :key="assignment.subject_id">
                                    <td class="py-3 px-4 hidden sm:table-cell">{{ index + 1 }}</td>
                                    <td class="py-3 px-4">{{ assignment.subject_id }}</td>
                                    <td class="py-3 px-4">{{ assignment.subject_name }}</td>
                                    <td v-if="role === 'student'" class="py-3 px-4">{{ assignment.knowledge_area }}</td>
                                    <td class="py-3 px-4">{{ assignment.credits }}</td>
                                    <td v-if="role === 'student'" class="py-3 px-4">
                                        {{ assignment.elective === 1 ? 'Yes' : 'No' }}
                                    </td>
                                    <td v-if="role === 'student'" class="py-3 px-4">
                                        {{ assignment.professor_name || 'Not Assigned' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <button @click="openStudentsModal(assignment)"
                                            class="dark:text-indigo-400 text-indigo-800 transition ease-in-out hover:-translate-y-1 hover:scale-110 duration-300">
                                            <i class="fa-solid fa-eye"></i> </button>
                                    </td>
                                </tr>
                                <tr v-if="role === 'student'" class="bg-gray-100">
                                    <td colspan="3" class="px-4 py-2 font-semibold text-gray-800 text-right">
                                        Total credits assigned:
                                    </td>
                                    <td colspan="2" class="px-4 py-2 font-semibold text-indigo-700">{{ totalCredits }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal para asignar asignaturas -->
            <Modal :show="isModalOpen" maxWidth="2xl" @close="closeStudentsModal">
                <div class="p-6 min-h-[600px] flex flex-col items-center">
                    <div class="flex justify-between items-center w-full mb-4">
                        <h2 class="text-2xl font-semibold">Students Enrolled in <span class="capitalize underline">{{
                            selectedAssignment.subject_name
                                }}</span>
                        </h2>
                        <span class="cursor-pointer" @click="closeStudentsModal">
                            <i class="fas fa-times text-gray-400 hover:text-black"></i>
                        </span>
                    </div>

                    <div v-if="selectedAssignment?.students?.length" class="overflow-x-auto">
                        <table class="min-w-full bg-white shadow-md rounded-xl text-center">
                            <thead>
                                <tr class="bg-blue-gray-100 text-gray-700">
                                    <th class="py-3 px-4 hidden sm:table-cell">#</th>
                                    <th class="py-3 px-4">Student ID</th>
                                    <th class="py-3 px-4">Student Name</th>
                                </tr>
                            </thead>
                            <tbody class="text-blue-gray-900 divide-y divide-blue-gray-200">
                                <tr v-for="(student, index) in selectedAssignment.students" :key="student.student_id">
                                    <td class="py-3 px-4 hidden sm:table-cell">{{ index + 1 }}</td>
                                    <td class="py-3 px-4">{{ student.student_id }}</td>
                                    <td class="py-3 px-4">{{ student.student_name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-gray-500">No students enrolled in this subject.</p>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>
