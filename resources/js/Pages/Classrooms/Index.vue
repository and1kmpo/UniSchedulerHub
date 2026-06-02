<script setup>
import { computed, reactive, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import axios from "axios";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import TableToolbar from "@/Components/UI/Table/TableToolbar.vue";
import TableSearch from "@/Components/UI/Table/TableSearch.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import TableActionButton from "@/Components/UI/Table/TableActionButton.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error, confirm } = useAlert();

const props = defineProps({
    classrooms: {
        type: Object,
        required: true,
    },

    buildings: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
});

const columns = [
    { key: "name", label: "Classroom", sortable: true },
    { key: "building", label: "Building" },
    { key: "floor", label: "Floor", sortable: true },
    { key: "capacity", label: "Capacity", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "schedules_count", label: "Schedules" },
];

const filterForm = reactive({
    search: props.filters.search || "",
    building: props.filters.building || "",
    status: props.filters.status || "",
});

const buildingOptions = computed(() =>
    props.buildings.map((building) => ({
        label: `${building.code} - ${building.name}`,
        value: building.id,
    }))
);

const activeCount = computed(() =>
    props.classrooms.data.filter((classroom) => classroom.status === "active").length
);

const totalCapacity = computed(() =>
    props.classrooms.data.reduce((total, classroom) => total + Number(classroom.capacity || 0), 0)
);

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(
            route("classrooms.index"),
            {
                ...filterForm,
                sort: props.filters.sort,
                direction: props.filters.direction,
                page: 1,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    },
    {
        deep: true,
    }
);

const clearFilters = () => {
    filterForm.search = "";
    filterForm.building = "";
    filterForm.status = "";
};

const destroy = async (classroom) => {
    const confirmed = await confirm(
        `Delete "${classroom.name}"?`,
        "Delete Classroom"
    );

    if (!confirmed) {
        return;
    }

    try {
        await axios.delete(route("classrooms.destroy", classroom.id));

        success("Classroom deleted successfully");

        router.reload();
    } catch (exception) {
        error(exception.response?.data?.message || "Could not delete classroom");
    }
};
</script>

<template>
    <CrudPageLayout title="Classrooms" subtitle="Manage rooms, capacity and schedule availability">
        <template #actions>
            <Link :href="route('classrooms.create')">
                <BaseButton variant="primary">
                    <i class="fa-solid fa-plus mr-2" />
                    Create Classroom
                </BaseButton>
            </Link>
        </template>

        <CrudContainer>
            <div class="mb-6 grid gap-4 md:grid-cols-3">
                <SectionCard class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Total Classrooms
                    </p>

                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ classrooms.total }}
                    </h3>
                </SectionCard>

                <SectionCard class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Active On Page
                    </p>

                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ activeCount }}
                    </h3>
                </SectionCard>

                <SectionCard class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Capacity On Page
                    </p>

                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ totalCapacity }}
                    </h3>
                </SectionCard>
            </div>

            <TableToolbar>
                <template #search>
                    <div class="w-full lg:max-w-sm">
                        <TableSearch v-model="filterForm.search" placeholder="Search classrooms..." />
                    </div>
                </template>

                <template #filters>
                    <div class="w-full sm:max-w-xs">
                        <BaseSelect v-model="filterForm.building" placeholder="Building" :options="buildingOptions" />
                    </div>

                    <div class="w-full sm:max-w-xs">
                        <BaseSelect v-model="filterForm.status" placeholder="Status" :options="[
                            { label: 'Active', value: 'active' },
                            { label: 'Inactive', value: 'inactive' },
                        ]" />
                    </div>
                </template>

                <template #actions>
                    <BaseButton variant="secondary" @click="clearFilters">
                        <i class="fa-solid fa-rotate-left mr-2" />
                        Reset
                    </BaseButton>
                </template>
            </TableToolbar>

            <DataTable v-if="classrooms.data.length" :columns="columns" :rows="classrooms.data" :filters="filters"
                sortable>
                <template #cell-building="{ row }">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ row.building?.name || "Unassigned" }}
                        </p>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ row.building?.code || "No code" }}
                        </p>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <StatusBadge :label="row.status" :variant="row.status === 'active' ? 'success' : 'gray'" />
                </template>

                <template #actions="{ row }">
                    <div class="flex items-center justify-center gap-2">
                        <Link :href="route('classrooms.show', row.id)">
                            <TableActionButton icon="fa-solid fa-eye" label="View classroom" color="sky" />
                        </Link>

                        <Link :href="route('classrooms.edit', row.id)">
                            <TableActionButton icon="fa-solid fa-pen-to-square" label="Edit classroom" color="indigo" />
                        </Link>

                        <Link :href="route('classrooms.schedule', row.id)">
                            <TableActionButton icon="fa-solid fa-calendar" label="View classroom schedule" color="gray" />
                        </Link>

                        <TableActionButton icon="fa-solid fa-trash" label="Delete classroom" color="red" @click="destroy(row)" />
                    </div>
                </template>
            </DataTable>

            <EmptyState v-else title="No classrooms found" description="Create the first classroom to start scheduling."
                icon="fa-solid fa-door-open">
                <Link :href="route('classrooms.create')">
                    <BaseButton variant="primary">
                        <i class="fa-solid fa-plus mr-2" />
                        Create Classroom
                    </BaseButton>
                </Link>
            </EmptyState>

            <TablePagination v-if="classrooms.data.length" :data="classrooms" />
        </CrudContainer>
    </CrudPageLayout>
</template>
