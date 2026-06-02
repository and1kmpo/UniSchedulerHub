<script setup>
import { computed, reactive, watch } from "vue";

import {
    Link,
    router,
} from "@inertiajs/vue3";

import { route } from "ziggy-js";

import axios from "axios";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import TableToolbar from "@/Components/UI/Table/TableToolbar.vue";
import TableSearch from "@/Components/UI/Table/TableSearch.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import TableActionButton from "@/Components/UI/Table/TableActionButton.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const {
    success,
    error,
    confirm,
} = useAlert();

const props = defineProps({
    classGroups: {
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
        key: "code",
        label: "Code",
        sortable: true,
    },

    {
        key: "subject",
        label: "Subject",
    },

    {
        key: "professor",
        label: "Professor",
    },

    {
        key: "shift",
        label: "Shift",
        sortable: true,
    },

    {
        key: "modality",
        label: "Modality",
        sortable: true,
    },

    {
        key: "status",
        label: "Status",
        sortable: true,
    },

    {
        key: "occupancy",
        label: "Occupancy",
    },
];

const filterForm = reactive({

    search:
        props.filters.search || "",

    modality:
        props.filters.modality || "",

    shift:
        props.filters.shift || "",

    status:
        props.filters.status || "",
});

watch(
    () => ({
        ...filterForm,
    }),

    () => {

        router.get(
            route("class-groups.index"),

            {
                ...filterForm,

                sort:
                    props.filters?.sort,

                direction:
                    props.filters?.direction,

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

    filterForm.modality = "";

    filterForm.shift = "";

    filterForm.status = "";
};

const destroy = async (group) => {

    const confirmed = await confirm(
        `Delete "${group.code}"?`,
        "Delete Class Group"
    );

    if (!confirmed) return;

    try {

        await axios.delete(
            route(
                "class-groups.destroy",
                group.id
            )
        );

        success(
            "Class group deleted successfully"
        );

        router.reload();

    } catch (e) {

        error(
            "Could not delete class group"
        );
    }
};

const occupancyPercentage = (group) => {

    return Math.round(
        (
            group.subject_enrollments_count /
            group.capacity
        ) * 100
    );
};

const totalStudents = computed(() => {

    return props.classGroups.data.reduce(
        (total, group) =>
            total +
            group.subject_enrollments_count,
        0
    );
});
</script>

<template>

    <CrudPageLayout title="Class Groups" subtitle="Manage academic groups, schedules and enrollments">

        <template #actions>

            <Link :href="route('class-groups.create')">

                <BaseButton variant="primary">

                    <i class="fa-solid fa-plus mr-2"></i>

                    Create Group

                </BaseButton>

            </Link>

        </template>

        <CrudContainer>

            <!-- ANALYTICS -->

            <div class="mb-6 grid gap-4 md:grid-cols-3">

                <SectionCard class="p-6">
                    <p class="text-sm text-gray-500">
                        Total Groups
                    </p>

                    <h3 class="mt-2 text-3xl font-bold">
                        {{ classGroups.total }}
                    </h3>
                </SectionCard>

                <SectionCard class="p-6">
                    <p class="text-sm text-gray-500">
                        Total Students
                    </p>

                    <h3 class="mt-2 text-3xl font-bold">
                        {{ totalStudents }}
                    </h3>
                </SectionCard>

                <SectionCard class="p-6">
                    <p class="text-sm text-gray-500">
                        Average Occupancy
                    </p>

                    <h3 class="mt-2 text-3xl font-bold">
                        {{
                            Math.round(
                                totalStudents /
                                classGroups.data.reduce(
                                    (sum, g) =>
                                        sum + g.capacity,
                                    0
                                ) * 100
                            ) || 0
                        }}%
                    </h3>
                </SectionCard>

            </div>

            <!-- TOOLBAR -->

            <TableToolbar>

                <template #search>

                    <div class="w-full lg:max-w-sm">

                        <TableSearch v-model="filterForm.search" placeholder="Search groups..." />

                    </div>

                </template>

                <template #filters>

                    <div class="w-full sm:max-w-xs">

                        <BaseSelect v-model="filterForm.shift" placeholder="Shift" :options="[
                            {
                                label: 'Day',
                                value: 'Day',
                            },
                            {
                                label: 'Night',
                                value: 'Night',
                            },
                            {
                                label: 'Intensive',
                                value: 'Intensive',
                            },
                        ]" />

                    </div>

                    <div class="w-full sm:max-w-xs">

                        <BaseSelect v-model="filterForm.modality" placeholder="Modality" :options="[
                            {
                                label: 'Virtual',
                                value: 'Virtual',
                            },
                            {
                                label: 'In-person',
                                value: 'In-person',
                            },
                            {
                                label: 'Hybrid',
                                value: 'Hybrid',
                            },
                        ]" />

                    </div>

                    <div class="w-full sm:max-w-xs">

                        <BaseSelect v-model="filterForm.status" placeholder="Status" :options="[
                            {
                                label: 'Draft',
                                value: 'draft',
                            },
                            {
                                label: 'Published',
                                value: 'published',
                            },
                            {
                                label: 'Cancelled',
                                value: 'cancelled',
                            },
                            {
                                label: 'Closed',
                                value: 'closed',
                            },
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

            <!-- TABLE -->

            <DataTable v-if="classGroups.data.length" :columns="columns" :rows="classGroups.data" :filters="filters"
                sortable>

                <!-- SUBJECT -->

                <template #cell-subject="{ row }">

                    <div>

                        <p class="font-medium">
                            {{
                                row.subject.name
                            }}
                        </p>

                        <p class="text-xs text-gray-500">
                            {{
                                row.subject.code
                            }}
                        </p>

                    </div>

                </template>

                <!-- PROFESSOR -->

                <template #cell-professor="{ row }">

                    <span>

                        {{
                            row.professor?.name ||
                            "Not assigned"
                        }}

                    </span>

                </template>

                <!-- SHIFT -->

                <template #cell-shift="{ row }">

                    <StatusBadge :label="row.shift" :variant="row.shift === 'Day'
                            ? 'success'
                            : row.shift === 'Night'
                                ? 'warning'
                                : 'gray'
                        " />

                </template>

                <!-- MODALITY -->

                <template #cell-modality="{ row }">

                    <StatusBadge :label="row.modality" variant="gray" />

                </template>

                <template #cell-status="{ row }">

                    <StatusBadge :label="row.status" :variant="row.status === 'published'
                            ? 'success'
                            : row.status === 'draft'
                                ? 'warning'
                                : 'gray'
                        " />

                </template>

                <!-- OCCUPANCY -->

                <template #cell-occupancy="{ row }">

                    <div class="w-44">

                        <div class="mb-1 flex items-center justify-between text-xs">

                            <span>
                                {{
                                    row.subject_enrollments_count
                                }}
                                /
                                {{
                                    row.capacity
                                }}
                            </span>

                            <span>
                                {{
                                    occupancyPercentage(row)
                                }}%
                            </span>

                        </div>

                        <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">

                            <div class="h-full rounded-full bg-indigo-600" :style="{
                                width:
                                    occupancyPercentage(row) + '%'
                            }" />

                        </div>

                    </div>

                </template>

                <!-- ACTIONS -->

                <template #actions="{ row }">

                    <div class="flex items-center justify-center gap-2">

                        <!-- SHOW -->

                        <Link :href="route(
                            'class-groups.show',
                            row.id
                        )
                            ">

                            <TableActionButton icon="fa-solid fa-eye" color="sky" />

                        </Link>

                        <!-- EDIT -->

                        <Link :href="route(
                            'class-groups.edit',
                            row.id
                        )
                            ">

                            <TableActionButton icon="fa-solid fa-pen-to-square" color="indigo" />

                        </Link>

                        <!-- CALENDAR -->

                        <Link :href="route(
                            'class-schedules.calendar',
                            row.id
                        )
                            ">

                            <TableActionButton icon="fa-solid fa-calendar" color="gray" />

                        </Link>

                        <!-- DELETE -->

                        <TableActionButton icon="fa-solid fa-trash" color="red" @click="destroy(row)" />

                    </div>

                </template>

            </DataTable>

            <!-- EMPTY STATE -->

            <EmptyState v-else title="No class groups found" description="Create your first academic group to begin."
                icon="fa-solid fa-users">

                <Link :href="route(
                    'class-groups.create'
                )
                    ">

                    <BaseButton variant="primary">

                        <i class="fa-solid fa-plus mr-2" />

                        Create Group

                    </BaseButton>

                </Link>

            </EmptyState>

            <!-- PAGINATION -->

            <TablePagination v-if="classGroups.data.length" :data="classGroups" />

        </CrudContainer>

    </CrudPageLayout>

</template>
