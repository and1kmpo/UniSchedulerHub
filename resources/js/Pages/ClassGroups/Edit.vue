<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import Form from "./Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const props = defineProps({
    classGroup: {
        type: Object,
        required: true,
    },

    subjects: {
        type: Array,
        default: () => [],
    },

    professors: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    subject_id: props.classGroup.subject_id ?? "",
    professor_id: props.classGroup.professor_id ?? "",
    capacity: props.classGroup.capacity ?? 30,
    modality: props.classGroup.modality ?? "In-person",
    shift: props.classGroup.shift ?? "Day",
    status: props.classGroup.status ?? "published",
    schedules: props.classGroup.schedules ?? [],
});

const submit = () => {
    form.put(route("class-groups.update", props.classGroup.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            success(page.props.flash?.success || "Class group updated successfully");
        },
        onError: () => {
            error("Failed to update class group");
        },
    });
};

const handleCancel = () => {
    router.visit(route("class-groups.index"));
};
</script>

<template>
    <CrudPageLayout title="Edit Class Group" subtitle="Update academic group information">
        <CrudContainer>
            <Form :form="form" :subjects="subjects" :professors="professors" :processing="form.processing"
                :updating="true" :handle-cancel="handleCancel" @submit="submit" />
        </CrudContainer>
    </CrudPageLayout>
</template>
