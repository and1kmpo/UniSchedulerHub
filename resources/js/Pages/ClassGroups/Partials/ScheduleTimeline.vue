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

                <h3 class="text-lg font-semibold text-ink dark:text-white">
                    Weekly Schedule
                </h3>

                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Group academic distribution
                </p>

            </div>

        </div>

        <div class="space-y-4">

            <div v-for="schedule in schedules" :key="`${schedule.day}-${schedule.start_time}`
                " class="rounded-lg border border-border-light bg-slate-50 p-4 dark:border-border-dark dark:bg-dark-bg">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="font-semibold capitalize text-ink dark:text-white">
                            {{
                                schedule.day
                            }}
                        </p>

                        <p class="text-sm text-slate-600 dark:text-slate-400">
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
                        class="rounded-full bg-brand-100 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                        Scheduled
                    </div>

                </div>

            </div>

        </div>

    </SectionCard>

</template>
