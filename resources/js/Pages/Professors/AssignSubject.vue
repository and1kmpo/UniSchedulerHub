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
                            <!-- <select id="professor" v-model="selectedProfessor" @change="handleProfessorChange"
                                class="block w-full mt-1 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="" disabled>Select a professor</option>
                                <option v-for="professor in professors" :key="professor.id" :value="professor">
                                    {{ professor.fullName }}
                                </option>
                            </select> -->

                            <v-select :options="professors" label="fullName" :reduce="professor => professor.id"
                                v-model="selectedProfessor" @input="handleProfessorChange"
                                placeholder="Search and select a professor..." :clearable="true" />


                        </div>

                        <!-- Listado de Materias Asignadas -->
                        <div>
                            <h2 class="text-xl font-semibold">Professor's Subjects:</h2>
                            <template v-if="assignedSubjects.length > 0">
                                <p class="text-gray-500 text-xs">You can select the first checkbox to select all
                                    subjects</p>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full mb-4 border-collapse border border-gray-200">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="py-2 px-4 text-center">
                                                    <input type="checkbox" @click="toggleSelectAll"
                                                        :checked="isAllSelected"
                                                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded" />
                                                </th>
                                                <th class="py-2 px-4 text-center">Subject</th>
                                                <th class="py-2 px-4 text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="subject in assignedSubjects" :key="subject.id"
                                                class="border-b border-gray-200">
                                                <td class="py-2 px-4 text-center">
                                                    <input type="checkbox" v-model="selectedSubjectsForRemoval"
                                                        :value="subject.id"
                                                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded" />
                                                </td>
                                                <td class="py-2 px-4 text-center">{{ subject.name }}</td>
                                                <td class="py-2 px-4 text-center">
                                                    <button @click="confirmUnassignSubject(subject.id)"
                                                        class="text-xs text-red-700 hover:text-red-500 hover:-translate-y-1 hover:scale-110 duration-300">
                                                        Unassign <i class="fa-regular fa-circle-xmark"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <template v-else>
                                <div class="p-4 bg-white shadow-md rounded-md">
                                    <p class="text-gray-500 text-center">
                                        No subjects assigned to this professor.
                                    </p>
                                </div>
                            </template>

                            <div class="flex justify-end items-center space-x-4 mt-4">
                                <button @click="openModalAssignSubject"
                                    class="bg-indigo-700 hover:bg-indigo-500 text-white rounded px-4 py-2 text-sm"
                                    :disabled="!selectedProfessor" :class="{
                                        'bg-indigo-700 hover:bg-indigo-500 cursor-pointer': selectedProfessor,
                                        'bg-indigo-300 cursor-not-allowed': !selectedProfessor
                                    }">
                                    Assign Subjects
                                </button>

                                <button @click="confirmUnassignSelectedSubjects"
                                    :disabled="selectedSubjectsForRemoval.length <= 0" :class="{
                                        'bg-red-600 hover:bg-red-400': selectedSubjectsForRemoval.length > 0,
                                        'bg-red-300 cursor-not-allowed': selectedSubjectsForRemoval.length <= 0
                                    }" class="hover:bg-red-400 text-white rounded px-4 py-2 text-white text-sm">
                                    Unassign Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal para asignar asignaturas -->
            <Modal :show="isModalOpen" maxWidth="2xl" @close="closeAssignSubjectModal">
                <div class="p-6 min-h-[600px] flex flex-col items-center">
                    <div class="flex justify-between items-center w-full mb-4">
                        <h2 class="text-2xl font-semibold">Assign Subjects to Professor</h2>
                        <span class="cursor-pointer" @click="closeAssignSubjectModal">
                            <i class="fas fa-times text-gray-400 hover:text-black"></i>
                        </span>
                    </div>

                    <div class="mb-4 w-full">
                        <!-- <label for="selectedSubjects" class="block text-sm font-medium text-gray-700">
                            Select subjects:
                        </label>
                        <select v-model="selectedSubjects" id="selectedSubjects" multiple
                            class="block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option v-for="subject in availableSubjects" :key="subject.id" :value="subject.id">
                                {{ subject.name }}
                            </option>
                        </select> -->

                        <v-select :options="availableSubjects" label="name" :reduce="subject => subject.id"
                            v-model="selectedSubjects" :multiple="true"
                            placeholder="Search and select one or more subjects..." :clearable="true" />
                    </div>

                    <button @click="assignSelectedSubjects"
                        class="bg-indigo-700 hover:bg-indigo-500 text-white rounded p-2 px-4 mt-4"
                        :disabled="assigningSubjects">
                        {{ assigningSubjects ? 'Assigning...' : 'Assign' }}
                    </button>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>


