<script>
export default {
    name: "AssignmentsIndex",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref } from "vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
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

</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-xl font-semibold leading-tight text-ink dark:text-white">
                Academic Assignments
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="rounded-xl border border-border-light bg-surface p-6 dark:border-border-dark dark:bg-surface-dark">
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-center text-sm">
                            <thead>
                                <tr class="border-b border-border-light bg-slate-50 text-slate-600 dark:border-border-dark dark:bg-zinc-900 dark:text-zinc-300">
                                    <th class="py-3 px-4 hidden sm:table-cell">#</th>
                                    <th class="py-3 px-4">Subject ID</th>
                                    <th class="py-3 px-4">Subject Name</th>
                                    <th v-if="role === 'student'" class="py-3 px-4">Knowledge Area</th>
                                    <th class="py-3 px-4">Subject Credits</th>
                                    <th v-if="role === 'student'" class="py-3 px-4">Elective</th>
                                    <th v-if="role === 'student'" class="py-3 px-4">Professor Name</th>
                                    <th v-if="role === 'professor'" class="py-3 px-4">Enrolled Students</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-light text-ink dark:divide-border-dark dark:text-zinc-100">
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
                                    <td v-if="role === 'professor'" class="py-3 px-4">
                                        <BaseButton type="button" variant="secondary" size="sm" @click="openStudentsModal(assignment)">
                                            <i class="fa-solid fa-users mr-2"></i>
                                            Students
                                        </BaseButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Contenedor para el total de créditos -->
                    <div v-if="role === 'student'"
                        class="mt-4 flex items-center justify-center rounded-lg border border-border-light bg-slate-50 px-4 py-3 dark:border-border-dark dark:bg-zinc-900">
                        <span class="font-semibold text-ink dark:text-zinc-100">Connected credits:</span>
                        <span class="ml-2 font-mono font-semibold text-brand dark:text-brand-300">{{ totalCredits }}</span>
                    </div>

                </div>
            </div>

            <!-- Modal para mostrar estudiantes matriculados en la asignatura -->
            <Modal :show="isModalOpen" maxWidth="2xl" @close="closeStudentsModal">
                <div class="flex min-h-[600px] flex-col items-center p-6">
                    <div class="flex justify-between items-center w-full mb-4">
                        <h2 class="text-2xl font-semibold text-ink dark:text-white">Students enrolled in <span class="capitalize underline">{{
                            selectedAssignment.subject_name
                                }}</span>
                        </h2>
                        <span class="cursor-pointer" @click="closeStudentsModal">
                            <i class="fas fa-times text-slate-400 hover:text-ink dark:hover:text-white"></i>
                        </span>
                    </div>

                    <div v-if="selectedAssignment?.students?.length" class="overflow-x-auto">
                        <table class="min-w-full rounded-xl border border-border-light bg-surface text-center text-sm dark:border-border-dark dark:bg-surface-dark">
                            <thead>
                                <tr class="border-b border-border-light bg-slate-50 text-slate-600 dark:border-border-dark dark:bg-zinc-900 dark:text-zinc-300">
                                    <th class="py-3 px-4 hidden sm:table-cell">#</th>
                                    <th class="py-3 px-4">ID</th>
                                    <th class="py-3 px-4">Name</th>
                                    <th class="py-3 px-4">Email</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-light text-ink dark:divide-border-dark dark:text-zinc-100">
                                <tr v-for="(student, index) in selectedAssignment.students" :key="student.student_id">
                                    <td class="py-3 px-4 hidden sm:table-cell">{{ index + 1 }}</td>
                                    <td class="py-3 px-4">{{ student.student_id }}</td>
                                    <td class="py-3 px-4">{{ student.student_name }}</td>
                                    <td class="py-3 px-4">{{ student.student_email }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-slate-500 dark:text-zinc-400">No students connected to this subject yet.</p>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>
