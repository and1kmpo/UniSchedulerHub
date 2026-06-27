<script setup>
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

defineProps({
    status: {
        type: Object,
        required: true,
    },
});

const variants = {
    enrolled: "success",
    cancelled: "danger",
    pending: "warning",
};
</script>

<template>

    <StatusBadge :label="status.description" :variant="variants[
        status.code
        ] || 'gray'
        " />

</template>
