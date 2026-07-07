<script setup>
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import WeeklySchedule from "@/Pages/Students/Partials/WeeklySchedule.vue";
import { useTranslations } from "@/Components/Composables/useTranslations";

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

const { t } = useTranslations();

function formatStatus(status) {
    return status ? status.replaceAll("_", " ").toUpperCase() : t("common.not_active");
}
</script>

<template>
    <CrudPageLayout
        :title="t('student_portal.my_schedule_title')"
        :subtitle="t('student_portal.my_schedule_subtitle')"
    >
        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <StatCard :title="t('student_portal.scheduled_blocks')" :value="currentSchedules.length" icon="fa-solid fa-calendar-week" />
                    <StatCard :title="t('student_portal.period')" :value="currentPeriod?.name ?? '-'" icon="fa-solid fa-calendar-days" />
                    <div class="rounded-lg border border-border-light bg-surface p-6 shadow-sm dark:border-border-dark dark:bg-surface-dark">
                        <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">{{ t("common.status") }}</p>
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
                            {{ t("student_portal.academic_week") }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                            {{ t("student_portal.academic_week_description") }}
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


