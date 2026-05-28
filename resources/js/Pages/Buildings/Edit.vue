<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import BuildingForm from "./Partials/Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const props = defineProps({
    building: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.building.name ?? "",
    code: props.building.code ?? "",
    description: props.building.description ?? "",
});

const submit = () => {
    form.put(route("buildings.update", props.building.id), {
        preserveScroll: true,

        onSuccess: (page) => {
            success(
                page.props.flash?.success ||
                "Building updated successfully"
            );
        },

        onError: () => {
            error("Failed to update building");
        },
    });
};

const handleCancel = () => {
    router.visit(route("buildings.index"));
};
</script>

<template>
    <CrudPageLayout title="Edit Building" subtitle="Update infrastructure building information">
        <CrudContainer>

            <BuildingForm :form="form" :processing="form.processing" :updating="true" :handleCancel="handleCancel"
                @submit="submit" />

        </CrudContainer>
    </CrudPageLayout>
</template>
