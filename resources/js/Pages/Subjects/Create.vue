<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import SubjectForm from "@/Components/Subjects/Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const form = useForm({
    name: "",
    description: "",
    credits: "",
    knowledge_area: "",
    elective: false,
});

const submit = () => {
    form.post(route("subjects.store"), {

        preserveScroll: true,

        onSuccess: (page) => {

            success(
                page.props.flash?.success ||
                "Subject created successfully"
            );
        },

        onError: () => {

            error("Failed to create subject");
        },
    });
};

const handleCancel = () => {
    router.visit(route("subjects.index"));
};
</script>

<template>
    <CrudPageLayout title="Create Subject" subtitle="Create a new university subject">
        <CrudContainer>

            <SubjectForm :form="form" :processing="form.processing" :handleCancel="handleCancel" @submit="submit" />

        </CrudContainer>
    </CrudPageLayout>
</template>