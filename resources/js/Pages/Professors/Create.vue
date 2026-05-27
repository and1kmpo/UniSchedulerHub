<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import ProfessorForm from "./Partials/Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const form = useForm({
    document: "",
    name: "",
    phone: "",
    email: "",
    password: "",
    address: "",
    city: "",
});

const submit = () => {
    form.post(route("professors.store"), {
        preserveScroll: true,

        onSuccess: (page) => {
            success(
                page.props.flash?.success ||
                "Professor created successfully"
            );
        },

        onError: () => {
            error("Failed to create professor");
        },
    });
};

const handleCancel = () => {
    router.visit(route("professors.index"));
};
</script>

<template>
    <CrudPageLayout title="Create Professor" subtitle="Create a professor profile and linked user account">
        <CrudContainer>

            <ProfessorForm :form="form" :processing="form.processing" :handleCancel="handleCancel" @submit="submit" />

        </CrudContainer>
    </CrudPageLayout>
</template>
