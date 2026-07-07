<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

import ShowSection from "@/Components/UI/Show/ShowSection.vue";
import InfoGrid from "@/Components/UI/Show/InfoGrid.vue";
import InfoItem from "@/Components/UI/Show/InfoItem.vue";
import StatsGrid from "@/Components/UI/Show/StatsGrid.vue";
import RelatedSection from "@/Components/UI/Show/RelatedSection.vue";
import { formatDate } from "@/Components/Composables/useDateTimeFormatter";

const props = defineProps({
    building: {
        type: Object,
        required: true,
    },

    classrooms: {
        type: Object,
        required: true,
    },
});

const columns = [
    { key: "name", label: "Classroom" },
    { key: "floor", label: "Floor" },
    { key: "capacity", label: "Capacity" },
    { key: "status", label: "Status" },
    { key: "schedules_count", label: "Schedules" },
];

const rows = computed(() =>
    props.classrooms.data.map((classroom) => ({
        id: classroom.id,
        name: classroom.name,
        floor: classroom.floor ?? "N/A",
        capacity: classroom.capacity,
        status: classroom.status,
        schedules_count: classroom.schedules_count,
    }))
);
</script>

<template>
    <CrudPageLayout :title="building.name" subtitle="Building details and classroom inventory">
        <template #actions>
            <Link :href="route('buildings.edit', building.id)">
                <BaseButton variant="primary">
                    <i class="fa-solid fa-pen mr-2"></i>
                    Edit Building
                </BaseButton>
            </Link>

            <Link :href="route('buildings.index')">
                <BaseButton variant="secondary">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back
                </BaseButton>
            </Link>
        </template>

        <CrudContainer class="space-y-6">

            <StatsGrid>

                <StatCard title="Classrooms" :value="building.classrooms_count" icon="fa-solid fa-door-open" />

                <StatCard title="Code" :value="building.code" icon="fa-solid fa-barcode" />

                <StatCard title="Created" :value="formatDate(building.created_at)"
                    icon="fa-solid fa-calendar-days" />

                <StatCard title="Updated" :value="formatDate(building.updated_at)"
                    icon="fa-solid fa-clock" />

            </StatsGrid>

            <ShowSection title="General Information" description="Basic building information">
                <InfoGrid>

                    <InfoItem label="Building Name" :value="building.name" />

                    <InfoItem label="Code" :value="building.code" />

                    <InfoItem label="Description" :value="building.description || 'No description available'"
                        class="md:col-span-2 xl:col-span-3" />

                </InfoGrid>
            </ShowSection>

            <RelatedSection title="Classrooms" description="Classrooms associated with this building">

                <DataTable v-if="rows.length" :columns="columns" :rows="rows">

                    <template #cell-status="{ value }">
                        <StatusBadge :label="value ? value.toUpperCase() : 'N/A'" :variant="{
                            available: 'success',
                            unavailable: 'danger',
                            maintenance: 'warning',
                        }[value] || 'gray'" />
                    </template>

                    <template #actions="{ row }">
                        <Link :href="route('classrooms.show', row.id)">
                            <BaseButton variant="secondary" size="sm">
                                View
                            </BaseButton>
                        </Link>
                    </template>

                </DataTable>

                <EmptyState v-else title="No classrooms assigned"
                    description="This building does not have classrooms yet." icon="fa-solid fa-door-open" />

                <TablePagination v-if="rows.length" :data="classrooms" />

            </RelatedSection>

        </CrudContainer>
    </CrudPageLayout>
</template>
