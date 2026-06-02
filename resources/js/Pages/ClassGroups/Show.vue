<script setup>
import { computed, ref, watch } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import EnrollmentWorkspace from "./Partials/Sections/EnrollmentWorkspace.vue";
import GroupOverviewSection from "./Partials/Sections/GroupOverviewSection.vue";
import GroupScheduleSection from "./Partials/Sections/GroupScheduleSection.vue";
import StudentRosterSection from "./Partials/Sections/StudentRosterSection.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const {
    success,
    error,
    confirm,
} = useAlert();

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/

const props = defineProps({
    classGroup: {
        type: Object,
        required: true,
    },

    allStudents: {
        type: Array,
        default: () => [],
    },

    enrolledIds: {
        type: Array,
        default: () => [],
    },
});

/*
|--------------------------------------------------------------------------
| LIVE ENROLLMENT STATE
|--------------------------------------------------------------------------
*/

const selectedStudent = ref(null);

const validationLoading = ref(false);

const validationResult = ref({
    allowed: true,
    valid: true,
    errors: [],

    conflicts: [],

    warnings: [],

    recommendations: [],

    load: {
        credits: 0,
        groups: 0,
        weekly_hours: 0,
    },

    waitlist: false,

    available_slots: 0,
});

/*
|--------------------------------------------------------------------------
| VALIDATION ENGINE
|--------------------------------------------------------------------------
*/

const validateEnrollment = async () => {

    if (!selectedStudent.value) {

        validationResult.value = {
            valid: true,
            allowed: true,
            errors: [],
            conflicts: [],
            warnings: [],
            recommendations: [],
            load: {
                credits: 0,
                groups: 0,
                weekly_hours: 0,
            },
            waitlist: false,
            available_slots: 0,
        };

        return;
    }

    validationLoading.value = true;

    try {

        const response = await axios.post(
            route(
                "class-groups.validate-enrollment",
                props.classGroup.id
            ),
            {
                student_id: selectedStudent.value.id,
            }
        );

        validationResult.value = response.data;

    } catch (exception) {
        const message =
            exception.response?.data?.message ||
            exception.response?.data?.error ||
            "Enrollment validation failed. Please review the selected student and group.";

        validationResult.value = {
            allowed: false,
            valid: false,
            errors: [message],
            conflicts: [],
            warnings: [],
            recommendations: [],
            load: {
                credits: 0,
                groups: 0,
                weekly_hours: 0,
            },
            waitlist: false,
            available_slots: 0,
        };

        error(message);

    } finally {

        validationLoading.value = false;
    }
};

const canConfirmEnrollment = computed(() => {
    return selectedStudent.value && validationResult.value.allowed && !validationLoading.value;
});

const enrollStudent = async () => {
    if (!canConfirmEnrollment.value) {
        return;
    }

    try {
        await axios.post(route("class-groups.enroll", props.classGroup.id), {
            student_id: selectedStudent.value.id,
        });

        success("Student enrolled successfully");

        router.reload({
            preserveScroll: true,
        });
    } catch (exception) {
        error(exception.response?.data?.code || "Could not enroll student");
    }
};

const unenrollStudent = async (student) => {
    const confirmed = await confirm(
        `Remove ${student.name} from this group?`,
        "Remove enrollment"
    );

    if (!confirmed) {
        return;
    }

    try {
        await axios.delete(
            route("class-groups.unenroll", [
                props.classGroup.id,
                student.id,
            ])
        );

        success("Enrollment removed successfully");

        router.reload({
            preserveScroll: true,
        });
    } catch (exception) {
        error(exception.response?.data?.code || "Could not remove enrollment");
    }
};

/*
|--------------------------------------------------------------------------
| WATCHERS
|--------------------------------------------------------------------------
*/

watch(
    selectedStudent,
    () => {

        validateEnrollment();

    }
);
</script>

<template>

    <CrudPageLayout :title="classGroup.code" :subtitle="classGroup.subject.name">

        <CrudContainer>

            <div class="space-y-8">

                <GroupOverviewSection :class-group="classGroup" />

                <GroupScheduleSection :class-group="classGroup" />

                <EnrollmentWorkspace :class-group="classGroup" :students="allStudents" :enrolled-ids="enrolledIds"
                    :selected-student="selectedStudent" :validation-result="validationResult"
                    :validation-loading="validationLoading" @select-student="selectedStudent = $event"
                    @enroll="enrollStudent" />

                <StudentRosterSection :students="classGroup.students" @unenroll="unenrollStudent" />

            </div>

        </CrudContainer>

    </CrudPageLayout>

</template>
