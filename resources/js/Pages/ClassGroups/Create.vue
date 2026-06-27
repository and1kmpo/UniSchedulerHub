<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import Form from "./Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },

    professors: {
        type: Array,
        default: () => [],
    },

    currentPeriodId: {
        type: Number,
        default: null,
    },
});

const form = useForm({
    subject_id: "",
    professor_id: "",
    capacity: 30,
    modality: "In-person",
    shift: "Day",
    status: "published",
    academic_period_id: props.currentPeriodId,
    schedules: [
        {
            day: "monday",
            start_time: "08:00",
            end_time: "10:00",
        },
    ],
});

const submit = () => {
    form.post(route("class-groups.store"), {
        preserveScroll: true,
        onSuccess: (page) => {
            success(page.props.flash?.success || "Class group created successfully");
        },
        onError: () => {
            error("Failed to create class group");
        },
    });
};

const handleCancel = () => {
    router.visit(route("class-groups.index"));
};
</script>

<template>
    <CrudPageLayout title="Create Class Group" subtitle="Create an academic group with its initial schedule">
        <CrudContainer>
            <Form :form="form" :subjects="subjects" :professors="professors" :processing="form.processing"
                :handle-cancel="handleCancel" @submit="submit" />
        </CrudContainer>
    </CrudPageLayout>
</template>

