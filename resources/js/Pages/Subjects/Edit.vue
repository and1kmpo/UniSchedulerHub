<script setup>
import { watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";

import AppLayout from "@/Layouts/AppLayout.vue";
import SubjectForm from "@/Components/Subjects/Form.vue";

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
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Subject
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-lg p-6">
                    <SubjectForm updating :form="form" :handleCancel="handleCancel" @submit="handleSubmit" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>