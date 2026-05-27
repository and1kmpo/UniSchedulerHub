<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import StudentForm from "./Partials/Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

defineProps({
    programs: {
        type: Array,
        required: true,
    },

    curricula: {
        type: Array,
        default: () => [],
    },

    academicStatuses: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    document: "",
    name: "",
    phone: "",
    email: "",
    password: "",
    address: "",
    city: "",
    semester: "",
    program_id: "",
    curriculum_id: "",
    academic_status: "active",
});

const submit = () => {
    form.post(route("students.store"), {
        preserveScroll: true,

        onSuccess: (page) => {
            success(
                page.props.flash?.success ||
                "Student created successfully"
            );
        },

        onError: () => {
            error("Failed to create student");
        },
    });
};

const handleCancel = () => {
    router.visit(route("students.index"));
};
</script>

<template>
    <CrudPageLayout title="Create Student" subtitle="Create a student profile and linked user account">
        <CrudContainer>

            <StudentForm :form="form" :programs="programs" :curricula="curricula"
                :academicStatuses="academicStatuses" :processing="form.processing" :handleCancel="handleCancel"
                @submit="submit" />

        </CrudContainer>
    </CrudPageLayout>
</template>
