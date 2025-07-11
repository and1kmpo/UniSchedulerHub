<template>
    <AppLayout title="Dashboard">
        <!-- Encabezado principal -->
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h1>
        </template>

        <!-- Contenido principal -->
        <div class="py-12 bg-gray-100 dark:bg-gray-900 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Reporte de asignaciones -->
                <div
                    class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                    <AssignmentsReport />
                </div>

                <!-- Grid de estadísticas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-8">
                    <!-- Gráfico de asignaciones -->
                    <div
                        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-x-auto">
                        <div class="w-full">
                            <BarChart />
                        </div>
                    </div>

                    <!-- Estudiantes por programa -->
                    <div
                        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-x-auto">
                        <div class="w-full">
                            <StudentsPerProgram />
                        </div>
                    </div>

                    <!-- Porcentaje de asignaturas optativas -->
                    <div
                        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                        <PercentageElectiveSubjects />
                    </div>

                    <!-- Estudiantes por semestre -->
                    <div
                        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                        <StudentsPerSemester />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AssignmentsReport from '@/Components/Dashboard/AssignmentsReport.vue';
import BarChart from '@/Components/Dashboard/ChartComponent.vue';
import StudentsPerProgram from '@/Components/Dashboard/StudentsPerProgram.vue';
import PercentageElectiveSubjects from '@/Components/Dashboard/PercentageElectiveSubjects.vue';
import StudentsPerSemester from '@/Components/Dashboard/StudentsPerSemester.vue';
</script>
