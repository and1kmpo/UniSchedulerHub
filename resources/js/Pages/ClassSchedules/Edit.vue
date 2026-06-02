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

    schedule: {
        type: Object,
        required: true,
    },

    classrooms: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    day: props.schedule.day ?? "",
    start_time: props.schedule.start_time ?? "",
    end_time: props.schedule.end_time ?? "",
    classroom_id: props.schedule.classroom_id ?? "",
    status: props.schedule.status ?? "published",
});

const submit = () => {
    form.put(route("class-schedules.update", [
        props.classGroup.id,
        props.schedule.id,
    ]), {
        preserveScroll: true,
        onSuccess: (page) => {
            success(page.props.flash?.success || "Schedule updated successfully");
        },
        onError: () => {
            error("Failed to update schedule");
        },
    });
};

const handleCancel = () => {
    router.visit(route("class-groups.show", props.classGroup.id));
};
</script>

<template>
    <CrudPageLayout title="Edit Schedule" subtitle="Update this schedule block">
        <CrudContainer>
            <ScheduleForm :form="form" :class-group="classGroup" :classrooms="classrooms"
                :processing="form.processing" :updating="true" :handle-cancel="handleCancel" @submit="submit" />
        </CrudContainer>
    </CrudPageLayout>
</template>
