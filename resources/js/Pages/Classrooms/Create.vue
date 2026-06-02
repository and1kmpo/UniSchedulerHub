<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import ClassroomForm from "./Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

defineProps({
    buildings: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    building_id: "",
    floor: "",
    capacity: "",
    description: "",
    status: "active",
});

const submit = () => {
    form.post(route("classrooms.store"), {
        preserveScroll: true,
        onSuccess: (page) => {
            success(page.props.flash?.success || "Classroom created successfully");
        },
        onError: () => {
            error("Failed to create classroom");
        },
    });
};

const handleCancel = () => {
    router.visit(route("classrooms.index"));
};
</script>

<template>
    <CrudPageLayout title="Create Classroom" subtitle="Create a room for academic scheduling">
        <CrudContainer>
            <ClassroomForm :form="form" :buildings="buildings" :processing="form.processing"
                :handle-cancel="handleCancel" @submit="submit" />
        </CrudContainer>
    </CrudPageLayout>
</template>
