<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

defineProps({
    schedules: Array,
});
</script>

<template>

    <SectionCard class="p-6">

        <div class="mb-6 flex items-center justify-between">

            <div>

                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                    Weekly Schedule
                </h3>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Group academic distribution
                </p>

            </div>

        </div>

        <div class="space-y-4">

            <div v-for="schedule in schedules" :key="`${schedule.day}-${schedule.start_time}`
                " class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-950">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="font-semibold capitalize text-gray-950 dark:text-white">
                            {{
                                schedule.day
                            }}
                        </p>

                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{
                                schedule.start_time
                            }}
                            -
                            {{
                                schedule.end_time
                            }}
                        </p>

                    </div>

                    <div
                        class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                        Scheduled
                    </div>

                </div>

            </div>

        </div>

    </SectionCard>

</template>
