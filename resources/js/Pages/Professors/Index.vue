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
    professors: {
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
        label: "Professor",
    },

    {
        key: "document",
        label: "Document",
        sortable: true,
    },

    {
        key: "email",
        label: "Email",
    },

    {
        key: "phone",
        label: "Phone",
    },

    {
        key: "subjects_count",
        label: "Capabilities",
    },

    {
        key: "class_groups_count",
        label: "Groups",
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
            route("professors.index"),
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

const deleteProfessor = async (professor) => {

    const confirmed = await confirm(
        `This will permanently delete "${professor.user?.name ?? 'this professor'}"`,
        "Delete Professor"
    );

    if (!confirmed) return;

    router.delete(
        route("professors.destroy", professor.id),
        {
            preserveScroll: true,

            onSuccess: (page) => {

                success(
                    page.props.flash?.success ||
                    "Professor deleted successfully"
                );
            },

            onError: () => {

                error(
                    "Failed to delete professor"
                );
            },
        }
    );
};
</script>

<template>
    <CrudPageLayout title="Professors" subtitle="Manage university professors">
        <template #actions>

            <Link :href="route('professors.create')">

                <BaseButton variant="primary">

                    <i class="fa-solid fa-plus mr-2"></i>

                    Create Professor

                </BaseButton>

            </Link>

        </template>

        <CrudContainer>

            <!-- TOOLBAR -->

            <TableToolbar>

                <template #search>

                    <div class="w-full lg:max-w-sm">

                        <TableSearch v-model="filterForm.search" placeholder="Search professors..." />

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

            <DataTable v-if="professors.data.length" :columns="columns" :rows="professors.data" :filters="filters"
                sortable>

                <template #cell-name="{ row }">

                    {{ row.user?.name ?? "-" }}

                </template>

                <template #cell-email="{ row }">

                    {{ row.user?.email ?? "-" }}

                </template>

                <template #actions="{ row }">

                    <div class="flex items-center justify-center gap-2">

                        <Link :href="route('professors.show', row.id)">

                            <TableActionButton icon="fa-solid fa-eye" color="sky" />

                        </Link>

                        <Link :href="route('professors.edit', row.id)">

                            <TableActionButton icon="fa-solid fa-pen" color="indigo" />

                        </Link>

                        <TableActionButton icon="fa-solid fa-trash" color="red" @click="deleteProfessor(row)" />

                    </div>

                </template>

            </DataTable>

            <!-- EMPTY STATE -->

            <EmptyState v-else title="No professors found" description="Create your first professor to begin."
                icon="fa-solid fa-user-tie">

                <Link :href="route('professors.create')">

                    <BaseButton variant="primary">

                        <i class="fa-solid fa-plus mr-2"></i>

                        Create Professor

                    </BaseButton>

                </Link>

            </EmptyState>

            <!-- PAGINATION -->

            <TablePagination v-if="professors.data.length" :data="professors" />

        </CrudContainer>

    </CrudPageLayout>
</template>
