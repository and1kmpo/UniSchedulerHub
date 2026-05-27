<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import ProgramForm from "./Partials/Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const form = useForm({
    name: "",
    description: "",
});

const submit = () => {

    form.post(
        route("programs.store"),
        {
            preserveScroll: true,

            onSuccess: (page) => {

                success(
                    page.props.flash?.success ||
                    "Program created successfully"
                );
            },

            onError: () => {

                error("Failed to create program");
            },
        }
    );
};

const handleCancel = () => {

    router.visit(
        route("programs.index")
    );
};
</script>

<template>
    <CrudPageLayout title="Create Program" subtitle="Create a new academic program">
        <CrudContainer>

            <ProgramForm :form="form" :processing="form.processing" :handleCancel="handleCancel" @submit="submit" />

        </CrudContainer>
    </CrudPageLayout>
</template>
