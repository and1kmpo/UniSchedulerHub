<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import SubjectForm from "@/Components/Subjects/Form.vue";
import PageHeader from "@/Components/UI/PageHeader.vue";
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
            <PageHeader title="Create Subject" subtitle="Add a subject to the academic catalog" />
        </template>

        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl">
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-900 sm:p-6">
                    <SubjectForm :form="form" :handleCancel="handleCancel" @submit="handleSubmit" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
