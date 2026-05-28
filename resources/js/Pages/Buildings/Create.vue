<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import BuildingForm from "./Partials/Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const form = useForm({
    name: "",
    code: "",
    description: "",
});

const submit = () => {
    form.post(route("buildings.store"), {
        preserveScroll: true,

        onSuccess: (page) => {
            success(
                page.props.flash?.success ||
                "Building created successfully"
            );
        },

        onError: () => {
            error("Failed to create building");
        },
    });
};

const handleCancel = () => {
    router.visit(route("buildings.index"));
};
</script>

<template>
    <CrudPageLayout title="Create Building" subtitle="Create a new infrastructure building">
        <CrudContainer>

            <BuildingForm :form="form" :processing="form.processing" :handleCancel="handleCancel" @submit="submit" />

        </CrudContainer>
    </CrudPageLayout>
</template>
