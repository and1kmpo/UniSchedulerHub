<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import SubjectForm from "./Partials/Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const props = defineProps({
    subject: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.subject.name ?? "",
    description: props.subject.description ?? "",
    credits: props.subject.credits ?? "",
    knowledge_area: props.subject.knowledge_area ?? "",
    elective: Boolean(props.subject.elective),
});

const submit = () => {
    form.put(route("subjects.update", props.subject.id), {

        preserveScroll: true,

        onSuccess: (page) => {

            success(
                page.props.flash?.success ||
                "Subject updated successfully"
            );
        },

        onError: () => {

            error("Failed to update subject");
        },
    });
};

const handleCancel = () => {
    router.visit(route("subjects.index"));
};
</script>

<template>
    <CrudPageLayout title="Edit Subject" subtitle="Update university subject information">
        <CrudContainer>

            <SubjectForm :form="form" :processing="form.processing" :updating="true" :handleCancel="handleCancel"
                @submit="submit" />

        </CrudContainer>
    </CrudPageLayout>
</template>
