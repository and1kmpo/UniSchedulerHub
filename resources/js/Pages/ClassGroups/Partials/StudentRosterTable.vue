<script setup>
import DataTable from "@/Components/UI/Table/DataTable.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";

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
            <BaseButton type="button" variant="danger" size="sm" @click="$emit('unenroll', row)">
                <i class="fa-solid fa-user-minus" />
            </BaseButton>
        </template>

    </DataTable>

</template>