<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import AppLayout from "@/Layouts/AppLayout.vue";
import Swal from 'sweetalert2';
import Modal from '@/Components/Modal.vue';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";


// Referencias Reactivas
const selectedProfessor = ref(null);
const professors = ref([]);
const assignedSubjects = ref([]);
const availableSubjects = ref([]);
const selectedSubjects = ref([]);
const selectedSubjectsForRemoval = ref([]);
const isModalOpen = ref(false);
const assigningSubjects = ref(false);

// Cargar lista de profesores al montar el componente
onMounted(async () => {
    await loadProfessors();
    await loadAllSubjects();
});

watch(selectedProfessor, async (newVal) => {
    console.log('Watcher - Selected Professor ID:', newVal);

    if (newVal) {
        await loadAssignedSubjects();
    }
});

// Función para cargar profesores
const loadProfessors = async () => {
    try {
        const response = await axios.get('/professors');
        const allProfessors = response.data.data;

        professors.value = allProfessors.map(prof => ({
            id: prof.professor.id,
            fullName: `${prof.professor.first_name} ${prof.professor.last_name} - ${prof.professor.document}`
        }));

        // Verificar la lista de profesores cargada
        console.log('Professors Loaded:', professors.value);
    } catch (error) {
        console.error('Error loading professors:', error);
    }
};

const loadAllSubjects = async () => {
    let allSubjects = [];
    let currentPage = 1;
    let hasMorePages = true;

    try {
        // Mientras haya más páginas, seguiremos cargando
        while (hasMorePages) {
            const response = await axios.get(`/subjects?page=${currentPage}`);
            const data = response.data;

            // Concatenamos las asignaturas obtenidas
            allSubjects = [...allSubjects, ...data.data];

            // Verificamos si hay más páginas
            if (data.next_page_url) {
                currentPage++; // Pasamos a la siguiente página
            } else {
                hasMorePages = false; // No hay más páginas
            }
        }

        // Guardamos todas las asignaturas en la variable de estado
        availableSubjects.value = allSubjects;

        console.log("Total Subjects Loaded:", availableSubjects.value.length);
        console.log("Subjects:", availableSubjects.value);

    } catch (error) {
        console.error('Error loading all subjects:', error);
    }
};



// Manejar cambio de profesor seleccionado
const handleProfessorChange = async () => {
    // Verificar si el `selectedProfessor` tiene un valor
    console.log('Selected Professor ID:', selectedProfessor.value);

    if (!selectedProfessor.value) {
        console.warn('No professor selected');
        return;
    }

    // Verificar el profesor seleccionado en la lista original
    const professorData = professors.value.find(prof => prof.id === selectedProfessor.value);
    console.log('Full Professor Data:', professorData);

    selectedSubjectsForRemoval.value = []; // Limpia las selecciones anteriores

    await loadAssignedSubjects();
};


// Función para cargar asignaturas asignadas al profesor
const loadAssignedSubjects = async () => {
    if (!selectedProfessor.value) return;

    try {
        console.log(`Loading subjects for professor ID: ${selectedProfessor.value}`);
        const response = await axios.get(`/professor-assigned-subjects/${selectedProfessor.value}`);

        if (response.data) {
            console.log('Assigned Subjects:', response.data);
            assignedSubjects.value = response.data;
        } else {
            console.warn('No subjects returned from API');
            assignedSubjects.value = [];
        }
    } catch (error) {
        console.error('Error loading assigned subjects:', error);
        Swal.fire('Error', 'Failed to load assigned subjects.', 'error');
        assignedSubjects.value = [];
    }
};



