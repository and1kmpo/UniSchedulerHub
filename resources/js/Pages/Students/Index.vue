<script setup>
import { computed, reactive, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
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
    students: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    programs: {
        type: Array,
        default: () => [],
    },

    academicStatuses: {
        type: Array,
        default: () => [],
    },
});

const columns = [
    { key: "document", label: "Document", sortable: true },
    { key: "name", label: "Student" },
    { key: "email", label: "Email" },
    { key: "program", label: "Program" },
    { key: "semester", label: "Semester", sortable: true },
    { key: "academic_status", label: "Status", sortable: true },
    { key: "enrollments_count", label: "Enrollments" },
];

const filterForm = reactive({
    search: props.filters.search || "",
    program: props.filters.program || "",
    academic_status: props.filters.academic_status || "",
    semester: props.filters.semester || "",
});

const rows = computed(() =>
    props.students.data.map((student) => ({
        id: student.id,
        document: student.document,
        name: student.user?.name,
        email: student.user?.email,
        phone: student.phone,
        program: student.program?.name ?? "N/A",
        semester: student.semester,
        academic_status: student.academic_status,
        enrollments_count: student.enrollments_count,
    }))
);

const programOptions = computed(() =>
    props.programs.map((program) => ({
        label: program.name,
        value: program.id,
    }))
);

const semesterOptions = Array.from({ length: 10 }, (_, index) => ({
    label: `Semester ${index + 1}`,
    value: index + 1,
}));

watch(
    () => ({
        ...filterForm,
    }),
    () => {
        router.get(
            route("students.index"),
            {
                search: filterForm.search,
                program: filterForm.program,
                academic_status: filterForm.academic_status,
                semester: filterForm.semester,
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
    filterForm.program = "";
    filterForm.academic_status = "";
    filterForm.semester = "";
};

const deleteStudent = async (student) => {
    const confirmed = await confirm(
        `This will permanently delete "${student.name}"`,
        "Delete Student"
    );

    if (!confirmed) return;

    router.delete(route("students.destroy", student.id), {
        preserveScroll: true,

        onSuccess: (page) => {
            success(
                page.props.flash?.success ||
                "Student deleted successfully"
            );
        },

        onError: () => {
            error("Failed to delete student");
        },
    });
};
</script>

<template>
    <CrudPageLayout title="Students" subtitle="Manage student records and academic status">
        <template #actions>
            <Link :href="route('students.create')">
                <BaseButton variant="primary">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Create Student
                </BaseButton>
            </Link>
        </template>

        <CrudContainer>

            <TableToolbar>
                <template #search>
                    <div class="w-full lg:max-w-sm">
                        <TableSearch v-model="filterForm.search" placeholder="Search students..." />
                    </div>
                </template>

                <template #filters>
                    <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-3 lg:max-w-3xl">
                        <BaseSelect v-model="filterForm.program" placeholder="Program" :options="programOptions" />

                        <BaseSelect v-model="filterForm.academic_status" placeholder="Academic status"
                            :options="academicStatuses" />

                        <BaseSelect v-model="filterForm.semester" placeholder="Semester" :options="semesterOptions" />
                    </div>
                </template>

                <template #actions>
                    <BaseButton variant="secondary" @click="clearFilters">
                        <i class="fa-solid fa-rotate-left mr-2"></i>
                        Reset
                    </BaseButton>
                </template>
            </TableToolbar>

            <DataTable v-if="rows.length" :columns="columns" :rows="rows" :filters="filters" sortable>
                <template #cell-academic_status="{ value }">
                    <StatusBadge :label="value ? value.replace('_', ' ').toUpperCase() : 'N/A'" :variant="{
                        active: 'success',
                        probation: 'warning',
                        suspended: 'danger',
                        graduated: 'success',
                        withdrawn: 'gray',
                    }[value] || 'gray'" />
                </template>

                <template #actions="{ row }">
                    <div class="flex items-center justify-center gap-2">
                        <Link :href="route('students.show', row.id)">
                            <TableActionButton icon="fa-solid fa-eye" label="View student" color="sky" />
                        </Link>

                        <Link :href="route('students.edit', row.id)">
                            <TableActionButton icon="fa-solid fa-pen" label="Edit student" color="indigo" />
                        </Link>

                        <TableActionButton icon="fa-solid fa-trash" label="Delete student" color="red" @click="deleteStudent(row)" />
                    </div>
                </template>
            </DataTable>

            <EmptyState v-else title="No students found" description="Create your first student record to begin."
                icon="fa-solid fa-user-graduate">
                <Link :href="route('students.create')">
                    <BaseButton variant="primary">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Create Student
                    </BaseButton>
                </Link>
            </EmptyState>

            <TablePagination v-if="rows.length" :data="students" />

        </CrudContainer>
    </CrudPageLayout>
</template>
