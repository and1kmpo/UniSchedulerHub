<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import StudentForm from "./Partials/Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },

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
    name: props.student.user?.name ?? "",
    document: props.student.document ?? "",
    phone: props.student.phone ?? "",
    email: props.student.user?.email ?? "",
    password: "",
    address: props.student.address ?? "",
    city: props.student.city ?? "",
    semester: props.student.semester ?? "",
    program_id: props.student.program_id ?? "",
    curriculum_id: props.student.curriculum_id ?? "",
    academic_status: props.student.academic_status ?? "active",
});

const submit = () => {
    form.put(route("students.update", props.student.id), {
        preserveScroll: true,

        onSuccess: (page) => {
            success(
                page.props.flash?.success ||
                "Student updated successfully"
            );
        },

        onError: () => {
            error("Failed to update student");
        },
    });
};

const handleCancel = () => {
    router.visit(route("students.index"));
};
</script>

<template>
    <CrudPageLayout title="Edit Student" subtitle="Update student profile and academic information">
        <CrudContainer>

            <StudentForm :form="form" :programs="programs" :curricula="curricula"
                :academicStatuses="academicStatuses" :processing="form.processing" :updating="true"
                :handleCancel="handleCancel" @submit="submit" />

        </CrudContainer>
    </CrudPageLayout>
</template>
