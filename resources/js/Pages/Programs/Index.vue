<script setup>
import { reactive, watch } from "vue";

import {
    Link,
    router,
} from "@inertiajs/vue3";

import { route } from "ziggy-js";

import { useAlert } from "@/Components/Composables/useAlert";
import { formatDate } from "@/Components/Composables/useDateTimeFormatter";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import TableToolbar from "@/Components/UI/Table/TableToolbar.vue";
import TableSearch from "@/Components/UI/Table/TableSearch.vue";

import DataTable from "@/Components/UI/Table/DataTable.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import TableActionButton from "@/Components/UI/Table/TableActionButton.vue";

import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";

const { confirm, success, error } = useAlert();

const props = defineProps({
    programs: {
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
        label: "Program",
        sortable: true,
    },

    {
        key: "description",
        label: "Description",
    },

    {
        key: "created_at",
        label: "Created",
        sortable: true,
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
            route("programs.index"),
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

const deleteProgram = async (program) => {

    const confirmed = await confirm(
        `This will permanently delete "${program.name}"`,
        "Delete Program"
    );

    if (!confirmed) return;

    router.delete(
        route("programs.destroy", program.id),
        {
            preserveScroll: true,

            onSuccess: (page) => {

                success(
                    page.props.flash?.success ||
                    "Program deleted successfully"
                );
            },

            onError: () => {

                error(
                    "Failed to delete program"
                );
            },
        }
    );
};
</script>

<template>
    <CrudPageLayout title="Programs" subtitle="Manage academic programs">
        <template #actions>

            <Link :href="route('programs.create')">

                <BaseButton variant="primary">

                    <i class="fa-solid fa-plus mr-2" />

                    Create Program

                </BaseButton>

            </Link>

        </template>

        <CrudContainer>

            <!-- TOOLBAR -->

            <TableToolbar>

                <template #search>

                    <div class="w-full lg:max-w-sm">

                        <TableSearch v-model="filterForm.search" placeholder="Search programs..." />

                    </div>

                </template>

                <template #actions>

                    <BaseButton variant="secondary" @click="clearFilters">
                        <i class="fa-solid fa-rotate-left mr-2" />

                        Reset

                    </BaseButton>

                </template>

            </TableToolbar>

            <!-- TABLE -->

            <DataTable v-if="programs.data.length" :columns="columns" :rows="programs.data" :filters="filters" sortable>
                <template #cell-created_at="{ value }">

                    <StatusBadge :label="formatDate(value)" variant="gray" />

                </template>

                <template #actions="{ row }">

                    <div class="flex items-center justify-center gap-2">

                        <Link :href="route('programs.show', row.id)">
                            <TableActionButton icon="fa-solid fa-eye" label="View program" color="sky" />
                        </Link>

                        <Link :href="route('programs.edit', row.id)">
                            <TableActionButton icon="fa-solid fa-pen" label="Edit program" color="brand" />
                        </Link>

                        <TableActionButton icon="fa-solid fa-trash" label="Delete program" color="red" @click="deleteProgram(row)" />

                    </div>

                </template>

            </DataTable>

            <!-- EMPTY STATE -->

            <EmptyState v-else title="No programs found" description="Create your first academic program to begin."
                icon="fa-solid fa-graduation-cap">

                <Link :href="route('programs.create')">

                    <BaseButton variant="primary">

                        <i class="fa-solid fa-plus mr-2" />

                        Create Program

                    </BaseButton>

                </Link>

            </EmptyState>

            <!-- PAGINATION -->

            <TablePagination v-if="programs.data.length" :data="programs" />

        </CrudContainer>

    </CrudPageLayout>
</template>

