<script>
export default {
    name: "AssignmentsIndex",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref } from "vue";
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
        default: 0,
    },
});

const isModalOpen = ref(false);
const selectedAssignment = ref(null);

// Abrir el modal de asignación de materias
const openStudentsModal = (assignment) => {
    selectedAssignment.value = assignment;
    isModalOpen.value = true;
};

// Cerrar el modal de asignación de materias
const closeStudentsModal = () => {
    isModalOpen.value = false;
    selectedAssignment.value = null;
};

// Verificar datos en la consola
</script>

<template>
    <AppLayout title="Assignments Snapshot">
        <template #header>
            <h1 class="text-xl font-semibold leading-tight text-ink dark:text-white">
                Assignments Snapshot
            </h1>
        </template>

        <div class="min-h-screen bg-slate-50 py-12 dark:bg-dark-bg">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="rounded-lg border border-border-light bg-surface p-6 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                    <div class="mb-5 rounded-lg border border-warning/20 bg-warning/10 p-4 text-sm text-amber-800 dark:text-amber-300">
                        This is a legacy assignment snapshot. Official enrollment, schedules and grades now run through My Subjects, Subject Enrollment and Academic Reports.
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full overflow-hidden rounded-lg border border-border-light bg-surface text-center text-sm dark:border-border-dark dark:bg-surface-dark">
                            <thead>
                                <tr class="bg-slate-50 text-slate-700 dark:bg-zinc-950 dark:text-zinc-200">
                                    <th class="px-4 py-3 hidden sm:table-cell">#</th>
                                    <th class="px-4 py-3">Subject ID</th>
                                    <th class="px-4 py-3">Subject Name</th>
                                    <th v-if="role === 'student'" class="px-4 py-3">Knowledge Area</th>
                                    <th class="px-4 py-3">Subject Credits</th>
                                    <th v-if="role === 'student'" class="px-4 py-3">Elective</th>
                                    <th v-if="role === 'student'" class="px-4 py-3">Professor Name</th>
                                    <th v-if="role === 'professor'" class="px-4 py-3">Enrolled Students</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-light text-ink dark:divide-border-dark dark:text-zinc-100">
                                <tr v-for="(assignment, index) in assignments" :key="assignment.subject_id">
                                    <td class="px-4 py-3 hidden sm:table-cell">{{ index + 1 }}</td>
                                    <td class="px-4 py-3">{{ assignment.subject_id }}</td>
                                    <td class="px-4 py-3">{{ assignment.subject_name }}</td>
                                    <td v-if="role === 'student'" class="px-4 py-3">{{ assignment.knowledge_area }}</td>
                                    <td class="px-4 py-3">{{ assignment.credits }}</td>
                                    <td v-if="role === 'student'" class="px-4 py-3">
                                        {{ assignment.elective === 1 ? 'Yes' : 'No' }}
                                    </td>
                                    <td v-if="role === 'student'" class="px-4 py-3">
                                        {{ assignment.professor_name || 'Not Assigned' }}
                                    </td>
                                    <td v-if="role === 'professor'" class="px-4 py-3">
                                        <button @click="openStudentsModal(assignment)"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-brand transition hover:bg-brand/10 hover:text-brand-dark dark:text-brand">
                                            <i class="fa-solid fa-eye"></i> </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Contenedor para el total de créditos -->
                    <div v-if="role === 'student'"
                        class="mt-4 flex items-center justify-center rounded-lg border border-border-light bg-slate-50 px-4 py-2 dark:border-border-dark dark:bg-zinc-950">
                        <span class="font-semibold text-ink dark:text-zinc-100">Total credits assigned:</span>
                        <span class="ml-2 font-mono font-semibold text-brand dark:text-brand">{{ totalCredits }}</span>
                    </div>

                </div>
            </div>

            <!-- Modal para mostrar estudiantes matriculados en la asignatura -->
            <Modal :show="isModalOpen" maxWidth="2xl" @close="closeStudentsModal">
                <div class="flex min-h-[600px] flex-col items-center p-6">
                    <div class="mb-4 flex w-full items-center justify-between">
                        <h2 class="text-2xl font-semibold text-ink dark:text-white">Students Enrolled in <span class="capitalize underline">{{
                            selectedAssignment?.subject_name
                                }}</span>
                        </h2>
                        <span class="cursor-pointer" @click="closeStudentsModal">
                            <i class="fas fa-times text-slate-400 hover:text-ink dark:hover:text-white"></i>
                        </span>
                    </div>

                    <div v-if="selectedAssignment?.students?.length" class="overflow-x-auto">
                        <table class="min-w-full overflow-hidden rounded-lg border border-border-light bg-surface text-center text-sm dark:border-border-dark dark:bg-surface-dark">
                            <thead>
                                <tr class="bg-slate-50 text-slate-700 dark:bg-zinc-950 dark:text-zinc-200">
                                    <th class="px-4 py-3 hidden sm:table-cell">#</th>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Email</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-light text-ink dark:divide-border-dark dark:text-zinc-100">
                                <tr v-for="(student, index) in selectedAssignment.students" :key="student.student_id">
                                    <td class="px-4 py-3 hidden sm:table-cell">{{ index + 1 }}</td>
                                    <td class="px-4 py-3">{{ student.student_id }}</td>
                                    <td class="px-4 py-3">{{ student.student_name }}</td>
                                    <td class="px-4 py-3">{{ student.student_email }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-slate-500 dark:text-zinc-400">No students enrolled in this subject.</p>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

