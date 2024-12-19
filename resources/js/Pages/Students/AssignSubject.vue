<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Assign subjects to student
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mt-4">
                        <div class="mb-4">
                            <label for="student" class="block text-sm font-medium text-gray-700">
                                Select student:
                            </label>
                            <!-- Reemplazo de v-select por un select convencional -->
                            <v-select v-model="selectedStudent" :options="students"
                                :reduce="student => student.student.id" label="fullName" @input="handleStudentChange"
                                placeholder="Select a student" class="w-full" />
                        </div>

                        <div>
                            <h2 class="text-xl font-semibold mb-4">Student's subjects:</h2>

                            <template v-if="assignedSubjects.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full mb-8">
                                        <thead>
                                            <tr class="border-b border-gray-200">
                                                <th class="py-2">Subject</th>
                                                <th class="py-2">Credits</th>
                                                <th class="py-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <tr v-for="subject in assignedSubjects" :key="subject.id"
                                                class="border-b border-gray-200">
                                                <td class="py-2">{{ subject.name }}</td>
                                                <td class="py-2">{{ subject.credits }}</td>
                                                <td class="py-2">
                                                    <button @click="confirmUnassignSubject(subject.id)"
                                                        class="text-xs text-red-700 hover:text-red-500 focus:outline-none">Unassign</button>
                                                </td>
                                            </tr>
                                            <tr class="bg-gray-100">
                                                <td class="px-4 py-2 font-semibold text-gray-800">Total credits
                                                    assigned:</td>
                                                <td class="px-4 py-2 font-semibold text-indigo-700">{{
                                                    totalAssignedCredits }}</td>
                                                <td class="px-4 py-2"></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </template>

                            <template v-else>
                                <div class="p-4 bg-white shadow-md rounded-md">
                                    <p class="text-gray-500" v-if="selectedStudent">This student has no assigned
                                        subjects yet.</p>
                                    <p class="text-gray-500" v-else>Select a student to view assigned subjects.</p>
                                </div>
                            </template>

                            <button @click="openModalAssignSubject" v-if="selectedStudent && !assigningSubjects"
                                class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white mb-4">
                                Assign more subjects
                            </button>

                            <!-- Modal -->
                            <Modal :show="isModalOpen" maxWidth="2xl" @close="closeAssignSubjectModal">
                                <div class="p-6 min-h-[500px] flex flex-col items-center">
                                    <div class="flex justify-between items-center w-full mb-4">
                                        <h2 class="text-2xl font-semibold">Assign subjects to the Student</h2>
                                        <span class="cursor-pointer" @click="closeAssignSubjectModal">
                                            <i class="fas fa-times text-gray-400 hover:text-black"></i>
                                        </span>
                                    </div>
                                    <div class="mb-4 w-full max-h-[300px]">
                                        <label for="selectedSubjects" class="block text-sm font-medium text-gray-700">
                                            Select subjects:
                                        </label>
                                        <!-- Reemplazo de v-select por un select convencional -->
                                        <v-select v-model="selectedSubjects" :options="availableSubjects"
                                            :reduce="subject => subject.id" label="name" multiple
                                            placeholder="Select subjects" class="w-full" />
                                    </div>
                                    <button @click="assignSelectedSubjects"
                                        class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white">Assign</button>
                                    <div class="mb-4">
                                        <p class="text-sm font-medium text-gray-700">Total Credits: {{ selectedCredits
                                            }}</p>
                                    </div>
                                </div>
                            </Modal>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import AppLayout from "@/Layouts/AppLayout.vue";
import Modal from '@/Components/Modal.vue';
import Swal from 'sweetalert2';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";

const selectedStudent = ref(null);
const students = ref([]);
const assignedSubjects = ref([]);
const isModalOpen = ref(false);
const selectedSubjects = ref([]);
const availableSubjects = ref([]);
const assigningSubjects = ref(false);
const selectedCredits = ref(0);

const totalAssignedCredits = computed(() => {
    return assignedSubjects.value.reduce((total, subject) => total + subject.credits, 0);
});

const calculateSelectedCredits = () => {
    selectedCredits.value = selectedSubjects.value.reduce((total, subjectId) => {
        const subject = availableSubjects.value.find(sub => sub.id === subjectId);
        const credits = subject ? subject.credits : 0; // Si no encuentra la asignatura, usa 0
        return total + credits;
    }, 0);
};



// Watcher for recalculating selected credits and selectedStudent
watch(selectedStudent, async (newValue) => {
    if (newValue) {
        await loadAssignedSubjects();
    } else {
        assignedSubjects.value = [];
    }
});

// Watcher para selectedSubjects
watch(selectedSubjects, calculateSelectedCredits);


const handleStudentChange = async () => {
    await loadAssignedSubjects(); // Carga las asignaturas del estudiante seleccionado.
};

