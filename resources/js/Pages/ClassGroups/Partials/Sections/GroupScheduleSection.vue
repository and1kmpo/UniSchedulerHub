<script setup>
import { Link, router, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { route } from "ziggy-js";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import Modal from "@/Components/Modal.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

import ScheduleForm from "@/Pages/ClassSchedules/Form.vue";
import ScheduleTimeline from "../ScheduleTimeline.vue";
import SmartSchedulerBoard from "../Scheduler/SmartSchedulerBoard.vue";
import { useAlert } from "@/Components/Composables/useAlert";

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

const canManageSchedules = props.classGroup.can_manage_schedules !== false;
const showCreateModal = ref(false);
const { success, error } = useAlert();

const localSchedules = ref([
    ...(props.classGroup.schedules || []),
]);

const createForm = useForm({
    day: "",
    start_time: "",
    end_time: "",
    classroom_id: "",
    status: "published",
});

watch(
    () => props.classGroup.schedules,
    (schedules) => {
        localSchedules.value = [
            ...(schedules || []),
        ];
    },
    { deep: true }
);

const replaceSchedule = (updatedSchedule) => {
    localSchedules.value = localSchedules.value.map((schedule) => {
        if (schedule.id !== updatedSchedule.id) {
            return schedule;
        }

        return updatedSchedule;
    });
};

const resetCreateForm = () => {
    createForm.reset();
    createForm.clearErrors();
    createForm.status = "published";
};

const openCreateModal = (defaults = {}) => {
    resetCreateForm();

    if (defaults?.day) {
        createForm.day = defaults.day;
        createForm.start_time = defaults.start_time || "";
        createForm.end_time = defaults.end_time || "";
    }

    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    resetCreateForm();
};

const submitCreateSchedule = () => {
    createForm.post(route("class-schedules.store", props.classGroup.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            closeCreateModal();
            success(page.props.flash?.success || "Schedule created successfully");

            router.reload({
                only: ["classGroup"],
                preserveScroll: true,
            });
        },
        onError: () => {
            error("Could not create schedule");
        },
    });
};
</script>

<template>
    <section class="space-y-6">
        <SectionCard class="p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-ink dark:text-white">
                        Schedule Planning
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Manage official blocks, conflicts and weekly distribution.
                    </p>
                </div>

                <div v-if="canManageSchedules" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <BaseButton variant="primary" @click="openCreateModal">
                        <i class="fa-solid fa-calendar-plus mr-2" />
                        Add Schedule
                    </BaseButton>

                    <Link :href="route('class-schedules.create', classGroup.id)"
                        class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        Open full form
                    </Link>
                </div>

                <div v-else class="rounded-lg border border-warning/30 bg-warning/10 px-4 py-2 text-sm text-amber-800">
                    Schedule changes are locked for this group or academic period.
                </div>
            </div>
        </SectionCard>

        <SmartSchedulerBoard :class-group-id="classGroup.id" :schedules="localSchedules"
            :can-edit="canManageSchedules" @schedule-create-request="openCreateModal"
            @schedule-updated="replaceSchedule" />

        <ScheduleTimeline :schedules="localSchedules" />

        <Modal :show="showCreateModal" max-width="2xl" @close="closeCreateModal">
            <ScheduleForm :form="createForm" :class-group="classGroup" :classrooms="classrooms"
                :processing="createForm.processing" :handle-cancel="closeCreateModal" :framed="false"
                @submit="submitCreateSchedule" />
        </Modal>
    </section>
</template>
