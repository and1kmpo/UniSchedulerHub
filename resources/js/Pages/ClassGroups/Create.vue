<script setup>
import Layout from "@/Layouts/AppLayout.vue";
import Form from "./Form.vue";

defineProps({
    subjects: Array,
    professors: Array,
});
</script>

<template>
    <Layout title="Create Class Group">
        <h1 class="text-xl font-bold mb-4">Create Class Group</h1>
        <Form :subjects="subjects" :professors="professors" />
    </Layout>
</template>
