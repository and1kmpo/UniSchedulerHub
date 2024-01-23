<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Definir el estado para almacenar los datos del reporte
const assignments = ref([]);

// Consultar datos al backend al cargar el componente
onMounted(async () => {
    try {
        const response = await axios.get('/assignments-report');
        console.log(response)
        assignments.value = response.data;
    } catch (error) {
        console.error('Error fetching assignments report:', error);
    }
});
</script>

<template>
    <div>
        <h2>Asignaturas por Estudiante</h2>
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Asignaturas</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="assignment in assignments" :key="assignment.student_id">
                    <td>
                        {{ assignment.first_name + " " + assignment.last_name }}
                    </td>
                    <td>
                        <ul v-if="assignment.subjects.length > 0">
                            <li v-for="subject in assignment.subjects" :key="subject.subject_name">
                                {{ subject.subject_name }}
                                <span v-if="subject.professor_first_name && subject.professor_last_name">
                                    (Profesor: {{ subject.professor_first_name }} {{ subject.professor_last_name }})
                                </span>
                                <span v-else>(No tiene asignaturas asignadas)</span>
                            </li>
                        </ul>
                        <span v-else>No tiene asignaturas asignadas.</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
  