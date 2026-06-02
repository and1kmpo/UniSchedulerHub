<script setup>
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";

defineProps({
    classroom: {
        type: Object,
        required: true,
    },
});

const columns = [
    { key: "day", label: "Day" },
    { key: "time", label: "Time" },
    { key: "subject", label: "Subject" },
    { key: "professor", label: "Professor" },
];
</script>

<template>
    <CrudPageLayout :title="classroom.name" subtitle="Classroom details and assigned schedules">
        <template #actions>
            <div class="flex gap-2">
                <Link :href="route('classrooms.edit', classroom.id)">
                    <BaseButton variant="primary">
                        <i class="fa-solid fa-pen-to-square mr-2" />
                        Edit
                    </BaseButton>
                </Link>

                <Link :href="route('classrooms.index')">
                    <BaseButton variant="secondary">
                        Back
                    </BaseButton>
                </Link>
            </div>
        </template>

        <CrudContainer>
            <div class="grid gap-6 lg:grid-cols-3">
                <SectionCard class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Building
                    </p>

                    <h3 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                        {{ classroom.building?.name || "Unassigned" }}
                    </h3>
                </SectionCard>

                <SectionCard class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Capacity
                    </p>

                    <h3 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                        {{ classroom.capacity }} students
                    </h3>
                </SectionCard>

                <SectionCard class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Status
                    </p>

                    <div class="mt-3">
                        <StatusBadge :label="classroom.status"
                            :variant="classroom.status === 'active' ? 'success' : 'gray'" />
                    </div>
                </SectionCard>
            </div>

            <SectionCard class="mt-6 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Description
                </h3>

                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    {{ classroom.description || "No description registered." }}
                </p>
            </SectionCard>

            <SectionCard class="mt-6 p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Scheduled Classes
                </h3>

                <DataTable v-if="classroom.schedules?.length" :columns="columns" :rows="classroom.schedules">
                    <template #cell-time="{ row }">
                        {{ row.start_time }} - {{ row.end_time }}
                    </template>

                    <template #cell-subject="{ row }">
                        {{ row.class_group?.subject?.name || "No subject" }}
                    </template>

                    <template #cell-professor="{ row }">
                        {{ row.class_group?.professor?.name || "No professor" }}
                    </template>
                </DataTable>

                <p v-else class="text-sm text-gray-500 dark:text-gray-400">
                    No schedules assigned to this classroom.
                </p>
            </SectionCard>
        </CrudContainer>
    </CrudPageLayout>
</template>