// Seleccionar o deseleccionar todas las asignaturas para eliminar
const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedSubjectsForRemoval.value = [];
    } else {
        selectedSubjectsForRemoval.value = assignedSubjects.value.map(subject => subject.id);
    }
};

// Computado para verificar si todas las asignaturas están seleccionadas
const isAllSelected = computed(() => selectedSubjectsForRemoval.value.length === assignedSubjects.value.length);

// Asignar asignaturas seleccionadas al profesor
const assignSelectedSubjects = async () => {
    if (!selectedProfessor.value) {
        Swal.fire('Error', 'No professor selected.', 'error');
        return;
    }

    if (selectedSubjects.value.length === 0) {
        Swal.fire('Notice', 'No subjects selected to assign.', 'info');
        return;
    }

    const result = await Swal.fire({
        title: 'Confirm Assignment',
        text: 'Are you sure you want to assign the selected subjects?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, assign',
        cancelButtonText: 'Cancel',
    });

    if (result.isConfirmed) {
        assigningSubjects.value = true;

        try {
            const professorId = selectedProfessor.value;

            console.log('Sending data:', {
                professor_id: selectedProfessor.value,
                subject_ids: selectedSubjectsForRemoval.value,
            });


            // Enviar solicitud para asignar materias
            await axios.post('/professors-assign-subject', {
                professor_id: professorId,
                subject_ids: selectedSubjects.value,
            });

            await loadAssignedSubjects(); // Recargar materias asignadas

            Swal.fire({
                title: 'Success',
                text: 'Subjects assigned successfully.',
                icon: 'success',
                timer: 3000,
                timerProgressBar: true
            });

            closeAssignSubjectModal();
        } catch (error) {
            console.error('Error when assigning subjects:', error);
            Swal.fire('Error', 'Failed to assign subjects.', 'error');
        } finally {
            assigningSubjects.value = false;
        }
    }
};

// Función para abrir el modal de asignación de materias
const openModalAssignSubject = async () => {
    isModalOpen.value = true;
};

// Cargar todas las materias disponibles para asignar
const loadAvailableSubjects = async () => {
    try {
        const response = await axios.get('/subjects');
        console.log(response)
        availableSubjects.value = response.data.data;
    } catch (error) {
        console.error('Error loading subjects:', error);
    }
};

// Cerrar el modal de asignación de materias
const closeAssignSubjectModal = () => {
    isModalOpen.value = false;
    selectedSubjects.value = [];
};

// Confirmar desasignación de materias individualmente
const confirmUnassignSubject = (subjectId) => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will unassign the subject from the professor.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, unassign',
        cancelButtonText: 'Cancel',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/unassign-subject-professor/${selectedProfessor.value}/${subjectId}`);
                await loadAssignedSubjects();

                Swal.fire({
                    title: 'Success',
                    text: 'Subject have been unassigned.',
                    icon: 'success',
                    timer: 3000,
                    timerProgressBar: true
                });

            } catch (error) {
                console.error('Error unassigning subject:', error);
                Swal.fire('Error', 'Failed to unassign subject.', 'error');
            }
        }
    });
};

// Confirmar desasignación de materias en grupo
const confirmUnassignSelectedSubjects = async () => {
    if (selectedSubjectsForRemoval.value.length === 0) {
        Swal.fire('Notice', 'No subjects selected to unassign.', 'info');
        return;
    }

    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'This will unassign the selected subjects from the professor.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, unassign',
        cancelButtonText: 'Cancel',
    });

    if (result.isConfirmed) {
        try {
            await axios.post('/unassign-selected-subjects', {
                professor_id: selectedProfessor.value,
                subject_ids: selectedSubjectsForRemoval.value,
            });

            await loadAssignedSubjects();
            selectedSubjectsForRemoval.value = [];

            Swal.fire({
                title: 'Success',
                text: 'Selected subjects have been unassigned.',
                icon: 'success',
                timer: 3000,
                timerProgressBar: true
            });

        } catch (error) {
            console.error('Error unassigning subjects:', error);
            Swal.fire('Error', 'Failed to unassign subjects.', 'error');
        }
    }
};
</script>
