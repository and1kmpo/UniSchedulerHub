<script setup>
import { ref, watch } from "vue";
import axios from "axios";

/*
|--------------------------------------------------------------------------
| Scheduler Core
|--------------------------------------------------------------------------
*/

import SchedulerGrid from "./SchedulerGrid.vue";
import GeneratedSchedulePreview from "./GeneratedSchedulePreview.vue";

/*
|--------------------------------------------------------------------------
| Optimization
|--------------------------------------------------------------------------
*/

import OptimizationPanel from "./OptimizationPanel.vue";
import ScheduleScoreCard from "./ScheduleScoreCard.vue";
import ConflictSidebar from "./ConflictSidebar.vue";
import RecommendationPanel from "./RecommendationPanel.vue";

/*
|--------------------------------------------------------------------------
| Generation Engine
|--------------------------------------------------------------------------
*/

import TimetableGeneratorPanel from "./Generation/TimetableGeneratorPanel.vue";
import GenerationRecommendations from "./Generation/GenerationRecommendations.vue";

/*
|--------------------------------------------------------------------------
| Analytics
|--------------------------------------------------------------------------
*/

import AcademicEfficiencyCard from "./Generation/AcademicEfficiencyCard.vue";
import ProfessorBalanceCard from "./Generation/ProfessorBalanceCard.vue";
import ClassroomUsageCard from "./Generation/ClassroomUsageCard.vue";

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    schedules: {
        type: Array,
        default: () => [],
    },
});

/*
|--------------------------------------------------------------------------
| Reactive State
|--------------------------------------------------------------------------
*/

const localSchedules = ref(
    structuredClone(props.schedules)
);

const generatedSchedule = ref([]);

const optimization = ref({
    conflicts: [],

    score: {
        score: 100,
        grade: "Excellent",
        penalties: [],
    },

    classrooms: [],

    professors: [],

    recommendations: [],

    metrics: {
        fragmentation: 0,
        dead_times: 0,
        balance: "Excellent",
    },
});

const generation = ref({
    recommendations: [],
    metrics: {},
});

const loading = ref(false);

const generating = ref(false);

/*
|--------------------------------------------------------------------------
| Optimization Engine
|--------------------------------------------------------------------------
*/

const optimize = async () => {

    loading.value = true;

    try {

        const response = await axios.post(
            route("smart-scheduler.optimize"),
            {
                schedules: localSchedules.value,
            }
        );

        optimization.value = response.data;

    } catch (e) {

        console.error(e);

    } finally {

        loading.value = false;
    }
};

/*
|--------------------------------------------------------------------------
| AI Timetable Generator
|--------------------------------------------------------------------------
*/

const generateTimetable = async (payload) => {

    generating.value = true;

    try {

        const response = await axios.post(
            route("smart-scheduler.generate"),
            payload
        );

        generatedSchedule.value =
            response.data.schedule;

        generation.value = {
            recommendations:
                response.data.recommendations ?? [],

            metrics:
                response.data.metrics ?? {},
        };

        /*
        |--------------------------------------------------------------------------
        | Inject generated schedule
        |--------------------------------------------------------------------------
        */

        localSchedules.value =
            response.data.schedule;

    } catch (e) {

        console.error(e);

    } finally {

        generating.value = false;
    }
};

/*
|--------------------------------------------------------------------------
| Live Optimization
|--------------------------------------------------------------------------
*/

watch(
    localSchedules,
    () => optimize(),
    {
        deep: true,
        immediate: true,
    }
);

/*
|--------------------------------------------------------------------------
| Scheduler Updates
|--------------------------------------------------------------------------
*/

const updateSchedules = (updated) => {

    localSchedules.value = updated;
};
</script>

<template>

    <div class="space-y-6">

        <!-- HEADER -->

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Smart Scheduler
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    AI-like academic timetable optimization engine
                </p>
            </div>

            <!-- GENERATOR -->

            <TimetableGeneratorPanel :loading="generating" @generate="generateTimetable" />

        </div>

        <!-- SCORE -->

        <ScheduleScoreCard :score="optimization.score" />

        <!-- ANALYTICS -->

        <div class="grid gap-6 lg:grid-cols-3">

            <AcademicEfficiencyCard :metrics="generation.metrics" />

            <ProfessorBalanceCard :professors="optimization.professors" />

            <ClassroomUsageCard :classrooms="optimization.classrooms" />

        </div>

        <!-- MAIN CONTENT -->

        <div class="grid gap-6 lg:grid-cols-4">

            <!-- MAIN GRID -->

            <div class="lg:col-span-3 space-y-6">

                <!-- LIVE GRID -->

                <SchedulerGrid :schedules="localSchedules" :conflicts="optimization.conflicts"
                    @update="updateSchedules" />

                <!-- GENERATED PREVIEW -->

                <GeneratedSchedulePreview :schedule="generatedSchedule" />

            </div>

            <!-- SIDEBAR -->

            <div class="space-y-6">

                <!-- OPTIMIZATION -->

                <OptimizationPanel :classrooms="optimization.classrooms" :professors="optimization.professors" />

                <!-- CONFLICTS -->

                <ConflictSidebar :conflicts="optimization.conflicts" />

                <!-- RECOMMENDATIONS -->

                <RecommendationPanel :recommendations="optimization.recommendations
                    " />

                <!-- AI RECOMMENDATIONS -->

                <GenerationRecommendations :recommendations="generation.recommendations
                    " />

            </div>

        </div>

    </div>

</template>