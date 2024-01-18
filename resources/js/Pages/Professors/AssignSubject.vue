<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Assign Subject to Professor
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mt-4">
                        <!-- Seleccionar profesor -->
                        <div class="mb-4">
                            <label for="professor" class="block text-sm font-medium text-gray-700">
                                Seleccionar Profesor:
                            </label>
                            <multiselect v-model="selectedProfessor" :options="professors" label="fullName" track-by="id"
                                placeholder="Seleccione un profesor" @select="loadAssignedSubjects">
                            </multiselect>
                        </div>

                        <!-- Mostrar asignaturas asignadas -->
                        <div>
                            <h2 class="text-xl font-semibold mb-4">Asignaturas del Profesor:</h2>

                            <template v-if="assignedSubjects.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full mb-8">
                                        <thead>
                                            <tr class="border-b border-gray-200">
                                                <th class="py-2">Asignatura</th>
                                                <th class="py-2">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <tr v-for="subject in assignedSubjects" :key="subject.id"
                                                class="border-b border-gray-200">
                                                <td class="py-2">{{ subject.name }}</td>
                                                <td class="py-2">
                                                    <button @click="confirmUnassignSubject(subject.id)"
                                                        class="text-xs text-red-700 hover:text-red-500 focus:outline-none">Desasignar</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <template v-else>
                                <div class="p-4 bg-white shadow-md rounded-md">
                                    <p class="text-gray-500" v-if="selectedProfessor">Este profesor no tiene asignaturas
                                        asignadas.</p>
                                    <p class="text-gray-500" v-else>Seleccione un profesor para ver las asignaturas
                                        asignadas.</p>
                                </div>
                            </template>

                            <!-- Botón para asignar más asignaturas -->
                            <button @click="openModalAssignSubject" v-if="selectedProfessor && !assigningSubjects"
                                class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white mb-4">
                                Asignar más asignaturas
                            </button>

                            <!-- Modal -->
                            <Modal :show="isModalOpen" maxWidth="2xl" @close="closeAssignSubjectModal">
                                <div class="p-6 min-h-[500px] flex flex-col items-center">
                                    <div class="flex justify-between items-center w-full mb-4">
                                        <h2 class="text-2xl font-semibold">Asignar Asignaturas al Profesor</h2>
                                        <span class="cursor-pointer" @click="closeAssignSubjectModal">
                                            <i class="fas fa-times text-gray-400 hover:text-black"></i>
                                        </span>
                                    </div>
                                    <div class="mb-4 w-full max-h-[300px]">
                                        <label for="selectedSubjects" class="block text-sm font-medium text-gray-700">
                                            Seleccionar Asignaturas:
                                        </label>
                                        <multiselect v-model="selectedSubjects" :options="availableSubjects" label="name"
                                            track-by="id" :multiple="true" placeholder="Seleccione asignaturas"
                                            style="width: 100%;" :maxHeight="300">
                                        </multiselect>
                                    </div>
                                    <button @click="assignSelectedSubjects"
                                        class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white">Asignar</button>
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

// Obtener la lista de profesores al cargar el componente
onMounted(async () => {
    try {
        const response = await axios.get('/professors');
        professors.value = response.data.data.map(professor => ({
            ...professor,
            fullName: `${professor.first_name} ${professor.last_name}  -  ${professor.document}`
        }));

        await loadAllSubjects();
    } catch (error) {
        console.error('Error al obtener la lista de profesores:', error);
    }
});

// Cargar las asignaturas asignadas cuando se selecciona un profesor
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
        console.error('Error al cargar asignaturas asignadas:', error);
    }
};

// Cargar todas las asignaturas
const loadAllSubjects = async () => {
    try {
        const response = await axios.get('/subjects');
        const totalPages = response.data.last_page;

        let allSubjects = [];

        // Agregar las asignaturas de la página actual al array
        allSubjects = [...allSubjects, ...response.data.data];

        // Realizar solicitudes para las páginas restantes (si hay más de una página)
        for (let currentPage = 2; currentPage <= totalPages; currentPage++) {
            const nextPageResponse = await axios.get(`/subjects?page=${currentPage}`);
            allSubjects = [...allSubjects, ...nextPageResponse.data.data];
        }

        availableSubjects.value = allSubjects;

    } catch (error) {
        console.error('Error al cargar todas las asignaturas:', error);
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

            // Recargar las asignaturas asignadas después de la asignación
            await loadAssignedSubjects();
            selectedSubjects.value = [];

        } else {
            console.error("No se ha seleccionado ningún profesor");
        }

        // Cerrar el modal después de la asignación
        closeAssignSubjectModal();

    } catch (error) {
        console.error('Error al asignar las asignaturas:', error);
    } finally {
        assigningSubjects.value = false;
    }

};
const confirmUnassignSubject = (subjectId) => {
    // Utiliza SweetAlert para mostrar una alerta de confirmación
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción desasignará la materia al profesor.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, desasignar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            // Llama a la función para desasignar la materia en el servidor
            unassignSubject(subjectId);
        }
    });
};

const unassignSubject = async (subjectId) => {
    try {
        // Realiza una solicitud para desasignar la materia
        const response = await axios.delete(`/unassign-subject/${selectedProfessor.value.id}/${subjectId}`);

        // Si la desasignación es exitosa, recarga las asignaturas asignadas
        if (response.data.success) {
            await loadAssignedSubjects();
            Swal.fire('Desasignado', response.data.message, 'success');
        } else {
            Swal.fire('Error', response.data.message, 'error');
        }
    } catch (error) {
        console.error('Error al desasignar asignatura:', error);
        Swal.fire('Error', 'Error al desasignar asignatura.', 'error');
    }
};
</script>
  