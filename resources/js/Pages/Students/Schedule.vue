<script setup>
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import WeeklySchedule from "@/Pages/Students/Partials/WeeklySchedule.vue";

defineProps({
    currentSchedules: {
        type: Array,
        default: () => [],
    },
    currentPeriod: {
        type: Object,
        default: null,
    },
});

function formatStatus(status) {
    return status ? status.replaceAll("_", " ").toUpperCase() : "NOT ACTIVE";
}
</script>

<template>
    <CrudPageLayout title="My Schedule" subtitle="Weekly class schedule for the active academic period">
        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <StatCard title="Scheduled Blocks" :value="currentSchedules.length" icon="fa-solid fa-calendar-week" />
                    <StatCard title="Period" :value="currentPeriod?.name ?? '-'" icon="fa-solid fa-calendar-days" />
                    <div class="rounded-lg border border-border-light bg-surface p-6 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">Status</p>
                        <div class="mt-4">
                            <StatusBadge
                                :label="formatStatus(currentPeriod?.state)"
                                :variant="currentPeriod ? 'success' : 'gray'"
                            />
                        </div>
                    </div>
                </section>

                <SectionCard>
                    <div class="border-b border-border-light p-6 dark:border-border-dark">
                        <h2 class="text-lg font-semibold text-ink dark:text-white">
                            Academic Week
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                            This read-only view shows your active enrolled and pre-enrolled class blocks.
                        </p>
                    </div>

                    <div class="p-6">
                        <WeeklySchedule :schedules="currentSchedules" />
                    </div>
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>


