<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import SubjectForm from "@/Components/Subjects/Form.vue";
import { useForm, router } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    description: "",
    credits: "",
    knowledge_area: "",
    elective: false,
});

const handleSubmit = () => {
    form.post(route("subjects.store"));
};

const handleCancel = () => {
    router.visit(route("subjects.index"));
};
</script>

<template>
    <AppLayout title="Create Subject">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Subject
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-lg p-6">
                    <SubjectForm :form="form" :handleCancel="handleCancel" @submit="handleSubmit" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>