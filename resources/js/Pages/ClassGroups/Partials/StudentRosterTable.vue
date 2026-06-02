<script setup>
import DataTable from "@/Components/UI/Table/DataTable.vue";

import EnrollmentStatusBadge from "./EnrollmentStatusBadge.vue";

const props = defineProps({
    students: Array,
});

defineEmits(["unenroll"]);

const columns = [

    {
        key: "document",
        label: "Document",
    },

    {
        key: "name",
        label: "Student",
    },

    {
        key: "status",
        label: "Status",
    },
];
</script>

<template>

    <DataTable :columns="columns" :rows="students">

        <template #cell-status="{ row }">

            <EnrollmentStatusBadge :status="row.status" />

        </template>

        <template #actions="{ row }">
            <button type="button"
                class="inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-500/10"
                @click="$emit('unenroll', row)">
                <i class="fa-solid fa-user-minus" />
            </button>
        </template>

    </DataTable>

</template>
