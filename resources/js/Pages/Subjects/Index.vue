<script setup>
import { reactive, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import { useAlert } from "@/Components/Composables/useAlert";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import CrudFilters from "@/Components/UI/CrudFilters.vue";
import DataTable from "@/Components/UI/DataTable.vue";
import TablePagination from "@/Components/UI/TablePagination.vue";
import EmptyState from "@/Components/UI/EmptyState.vue";

import BaseButton from "@/Components/UI/BaseButton.vue";
import BaseInput from "@/Components/UI/BaseInput.vue";
import BaseSelect from "@/Components/UI/BaseSelect.vue";

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
    { key: "name", label: "Subject" },
    { key: "description", label: "Description" },
    { key: "credits", label: "Credits" },
    { key: "knowledge_area", label: "Knowledge Area" },
    { key: "elective", label: "Elective" },
];

const filterForm = reactive({
    search: props.filters.search || "",
    elective: props.filters.elective || "",
});

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(
            route("subjects.index"),
            {
                search: filterForm.search,
                elective: filterForm.elective,
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

    router.delete(route("subjects.destroy", subject.id), {

        preserveScroll: true,

        onSuccess: (page) => {

            success(
                page.props.flash?.success ||
                "Subject deleted successfully"
            );
        },

        onError: () => {

            error("Failed to delete subject");
        },
    });
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

            <!-- FILTERS -->

            <CrudFilters title="Filters">

                <div class="w-full sm:max-w-xs">
                    <BaseInput v-model="filterForm.search" placeholder="Search subject..." />
                </div>

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

                <BaseButton variant="secondary" @click="clearFilters">
                    <i class="fa-solid fa-rotate-left mr-2"></i>
                    Reset
                </BaseButton>

            </CrudFilters>

            <!-- TABLE -->

            <DataTable v-if="subjects.data.length" :columns="columns" :rows="subjects.data">

                <template #cell-elective="{ row }">

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="row.elective
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                        ">
                        {{ row.elective ? "YES" : "NO" }}
                    </span>

                </template>

                <template #actions="{ row }">

                    <div class="flex items-center justify-center gap-2">

                        <Link :href="route('subjects.edit', row.id)">

                            <button
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-indigo-600 transition hover:bg-indigo-50 hover:text-indigo-800 dark:text-indigo-300 dark:hover:bg-indigo-500/10"
                                type="button">
                                <i class="fas fa-edit"></i>
                            </button>

                        </Link>

                        <button @click="deleteSubject(row)"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 hover:text-red-800 dark:text-red-300 dark:hover:bg-red-500/10"
                            type="button">
                            <i class="fas fa-trash"></i>
                        </button>

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