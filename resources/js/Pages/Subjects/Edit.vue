<script setup>
import { watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";

import AppLayout from "@/Layouts/AppLayout.vue";
import SubjectForm from "@/Components/Subjects/Form.vue";
import PageHeader from "@/Components/UI/PageHeader.vue";

const props = defineProps({
    subject: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: "",
    description: "",
    credits: "",
    knowledge_area: "",
    elective: false,
});

watch(
    () => props.subject,
    (subject) => {
        if (!subject) return;

        form.name = subject.name ?? "";
        form.description = subject.description ?? "";
        form.credits = subject.credits ?? "";
        form.knowledge_area = subject.knowledge_area ?? "";
        form.elective = Boolean(subject.elective);
    },
    { immediate: true }
);

const handleSubmit = () => {
    form.put(route("subjects.update", props.subject.id));
};

const handleCancel = () => {
    router.visit(route("subjects.index"));
};
</script>

<template>
    <AppLayout title="Edit Subject">
        <template #header>
            <PageHeader title="Edit Subject" subtitle="Update subject information" />
        </template>

        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl">
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-900 sm:p-6">
                    <SubjectForm updating :form="form" :handleCancel="handleCancel" @submit="handleSubmit" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
