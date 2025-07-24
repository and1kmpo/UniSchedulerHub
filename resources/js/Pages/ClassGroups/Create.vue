<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Form from "./Form.vue";

defineProps({
    subjects: Array,
    professors: Array,
});
</script>

<template>
    <AppLayout title="Create Class Group">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Create class group
            </h1>
        </template>
        <div class="max-w-4xl mx-auto">
            <Form :subjects="subjects" :professors="professors" />
        </div>
    </AppLayout>
</template>
