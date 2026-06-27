<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import ClassroomForm from "./Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const props = defineProps({
    classroom: {
        type: Object,
        required: true,
    },

    buildings: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: props.classroom.name ?? "",
    building_id: props.classroom.building_id ?? "",
    floor: props.classroom.floor ?? "",
    capacity: props.classroom.capacity ?? "",
    description: props.classroom.description ?? "",
    status: props.classroom.status ?? "active",
});

const submit = () => {
    form.put(route("classrooms.update", props.classroom.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            success(page.props.flash?.success || "Classroom updated successfully");
        },
        onError: () => {
            error("Failed to update classroom");
        },
    });
};

const handleCancel = () => {
    router.visit(route("classrooms.index"));
};
</script>

<template>
    <CrudPageLayout title="Edit Classroom" :subtitle="classroom.name">
        <CrudContainer>
            <ClassroomForm :form="form" :buildings="buildings" :processing="form.processing" :updating="true"
                :handle-cancel="handleCancel" @submit="submit" />
        </CrudContainer>
    </CrudPageLayout>
</template>

