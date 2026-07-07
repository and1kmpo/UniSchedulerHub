<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import ProfessorForm from "./Partials/Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const props = defineProps({
    professor: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.professor.user?.name ?? "",
    document: props.professor.document ?? "",
    phone: props.professor.phone ?? "",
    email: props.professor.user?.email ?? "",
    password: "",
    address: props.professor.address ?? "",
    city: props.professor.city ?? "",
});

const submit = () => {
    form.put(route("professors.update", props.professor.id), {
        preserveScroll: true,

        onSuccess: (page) => {
            success(
                page.props.flash?.success ||
                "Professor updated successfully"
            );
        },

        onError: () => {
            error("Failed to update professor");
        },
    });
};

const handleCancel = () => {
    router.visit(route("professors.index"));
};
</script>

<template>
    <CrudPageLayout title="Edit Professor" subtitle="Update professor profile and contact information">
        <CrudContainer>

            <ProfessorForm :form="form" :processing="form.processing" :updating="true" :handleCancel="handleCancel"
                @submit="submit" />

        </CrudContainer>
    </CrudPageLayout>
</template>