onMounted(async () => {
    try {
        const response = await axios.get('/students');
        const totalPages = response.data.last_page;

        let allStudents = response.data.data;
        for (let currentPage = 2; currentPage <= totalPages; currentPage++) {
            const nextPageResponse = await axios.get(`/students?page=${currentPage}`);
            allStudents = [...allStudents, ...nextPageResponse.data.data];
        }

        students.value = allStudents.map(student => ({
            ...student,
            fullName: `${student.name} -  ${student.student.document}`
        }));

        await loadSubjectsWithProfessorAssigned();
    } catch (error) {
        console.error('Error getting list of students:', error);
    }
});

const loadAssignedSubjects = async () => {
    try {
        if (selectedStudent.value) {
            const studentId = selectedStudent.value;
            const response = await axios.get(`/student-assigned-subjects/${studentId}`);

            assignedSubjects.value = response.data.map(subject => ({
                id: subject.id,
                name: subject.name,
                credits: subject.credits,
            }));

            // Preselecciona las materias ya asignadas
            selectedSubjects.value = response.data.map(subject => subject.id);

        } else {
            assignedSubjects.value = [];
        }
    } catch (error) {
        console.error('Error getting subjects assigned:', error);
    }
};

const loadSubjectsWithProfessorAssigned = async () => {
    try {
        const response = await axios.get('/subjects-with-professors');
        availableSubjects.value = response.data.map(subject => ({
            id: subject.id,
            name: `${subject.name} (Credits: ${subject.credits})`,
            credits: Number(subject.credits) || 0, // Asegúrate de que los créditos sean numéricos
        }));
    } catch (error) {
        console.error('Error getting all subjects', error);
    }
};

const openModalAssignSubject = async () => {
    if (selectedStudent.value) {
        // Asegúrate de que las asignaturas asignadas estén actualizadas
        await loadAssignedSubjects();
    }
    isModalOpen.value = true; // Ahora abre el modal
};

const closeAssignSubjectModal = () => {
    isModalOpen.value = false;
};


const assignSelectedSubjects = async () => {
    try {
        const result = await Swal.fire({
            title: 'Confirm Assignment',
            text: 'Are you sure you want to assign the selected subjects?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fa-solid fa-check"></i> Yes, assign',
            cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancel',
        });

        if (result.isConfirmed) {
            assigningSubjects.value = true;

            if (selectedStudent.value) {
                const studentId = selectedStudent.value;
                const subjectsIds = selectedSubjects.value;

                // Aquí estamos enviando también los créditos seleccionados
                const response = await axios.post(`/students-assign-subject`, {
                    student_id: studentId,
                    subject_ids: subjectsIds,
                    total_credits: selectedCredits.value // Enviar el total de créditos al back-end
                });

                if (response.status === 200 && response.data.success) {
                    await loadAssignedSubjects();
                    selectedSubjects.value = [];
                    closeAssignSubjectModal();

                    Swal.fire({
                        title: 'Assigned subjects',
                        text: 'Selected subjects have been successfully assigned.',
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK',
                        timer: 2000,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.data.error || 'Error when assigning subjects.',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                }
            } else {
                console.error("No student has been selected.");
            }
        }
    } catch (error) {
        console.error('Error when assigning subjects:', error);
        Swal.fire('Error', 'Error when assigning subjects. Min 7 credits', 'error');
    } finally {
        assigningSubjects.value = false;
    }
};

const confirmUnassignSubject = async (subjectId) => {
    try {
        const result = await Swal.fire({
            title: 'Confirm Unassignment',
            text: 'Are you sure you want to unassign this subject?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fa-solid fa-trash"></i> Yes, unassign',
            cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancel',
        });

        if (result.isConfirmed) {
            const response = await axios.delete(`/unassign-subject-student/${selectedStudent.value}/${subjectId}`, {
                student_id: selectedStudent.value,
                subject_id: subjectId
            });

            if (response.status === 200 && response.data.success) {
                // Si la desasignación es exitosa
                assignedSubjects.value = assignedSubjects.value.filter(subject => subject.id !== subjectId);
                Swal.fire({
                    title: 'Subject Unassigned',
                    text: 'The subject has been successfully unassigned.',
                    icon: 'success',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    timerProgressBar: true
                });
            } else {
                // Si el servidor retorna un error (aunque con estado 200)
                Swal.fire({
                    title: 'Error',
                    text: response.data.error || 'Error when unassigning subject.',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            }
        }
    } catch (error) {
        // Si ocurre un error, como la violación de la regla de los 7 créditos mínimos
        if (error.response && error.response.status === 422 && error.response.data.error) {
            Swal.fire({
                title: 'Cannot Unassign Subject',
                text: error.response.data.error, // Mostrar el mensaje del controlador (ej. "Cannot unassign this subject. The student must have at least 7 credits assigned.")
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        } else {
            // Para otros errores no controlados
            console.error('Error when unassigning subject:', error);
            Swal.fire('Error', 'Error when unassigning subject.', 'error');
        }
    }
};
</script>
