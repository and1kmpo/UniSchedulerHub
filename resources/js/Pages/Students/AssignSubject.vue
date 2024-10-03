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
                            <multiselect v-model="selectedStudent" :options="students" label="fullName" track-by="id"
                                placeholder="Select a student" @select="loadAssignedSubjects">
                            </multiselect>
                        </div>

                        <div>
                            <h2 class="text-xl font-semibold mb-4">
                                Student's subjects:</h2>

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
                                        </tbody>
                                        <span class="font-semibold text-red-700">Total credits
                                            student:10</span>
                                    </table>
                                </div>
                            </template>

                            <template v-else>
                                <div class="p-4 bg-white shadow-md rounded-md">
                                    <p class="text-gray-500" v-if="selectedStudent">This student has no assigned
                                        subjects
                                        yet.</p>
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
                                        <multiselect v-model="selectedSubjects" :options="availableSubjects"
                                            label="name" track-by="id" :multiple="true" placeholder="Select subjects"
                                            style="width: 100%;" :maxHeight="300">
                                        </multiselect>
                                    </div>
                                    <button @click="assignSelectedSubjects"
                                        class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white">Assign</button>
                                    <div class="mb-4">
                                        <p class="text-sm font-medium text-gray-700">Total Credits: {{ selectedCredits
                                            }}
                                        </p>
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
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import AppLayout from "@/Layouts/AppLayout.vue";
import Multiselect from 'vue-multiselect';
import Modal from '@/Components/Modal.vue';
import Swal from 'sweetalert2';

const selectedStudent = ref(null);
const students = ref([]);
const assignedSubjects = ref([]);
const isModalOpen = ref(false);
const selectedSubjects = ref([]);
const availableSubjects = ref([]);
const assigningSubjects = ref(false);
const selectedCredits = ref(0);

const calculateSelectedCredits = () => {
    let totalCredits = 0;
    selectedSubjects.value.forEach(subject => {
        totalCredits += subject.credits;
    });
    selectedCredits.value = totalCredits;
};

// Observador para recalcular los créditos seleccionados cada vez que cambian las materias seleccionadas
watch(selectedSubjects, () => {
    calculateSelectedCredits();
});

// Get the list of students when the component loads
onMounted(async () => {
    try {
        const response = await axios.get('/students');
        const totalPages = response.data.last_page;

        let allStudents = response.data.data;

        for (let currentPage = 2; currentPage <= totalPages; currentPage++) {
            const nextPageResponse = await axios.get(`/students?page=${currentPage}`);
            allStudents = [...allStudents, ...nextPageResponse.data.data];
        }

        console.log(allStudents);

        students.value = allStudents.map(student => ({
            ...student,
            fullName: `${student.first_name} ${student.last_name}  -  ${student.document}`
        }));

        await loadSubjectsWithProfessorAssigned();
    } catch (error) {
        console.error('Error getting list of students:', error);
    }
});

// Load assigned subjects when a student is selected
const loadAssignedSubjects = async () => {
    try {
        if (selectedStudent.value) {
            const studentId = selectedStudent.value.id;
            const response = await axios.get(`/student-assigned-subjects/${studentId}`);

            assignedSubjects.value = response.data;
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
            name: `${subject.name}  (Credits: ${subject.credits})`,
            credits: subject.credits
        }));

    } catch (error) {
        console.error('Error getting all subjects', error);
    }
};

const openModalAssignSubject = () => {
    isModalOpen.value = true;
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
                const studentId = selectedStudent.value.id;
                const subjectsIds = selectedSubjects.value.map(subject => subject.id);

                // Request to assign the selected subjects to the selected student
                const response = await axios.post(`/students-assign-subject`, {
                    student_id: studentId,
                    subject_ids: subjectsIds
                });

                // Check if all conditions are met
                if (response.status === 200 && response.data.success) {
                    // Reload assigned subjects after assignment
                    await loadAssignedSubjects();
                    selectedSubjects.value = [];

                    // Close modal after assignment
                    closeAssignSubjectModal();

                    // Show success SweetAlert
                    Swal.fire({
                        title: 'Assigned subjects',
                        text: 'Selected subjects have been successfully assigned.',
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Reload assigned subjects after assignment
                            loadAssignedSubjects();
                            selectedSubjects.value = [];

                            // Close modal after assignment
                            closeAssignSubjectModal();
                        }
                    });
                } else {
                    // Show error SweetAlert if conditions are not met
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


const confirmUnassignSubject = (subjectId) => {
    Swal.fire({
        title: "You're sure?",
        text: 'This action will unassign the subject to the student.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fa-solid fa-check"></i> Yes, unassign',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            unassignSubject(subjectId);
        }
    });
};

const unassignSubject = async (subjectId) => {
    try {
        const response = await axios.delete(`/unassign-subject-student/${selectedStudent.value.id}/${subjectId}`);
        if (response.data.success) {
            await loadAssignedSubjects();
            Swal.fire('Unassigned subject', response.data.message, 'success');
        } else {
            Swal.fire('Error', response.data.message, 'error');
        }
    } catch (error) {
        console.error('Error when unassigning subject:', error);
        Swal.fire('Error', 'Error when unassigning subject.', 'error');
    }
};
</script>