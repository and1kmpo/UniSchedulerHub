<script setup>
import {
    useForm,
    router,
} from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import ProgramForm from "./Partials/Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const props = defineProps({
    program: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.program.name ?? "",
    description: props.program.description ?? "",
});

const submit = () => {

    form.put(
        route(
            "programs.update",
            props.program.id
        ),
        {
            preserveScroll: true,

            onSuccess: (page) => {

                success(
                    page.props.flash?.success ||
                    "Program updated successfully"
                );
            },

            onError: () => {

                error("Failed to update program");
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
    <CrudPageLayout title="Edit Program" subtitle="Update academic program information">
        <CrudContainer>

            <ProgramForm :form="form" :processing="form.processing" :updating="true" :handleCancel="handleCancel"
                @submit="submit" />

        </CrudContainer>
    </CrudPageLayout>
</template>
