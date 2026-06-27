<script setup>
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import { formatTime } from "@/Components/Composables/useDateTimeFormatter";

const props = defineProps({
    classroom: {
        type: Object,
        required: true,
    },
});

const columns = [
    { key: "day", label: "Day" },
    { key: "time", label: "Time" },
    { key: "class_group", label: "Class Group" },
    { key: "subject", label: "Subject" },
    { key: "professor", label: "Professor" },
    { key: "status", label: "Status" },
];

const rows = props.classroom.schedules.map((schedule) => ({
    id: schedule.id,
    day: schedule.day,
    time: `${formatTime(schedule.start_time)} - ${formatTime(schedule.end_time)}`,
    class_group: schedule.class_group?.name || schedule.class_group?.code || "Unassigned",
    subject: schedule.class_group?.subject?.name || "Unassigned",
    professor: schedule.class_group?.professor?.name || "Unassigned",
    status: schedule.status || "published",
}));
</script>

<template>
    <CrudPageLayout
        :title="`Classroom Schedule - ${classroom.name}`"
        subtitle="Review classroom occupancy by class group, subject and professor"
    >
        <template #actions>
            <Link :href="route('classrooms.index')">
                <BaseButton variant="secondary">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Classrooms
                </BaseButton>
            </Link>
        </template>

        <CrudContainer>
            <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                <template #cell-day="{ value }">
                    <span class="font-medium capitalize text-ink dark:text-white">
                        {{ value }}
                    </span>
                </template>

                <template #cell-time="{ value }">
                    <span class="font-mono text-sm text-slate-700 dark:text-zinc-300">
                        {{ value }}
                    </span>
                </template>

                <template #cell-class_group="{ value }">
                    <span class="font-medium text-ink dark:text-white">
                        {{ value }}
                    </span>
                </template>

                <template #cell-status="{ value }">
                    <StatusBadge
                        :label="value"
                        :variant="value === 'published' ? 'success' : value === 'draft' ? 'warning' : 'gray'"
                    />
                </template>
            </DataTable>

            <EmptyState
                v-else
                title="No scheduled blocks"
                description="This classroom does not have assigned class schedules yet."
                icon="fa-solid fa-calendar-days"
            />
        </CrudContainer>
    </CrudPageLayout>
</template>

