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
import TableActionButton from "@/Components/UI/Table/TableActionButton.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";

import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

const { confirm, success, error } = useAlert();

const props = defineProps({
    subjects: {
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
        label: "Subject",
        sortable: true,
    },

    {
        key: "description",
        label: "Description",
    },

    {
        key: "credits",
        label: "Credits",
        sortable: true,
    },

    {
        key: "knowledge_area",
        label: "Knowledge Area",
        sortable: true,
    },

    {
        key: "elective",
        label: "Elective",
    },
];

const filterForm = reactive({
    search: props.filters.search || "",
    elective: props.filters.elective || "",
});

watch(
    () => ({
        ...filterForm,
    }),
    () => {
        router.get(
            route("subjects.index"),
            {
                search: filterForm.search,
                elective: filterForm.elective,
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
    filterForm.elective = "";
};

const deleteSubject = async (subject) => {

    const confirmed = await confirm(
        `This will permanently delete "${subject.name}"`,
        "Delete Subject"
    );

    if (!confirmed) return;

    router.delete(
        route("subjects.destroy", subject.id),
        {
            preserveScroll: true,

            onSuccess: (page) => {

                success(
                    page.props.flash?.success ||
                    "Subject deleted successfully"
                );
            },

            onError: () => {

                error(
                    "Failed to delete subject"
                );
            },
        }
    );
};
</script>

<template>
    <CrudPageLayout title="Subjects" subtitle="Manage university subjects">

        <template #actions>

            <Link :href="route('subjects.create')">

                <BaseButton variant="primary">

                    <i class="fa-solid fa-plus mr-2"></i>

                    Create Subject

                </BaseButton>

            </Link>

        </template>

        <CrudContainer>

            <!-- TOOLBAR -->

            <TableToolbar>

                <template #search>

                    <div class="w-full lg:max-w-sm">

                        <TableSearch v-model="filterForm.search" placeholder="Search subjects..." />

                    </div>

                </template>

                <template #filters>

                    <div class="w-full sm:max-w-xs">

                        <BaseSelect v-model="filterForm.elective" placeholder="Elective status" :options="[
                            {
                                label: 'Elective',
                                value: 1,
                            },
                            {
                                label: 'Non elective',
                                value: 0,
                            },
                        ]" />

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

            <DataTable v-if="subjects.data.length" :columns="columns" :rows="subjects.data" :filters="filters" sortable>

                <!-- ELECTIVE -->

                <template #cell-elective="{ row }">

                    <StatusBadge :label="row.elective ? 'YES' : 'NO'" :variant="row.elective
                            ? 'success'
                            : 'gray'
                        " />

                </template>

                <!-- ACTIONS -->

                <template #actions="{ row }">

                    <div class="flex items-center justify-center gap-2">

                        <!-- SHOW -->

                        <Link :href="route('subjects.show', row.id)">

                            <TableActionButton icon="fa-solid fa-eye" label="View subject" color="sky" />

                        </Link>

                        <!-- EDIT -->

                        <Link :href="route('subjects.edit', row.id)">

                            <TableActionButton icon="fa-solid fa-pen" label="Edit subject" color="brand" />

                        </Link>

                        <!-- DELETE -->

                        <TableActionButton icon="fa-solid fa-trash" label="Delete subject" color="red" @click="deleteSubject(row)" />

                    </div>

                </template>

            </DataTable>

            <!-- EMPTY STATE -->

            <EmptyState v-else title="No subjects found" description="Create your first subject to begin."
                icon="fa-solid fa-book">

                <Link :href="route('subjects.create')">

                    <BaseButton variant="primary">

                        <i class="fa-solid fa-plus mr-2"></i>

                        Create Subject

                    </BaseButton>

                </Link>

            </EmptyState>

            <!-- PAGINATION -->

            <TablePagination v-if="subjects.data.length" :data="subjects" />

        </CrudContainer>

    </CrudPageLayout>
</template>


