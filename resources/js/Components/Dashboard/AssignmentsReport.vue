<template>
    <div class="space-y-8">
        <!-- Título -->
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                Assignments Report
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Search and view the details of assignments.
            </p>
        </div>

        <!-- Input de búsqueda -->
        <div class="flex justify-center">
            <div class="relative w-full max-w-lg">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" v-model="searchQuery"
                    class="block w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                    placeholder="Search by students, subjects, or professors..." />
                <div v-if="typing || loading" class="absolute right-3 top-2">
                    <i class="fa-regular fa-keyboard fa-beat-fade"></i>
                </div>
            </div>
        </div>

        <!-- Tabla con spinner -->
        <div class="flex justify-center">
            <div class="w-full max-w-5xl bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 relative">
                <!-- Spinner de carga -->
                <div v-if="loading"
                    class="absolute inset-0 flex justify-center items-center bg-white bg-opacity-80 z-10">
                    <i class="fa-solid fa-spinner fa-spin-pulse text-3xl text-blue-500"></i>
                </div>
                <!-- Tabla de datos -->
                <el-table v-else :data="assignments.data" border stripe
                    header-row-class-name="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-semibold"
                    row-class-name="hover:bg-gray-50 dark:hover:bg-gray-800">
                    <el-table-column prop="nameStudent" label="Student" sortable align="center" header-align="center">
                        <template #default="{ row }">
                            <span class="text-gray-900 font-medium dark:text-gray-200">
                                {{ row.nameStudent }}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="document" label="Document" sortable align="center" header-align="center">
                        <template #default="{ row }">
                            <span class="text-gray-700 dark:text-gray-400">
                                {{ row.document }}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column label="Subjects" align="center" header-align="center">
                        <template #default="{ row }">
                            <div>
                                <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                                    <li v-for="subject in row.subjects" :key="subject.subjectName" class="py-2">
                                        {{ subject.subjectName }}
                                    </li>
                                </ul>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="Professors" align="center" header-align="center">
                        <template #default="{ row }">
                            <div>
                                <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                                    <li v-for="subject in row.subjects" :key="subject.subjectName" class="py-2">
                                        {{ subject.professorName || "No professor assigned" }}
                                    </li>
                                </ul>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </div>

        <!-- Paginación -->
        <div class="mt-4 flex flex-wrap justify-center items-center gap-2">
            <button @click="changePage(assignments.prev_page_url)" :disabled="!assignments.prev_page_url"
                class="px-4 py-2 border rounded bg-blue-600 text-white disabled:bg-gray-400 disabled:cursor-not-allowed transition">
                <i class="fas fa-angles-left"></i>
            </button>
            <span class="px-4 py-2 text-center dark:text-white">
                {{ assignments.current_page }} of {{ assignments.last_page }}
            </span>
            <button @click="changePage(assignments.next_page_url)" :disabled="!assignments.next_page_url"
                class="px-4 py-2 border rounded bg-blue-600 text-white disabled:bg-gray-400 disabled:cursor-not-allowed transition">
                <i class="fas fa-angles-right"></i>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import debounce from "lodash/debounce";

const assignments = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    prev_page_url: null,
    next_page_url: null,
});

const searchQuery = ref("");
const loading = ref(false); // Estado de carga real
const typing = ref(false); // Estado mientras el usuario escribe


const changePage = async (url) => {
    if (!url) return; // Si la URL es nula, no hacemos nada.
    await fetchAssignments(url); // Llama a fetchAssignments con la URL proporcionada.
};

// Función para buscar datos
const fetchAssignments = async (url = "/assignments-report") => {
    loading.value = true;
    try {
        const response = await axios.get(url, {
            params: { search: searchQuery.value },
        });
        assignments.value = response.data;
    } catch (error) {
        console.error("Error fetching assignments:", error);
    } finally {
        loading.value = false;
    }
};

// Debounce para optimizar búsqueda
const debouncedFetchAssignments = debounce(async () => {
    await fetchAssignments();
    typing.value = false; // Ocultar estado de escritura
}, 1000);

// Observa el input y actualiza el estado
watch(searchQuery, () => {
    typing.value = true;
    debouncedFetchAssignments();
});

// Carga inicial
onMounted(fetchAssignments);
</script>

<style scoped>
:deep(.el-table) {
    --el-table-header-bg-color: #f9fafb;
    --el-table-border-color: #d1d5db;
}
</style>
