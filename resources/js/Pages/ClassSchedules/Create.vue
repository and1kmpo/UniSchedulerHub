<script setup>
import Layout from "@/Layouts/AppLayout.vue";
import Form from "./Form.vue";

defineProps({
    classGroup: Object,
    schedule: Object, // Solo en Edit.vue
});
</script>

<template>
    <Layout :title="schedule ? 'Edit Schedule' : 'Add Schedule'">
        <Form :classGroup="classGroup" :schedule="schedule" />
    </Layout>
</template>
