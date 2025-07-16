<script setup>
import Layout from "@/Layouts/AppLayout.vue";
import Form from "./Form.vue";

defineProps({
    classGroup: Object,
    subjects: Array,
    professors: Array,
});
</script>

<template>
    <Layout title="Edit Class Group">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100">Edit Class Group</h1>
            <Form :classGroup="classGroup" :subjects="subjects" :professors="professors" />
        </div>
    </Layout>
</template>
