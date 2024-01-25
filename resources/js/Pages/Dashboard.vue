<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Componente de asignaciones -->
                    <div class="mb-8">
                        <AssignmentsReport />
                    </div>

                    <!-- Grid layout -->
                    <div class="grid grid-cols-2 gap-8">

                        <!-- Graphics -->
                        <div>
                            <BarChart />
                        </div>

                        <div>
                            <StudentsPerProgram />
                        </div>

                        <div>
                            <PercentageElectiveSubjects />
                        </div>

                        <div>
                            <StudentsPerSemester />
                        </div>


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
  