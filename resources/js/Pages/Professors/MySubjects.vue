<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import { formatTime } from "@/Components/Composables/useDateTimeFormatter";
import { useTranslations } from "@/Components/Composables/useTranslations";

const props = defineProps({
    groups: {
        type: Array,
        default: () => [],
    },

    period: {
        type: Object,
        default: null,
    },

    summary: {
        type: Object,
        default: () => ({
            groups: 0,
            students: 0,
            credits: 0,
        }),
    },

    systemState: {
        type: String,
        default: "ready",
    },
});

const { t } = useTranslations();

const columns = computed(() => [
    { key: "subject", label: t("common.subject") },
    { key: "code", label: t("common.group") },
    { key: "schedule_summary", label: t("common.schedule") },
    { key: "modality_summary", label: t("professor_portal.mode") },
    { key: "students_summary", label: t("common.students") },
    { key: "grade_summary", label: t("common.grades") },
    { key: "status", label: t("common.status") },
]);

const rows = computed(() =>
    props.groups.map((group) => ({
        id: group.id,
        subject: `${group.subject?.code ?? "N/A"} - ${group.subject?.name ?? "N/A"}`,
        code: group.code ?? group.name,
        schedule_summary: formatSchedules(group.schedules),
        modality_summary: `${formatLabel(group.modality)} / ${formatLabel(group.shift)}`,
        students_summary: `${group.subject_enrollments_count}/${group.capacity}`,
        grade_summary: formatGradeProgress(group.subject_enrollments),
        status: group.status,
        source: group,
    }))
);

function formatDay(day) {
    return day ? day.charAt(0).toUpperCase() + day.slice(1) : "";
}

function formatStatus(status) {
    return status ? status.replaceAll("_", " ").toUpperCase() : t("common.pending");
}

function formatLabel(value) {
    return value ? value.replaceAll("_", " ").toUpperCase() : t("common.tbd");
}

function formatSchedules(schedules = []) {
    if (!schedules.length) {
        return t("common.pending");
    }

    return schedules
        .map((schedule) => {
            const room = (schedule.classroom_location || schedule.classroom)
                ? ` - ${schedule.classroom_location || schedule.classroom}`
                : "";

            return `${formatDay(schedule.day)} ${formatTime(schedule.start_time)}-${formatTime(schedule.end_time)}${room}`;
        })
        .join("; ");
}

function formatGradeProgress(enrollments = []) {
    if (!enrollments.length) {
        return t("professor_portal.no_students");
    }

    const graded = enrollments.filter((enrollment) =>
        enrollment.grade?.final_grade !== null && enrollment.grade?.final_grade !== undefined
    ).length;

    return t("professor_portal.graded_count", null, { count: `${graded}/${enrollments.length}` });
}

const groupStatusVariant = (status) => ({
    draft: "warning",
    published: "success",
    cancelled: "danger",
    closed: "gray",
}[status] || "gray");
</script>

<template>
    <CrudPageLayout :title="t('professor_portal.my_groups_title')" :subtitle="period
        ? t('professor_portal.my_groups_subtitle_with_period', null, { period: period.name })
        : t('professor_portal.my_groups_subtitle')
        ">
        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <StatCard :title="t('professor_portal.assigned_groups')" :value="summary.groups" icon="fa-solid fa-users-rectangle" />
                    <StatCard :title="t('professor_portal.active_students')" :value="summary.students" icon="fa-solid fa-user-graduate" />
                    <StatCard :title="t('professor_portal.credits_assigned')" :value="summary.credits" icon="fa-solid fa-layer-group" />
                </section>

                <SectionCard>
                    <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-ink dark:text-white">
                                {{ t("professor_portal.current_period") }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                                {{ period?.name ?? t("professor_portal.no_active_period") }}
                            </p>
                        </div>

                        <StatusBadge :label="period?.state ? formatStatus(period.state) : t('common.not_active')"
                            :variant="period?.can_edit_grades ? 'success' : 'gray'" />
                    </div>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-border-light p-6 dark:border-border-dark">
                        <h2 class="text-lg font-semibold text-ink dark:text-white">
                            {{ t("professor_portal.assigned_groups_section") }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                            {{ t("professor_portal.assigned_groups_description") }}
                        </p>
                    </div>

                    <div class="p-6">
                        <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                            <template #cell-schedule_summary="{ value }">
                                <span class="block max-w-md whitespace-normal text-sm text-slate-700 dark:text-zinc-300">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-modality_summary="{ value }">
                                <span class="text-sm text-slate-700 dark:text-zinc-300">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-students_summary="{ value }">
                                <span class="font-medium text-ink dark:text-white">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-grade_summary="{ value }">
                                <span class="text-sm text-slate-700 dark:text-zinc-300">
                                    {{ value }}
                                </span>
                            </template>

                            <template #cell-status="{ value }">
                                <StatusBadge :label="formatStatus(value)" :variant="groupStatusVariant(value)" />
                            </template>

                            <template #actions="{ row }">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <Link :href="route('admin.class-groups.enrollments', row.id)">
                                        <BaseButton size="sm" variant="secondary">
                                            <i class="fa-solid fa-users mr-2" />
                                            {{ t("professor_portal.roster") }}
                                        </BaseButton>
                                    </Link>

                                    <Link v-if="row.source.can_view_grades" :href="route('groups.grades.index', row.id)">
                                        <BaseButton size="sm" :variant="row.source.can_edit_grades ? 'primary' : 'secondary'">
                                            <i class="fa-solid fa-clipboard-list mr-2" />
                                            {{ row.source.can_edit_grades ? t("common.grades") : t("professor_portal.view_grades") }}
                                        </BaseButton>
                                    </Link>

                                    <BaseButton v-else size="sm" variant="secondary" disabled>
                                        <i class="fa-solid fa-lock mr-2" />
                                        {{ t("professor_portal.grades_unavailable") }}
                                    </BaseButton>
                                </div>
                            </template>
                        </DataTable>

                        <EmptyState v-else :title="t('professor_portal.no_groups_title')"
                            :description="systemState === 'no_period'
                                ? t('professor_portal.no_period_description')
                                : t('professor_portal.no_groups_description')"
                            icon="fa-solid fa-users-rectangle" />
                    </div>
                </SectionCard>
            </div>
        </CrudContainer>

    </CrudPageLayout>
</template>


