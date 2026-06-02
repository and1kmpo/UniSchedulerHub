<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import ScheduleForm from "./Form.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { success, error } = useAlert();

const props = defineProps({
    classGroup: {
        type: Object,
        required: true,
    },

    classrooms: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    day: "",
    start_time: "",
    end_time: "",
    classroom_id: "",
    status: "published",
});

const submit = () => {
    form.post(route("class-schedules.store", props.classGroup.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            success(page.props.flash?.success || "Schedule created successfully");
        },
        onError: () => {
            error("Failed to create schedule");
        },
    });
};

const handleCancel = () => {
    router.visit(route("class-groups.show", props.classGroup.id));
};
</script>

<template>
    <CrudPageLayout title="Create Schedule" subtitle="Add a schedule block to this class group">
        <CrudContainer>
            <ScheduleForm :form="form" :class-group="classGroup" :classrooms="classrooms"
                :processing="form.processing" :handle-cancel="handleCancel" @submit="submit" />
        </CrudContainer>
    </CrudPageLayout>
</template>
