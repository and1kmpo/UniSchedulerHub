<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Assign subjects to professor
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mt-4">
                        <div class="mb-4">
                            <label for="professor" class="block text-sm font-medium text-gray-700">

                                Select professor:
                            </label>
                            <multiselect v-model="selectedProfessor" :options="professors" label="fullName" track-by="id"
                                placeholder="Select a professor" @select="loadAssignedSubjects">
                            </multiselect>
                        </div>

                        <div>
                            <h2 class="text-xl font-semibold mb-4">
                                Professor's subjects:</h2>

                            <template v-if="assignedSubjects.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full mb-8">
                                        <thead>
                                            <tr class="border-b border-gray-200">
                                                <th class="py-2">Subject</th>
                                                <th class="py-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <tr v-for="subject in assignedSubjects" :key="subject.id"
                                                class="border-b border-gray-200">
                                                <td class="py-2">{{ subject.name }}</td>
                                                <td class="py-2">
                                                    <button @click="confirmUnassignSubject(subject.id)"
                                                        class="text-xs text-red-700 hover:text-red-500 focus:outline-none">Unassign</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <template v-else>
                                <div class="p-4 bg-white shadow-md rounded-md">
                                    <p class="text-gray-500" v-if="selectedProfessor">This professor has no assigned
                                        subjects
                                        yet.</p>
                                    <p class="text-gray-500" v-else>Select a professor to view assigned subjects.</p>
                                </div>
                            </template>


                            <button @click="openModalAssignSubject" v-if="selectedProfessor && !assigningSubjects"
                                class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white mb-4">
                                Assign more subjects
                            </button>

                            <!-- Modal -->
                            <Modal :show="isModalOpen" maxWidth="2xl" @close="closeAssignSubjectModal">
                                <div class="p-6 min-h-[500px] flex flex-col items-center">
                                    <div class="flex justify-between items-center w-full mb-4">
                                        <h2 class="text-2xl font-semibold">Assign subjects to the Professor</h2>
                                        <span class="cursor-pointer" @click="closeAssignSubjectModal">
                                            <i class="fas fa-times text-gray-400 hover:text-black"></i>
                                        </span>
                                    </div>
                                    <div class="mb-4 w-full max-h-[300px]">
                                        <label for="selectedSubjects" class="block text-sm font-medium text-gray-700">
                                            Select subjects:
                                        </label>
                                        <multiselect v-model="selectedSubjects" :options="availableSubjects" label="name"
                                            track-by="id" :multiple="true" placeholder="Select subjects"
                                            style="width: 100%;" :maxHeight="300">
                                        </multiselect>
                                    </div>
                                    <button @click="assignSelectedSubjects"
                                        class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white">Assign</button>
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
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from "@/Layouts/AppLayout.vue";
import Multiselect from 'vue-multiselect';
import Modal from '@/Components/Modal.vue';
import Swal from 'sweetalert2';

const selectedProfessor = ref(null);
const professors = ref([]);
const assignedSubjects = ref([]);
const isModalOpen = ref(false);
const selectedSubjects = ref([]);
const availableSubjects = ref([]);
const assigningSubjects = ref(false);

// Get the list of professors when the component loads
onMounted(async () => {
    try {
        const response = await axios.get('/professors');
        const totalPages = response.data.last_page;

        let allProfessors = response.data.data;

        for (let currentPage = 2; currentPage <= totalPages; currentPage++) {
            const nextPageResponse = await axios.get(`/professors?page=${currentPage}`);
            allProfessors = [...allProfessors, ...nextPageResponse.data.data];
        }

        console.log(allProfessors)

        professors.value = allProfessors.map(professor => ({
            ...professor,
            fullName: `${professor.first_name} ${professor.last_name}  -  ${professor.document}`
        }));

        await loadAllSubjects();
    } catch (error) {
        console.error('Error getting list of professors:', error);
    }
});

// Load assigned subjects when a professor is selected
const loadAssignedSubjects = async () => {
    try {
        if (selectedProfessor.value) {
            const professorId = selectedProfessor.value.id;
            const response = await axios.get(`/professor-assigned-subjects/${professorId}`);
            assignedSubjects.value = response.data;
        } else {
            assignedSubjects.value = [];
        }
    } catch (error) {
        console.error('Error getting subjects assigned:', error);
    }
};

const loadAllSubjects = async () => {
    try {
        const response = await axios.get('/subjects');
        const totalPages = response.data.last_page;

        let allSubjects = [];

        // Add the subjects of the current page to the array
        allSubjects = [...allSubjects, ...response.data.data];

        // Make requests for the remaining pages (if there is more than one page). Since the backend returns the subject list paginated
        for (let currentPage = 2; currentPage <= totalPages; currentPage++) {
            const nextPageResponse = await axios.get(`/subjects?page=${currentPage}`);
            allSubjects = [...allSubjects, ...nextPageResponse.data.data];
        }

        availableSubjects.value = allSubjects;

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

            if (selectedProfessor.value) {
                const professorId = selectedProfessor.value.id;
                const subjectsIds = selectedSubjects.value.map(subject => subject.id);


                console.log('Professor ID:', professorId);
                console.log('Subject IDs:', selectedSubjects.value);
                //Request to assign the selected subjects to the selected professor
                await axios.post(`/professors-assign-subject`, {
                    professor_id: professorId,
                    subject_ids: subjectsIds
                });

                // Reload assigned subjects after assignment
                await loadAssignedSubjects();
                selectedSubjects.value = [];

            } else {
                console.error("No professor has been selected.");
            }
            // Muestra un SweetAlert de éxito
            Swal.fire('Assigned subjects', 'Selected subjects have been successfully assigned.', 'success');
            // Close modal after assignment
            closeAssignSubjectModal();
        }
    } catch (error) {
        console.error('Error when assigning subjects:', error);
    } finally {
        assigningSubjects.value = false;
    }

};
const confirmUnassignSubject = (subjectId) => {
    Swal.fire({
        title: "You're sure?",
        text: 'This action will unassign the subject to the professor.',
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
        // Make a request to unassign the subject
        const response = await axios.delete(`/unassign-subject-professor/${selectedProfessor.value.id}/${subjectId}`);

        // If the subject unassignment is successful, reload the assigned subjects
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
  