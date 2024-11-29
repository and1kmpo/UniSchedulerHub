<template>
    <div class="mx-auto max-w-4xl p-6">
        <!-- Subject report per student -->
        <h2 class="text-lg font-medium text-center mb-4">Subject report per student</h2>

        <!-- Search Input -->
        <div class="relative mb-6">
            <input v-model="search"
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 pl-10"
                placeholder="Type to search" />
            <i class="absolute left-4 top-3 text-gray-500 fas fa-search"></i>
        </div>

        <!-- Assignment Table -->
        <el-table :data="filteredAssignments" border :default-sort="{ prop: 'last_name', order: 'ascending' }">

            <!-- Student Column -->
            <el-table-column label="Student" prop="last_name" sortable :filterable="true"
                :header-row-class-name="headerRowClassName">
                <template v-slot="{ row }">
                    {{ row.first_name }} {{ row.last_name }}
                </template>
            </el-table-column>

            <!-- Document Column -->
            <el-table-column prop="document" label="Document" sortable :filterable="true"></el-table-column>

            <!-- Subjects Column -->
            <el-table-column label="Subjects" width="200" sortable :filterable="true">
                <template v-slot="{ row }">
                    <div v-if="row.subjects && hasNonNullSubjects(row.subjects)" class="space-y-2">
                        <div v-for="(subject, index) in row.subjects" :key="index" class="whitespace-nowrap">
                            {{ subject.subject_name }}
                        </div>
                    </div>
                    <span v-else class="text-gray-500">No assigned subjects.</span>
                </template>
            </el-table-column>

            <!-- Professor Column -->
            <el-table-column label="Professor" width="200" sortable :filterable="true">
                <template v-slot="{ row }">
                    <div v-if="row.subjects && hasNonNullSubjects(row.subjects)" class="space-y-2">
                        <div v-for="(subject, index) in row.subjects" :key="index" class="whitespace-nowrap">
                            <span v-if="subject.professor_first_name !== null && subject.professor_last_name !== null">
                                {{ subject.professor_first_name }} {{ subject.professor_last_name }}
                            </span>
                            <span v-else class="text-gray-500">No assigned professor.</span>
                            <br />
                        </div>
                    </div>
                    <span v-else class="text-gray-500">No assigned subjects.</span>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
const assignments = ref([]);
const search = ref('');

const headerRowClassName = ({ row, rowIndex }) => {
    return 'bg-gray-200'; // Cambia 'bg-gray-200' al color que desees
};

onMounted(async () => {
    // Llamar a la API para obtener los datos de los estudiantes y sus asignaturas
    const response = await axios.get('/assignments-report');
    assignments.value = response.data;
});

// Función para verificar si todas las propiedades en los elementos del array subjects son diferentes de null
const hasNonNullSubjects = (subjects) => {
    return subjects && subjects.every(subject =>
        subject.subject_name !== null &&
        subject.professor_first_name !== null &&
        subject.professor_last_name !== null
    );
};

// Función de filtro personalizada para la columna Professor
const filterProfessor = (value, row) => {
    return (
        (row.subjects || []).some(subject =>
            subject.professor_first_name.toLowerCase().includes(value.toLowerCase()) ||
            subject.professor_last_name.toLowerCase().includes(value.toLowerCase())
        )
    );
};

// Filtrar asignaciones basadas en la búsqueda
const filteredAssignments = computed(() => {
    return assignments.value.filter(assignment => {
        const fullNameStudent = `${assignment.first_name} ${assignment.last_name}`.toLowerCase();
        const document = `${assignment.document}`.toLowerCase();
        const subjects = (assignment.subjects || []).map(subject => subject.subject_name).join(' ').toLowerCase();
        const fullNameprofessor = (assignment.subjects || []).map(subject => `${subject.professor_first_name} ${subject.professor_last_name}`).join(' ').toLowerCase();

        return fullNameStudent.includes(search.value.toLowerCase()) ||
            document.includes(search.value.toLowerCase()) ||
            subjects.includes(search.value.toLowerCase()) ||
            fullNameprofessor.includes(search.value.toLowerCase())
    });
});
</script>
