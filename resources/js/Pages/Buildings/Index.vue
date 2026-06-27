<script setup>
import { reactive, watch } from "vue";

import {
    Link,
    router,
} from "@inertiajs/vue3";

import { route } from "ziggy-js";

import { useAlert } from "@/Components/Composables/useAlert";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import TableToolbar from "@/Components/UI/Table/TableToolbar.vue";
import TableSearch from "@/Components/UI/Table/TableSearch.vue";

import DataTable from "@/Components/UI/Table/DataTable.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import TableActionButton from "@/Components/UI/Table/TableActionButton.vue";

import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";

const { confirm, success, error } = useAlert();

const props = defineProps({
    buildings: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
});

const columns = [
    {
        key: "name",
        label: "Building",
        sortable: true,
    },

    {
        key: "code",
        label: "Code",
        sortable: true,
    },

    {
        key: "description",
        label: "Description",
    },

    {
        key: "classrooms_count",
        label: "Classrooms",
    },
];

const filterForm = reactive({
    search: props.filters.search || "",
});

watch(
    () => ({
        ...filterForm,
    }),
    () => {

        router.get(
            route("buildings.index"),
            {
                search: filterForm.search,
                sort: props.filters?.sort,
                direction: props.filters?.direction,
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
};

const deleteBuilding = async (building) => {

    const confirmed = await confirm(
        `This will permanently delete "${building.name}"`,
        "Delete Building"
    );

    if (!confirmed) return;

    router.delete(
        route("buildings.destroy", building.id),
        {
            preserveScroll: true,

            onSuccess: (page) => {

                success(
                    page.props.flash?.success ||
                    "Building deleted successfully"
                );
            },

            onError: () => {

                error(
                    "Failed to delete building"
                );
            },
        }
    );
};
</script>

<template>
    <CrudPageLayout title="Buildings" subtitle="Manage university infrastructure buildings">
        <template #actions>

            <Link :href="route('buildings.create')">

                <BaseButton variant="primary">

                    <i class="fa-solid fa-plus mr-2"></i>

                    Create Building

                </BaseButton>

            </Link>

        </template>

        <CrudContainer>

            <!-- TOOLBAR -->

            <TableToolbar>

                <template #search>

                    <div class="w-full lg:max-w-sm">

                        <TableSearch v-model="filterForm.search" placeholder="Search buildings..." />

                    </div>

                </template>

                <template #actions>

                    <BaseButton variant="secondary" @click="clearFilters">
                        <i class="fa-solid fa-rotate-left mr-2"></i>

                        Reset

                    </BaseButton>

                </template>

            </TableToolbar>

            <!-- TABLE -->

            <DataTable v-if="buildings.data.length" :columns="columns" :rows="buildings.data" :filters="filters"
                sortable>
                <template #actions="{ row }">

                    <div class="flex items-center justify-center gap-2">

                        <Link :href="route('buildings.show', row.id)">
                            <TableActionButton icon="fa-solid fa-eye" label="View building" color="sky" />
                        </Link>

                        <Link :href="route('buildings.edit', row.id)">
                            <TableActionButton icon="fa-solid fa-pen" label="Edit building" color="brand" />
                        </Link>

                        <TableActionButton icon="fa-solid fa-trash" label="Delete building" color="red" @click="deleteBuilding(row)" />

                    </div>

                </template>

            </DataTable>

            <!-- EMPTY STATE -->

            <EmptyState v-else title="No buildings found" description="Create your first building to begin."
                icon="fa-solid fa-building">
                <Link :href="route('buildings.create')">

                    <BaseButton variant="primary">

                        <i class="fa-solid fa-plus mr-2"></i>

                        Create Building

                    </BaseButton>

                </Link>

            </EmptyState>

            <!-- PAGINATION -->

            <TablePagination v-if="buildings.data.length" :data="buildings" />

        </CrudContainer>

    </CrudPageLayout>
</template>

