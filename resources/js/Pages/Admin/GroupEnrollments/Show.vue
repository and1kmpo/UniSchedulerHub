<script setup>
import { computed, ref, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import axios from "axios";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import EnrollmentWorkspace from "@/Pages/ClassGroups/Partials/Sections/EnrollmentWorkspace.vue";

import { useAlert } from "@/Components/Composables/useAlert";

const { toastSuccess, toastError, confirm } = useAlert();

const props = defineProps({
    classGroup: {
        type: Object,
        required: true,
    },

    allStudents: {
        type: Array,
        default: () => [],
    },

    enrollments: {
        type: Array,
        default: () => [],
    },

    enrolledIds: {
        type: Array,
        default: () => [],
    },

    canManageEnrollments: {
        type: Boolean,
        default: false,
    },
});

const selectedStudent = ref(null);
const processing = ref(false);
const validationLoading = ref(false);
const validationResult = ref(defaultValidationResult());

const columns = [
    { key: "student_name", label: "Student" },
    { key: "document", label: "Document" },
    { key: "email", label: "Email" },
    { key: "status", label: "Status" },
    { key: "final_grade", label: "Final Grade" },
    { key: "grade_state", label: "Grade Status" },
];

const rows = computed(() => props.enrollments);

const scheduleSummary = computed(() => {
    if (!props.classGroup.schedules?.length) {
        return "Pending";
    }

    return props.classGroup.schedules
        .map((schedule) => {
            const day = schedule.day.charAt(0).toUpperCase() + schedule.day.slice(1);
            const room = schedule.classroom ? ` - ${schedule.classroom}` : "";

            return `${day} ${schedule.start_time}-${schedule.end_time}${room}`;
        })
        .join("; ");
});

const pageSubtitle = computed(() =>
    props.canManageEnrollments
        ? "Review and manage active enrollments for this group"
        : "Read-only roster for your assigned class group"
);

const enrollmentStatusVariant = (status) => ({
    pre_enrolled: "warning",
    enrolled: "success",
    withdrawn: "gray",
    cancelled: "gray",
}[status] || "gray");

function formatStatus(status) {
    return status ? status.replaceAll("_", " ").toUpperCase() : "PENDING";
}

function defaultValidationResult() {
    return {
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
    };
}

async function validateEnrollment() {
    if (!selectedStudent.value) {
        validationResult.value = defaultValidationResult();
        return;
    }

    validationLoading.value = true;

    try {
        const response = await axios.post(
            route("class-groups.validate-enrollment", props.classGroup.id),
            {
                student_id: selectedStudent.value.id,
            }
        );

        validationResult.value = response.data;
    } catch (exception) {
        const message =
            exception.response?.data?.message
            || exception.response?.data?.error
            || "Enrollment validation failed. Please review the selected student and group.";

        validationResult.value = {
            ...defaultValidationResult(),
            allowed: false,
            valid: false,
            errors: [message],
        };

        toastError(message);
    } finally {
        validationLoading.value = false;
    }
}

async function enrollStudent() {
    if (!selectedStudent.value || !validationResult.value.allowed) {
        return;
    }

    processing.value = true;

    try {
        await axios.post(route("class-groups.enroll", props.classGroup.id), {
            student_id: selectedStudent.value.id,
        });

        toastSuccess("Student enrolled successfully");
        router.reload({ preserveScroll: true });
    } catch (exception) {
        toastError(exception.response?.data?.code || exception.response?.data?.message || "Enrollment failed");
    } finally {
        processing.value = false;
        selectedStudent.value = null;
        validationResult.value = defaultValidationResult();
    }
}

async function removeEnrollment(row) {
    const ok = await confirm("Are you sure you want to remove this student?", "Confirm removal");

    if (!ok) {
        return;
    }

    processing.value = true;

    try {
        await axios.delete(route("class-groups.unenroll", [
            props.classGroup.id,
            row.student_id,
        ]));

        toastSuccess("Enrollment removed successfully");
        router.reload({ preserveScroll: true });
    } catch (exception) {
        toastError(exception.response?.data?.code || exception.response?.data?.message || "Could not remove enrollment");
    } finally {
        processing.value = false;
    }
}

watch(selectedStudent, validateEnrollment);
</script>

<template>
    <CrudPageLayout :title="`Roster - ${classGroup.code}`" :subtitle="pageSubtitle">
        <template #actions>
            <Link v-if="classGroup.can_manage_grades" :href="route('groups.grades.index', classGroup.id)">
                <BaseButton :variant="classGroup.can_edit_grades ? 'primary' : 'secondary'">
                    <i :class="classGroup.can_edit_grades ? 'fa-solid fa-clipboard-list mr-2' : 'fa-solid fa-eye mr-2'" />
                    {{ classGroup.can_edit_grades ? "Manage Grades" : "View Grades" }}
                </BaseButton>
            </Link>
        </template>

        <CrudContainer>
            <div class="space-y-6">
                <SectionCard>
                    <div class="grid gap-4 p-6 md:grid-cols-3">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">
                                Subject
                            </p>

                            <p class="mt-1 font-semibold text-ink dark:text-white">
                                {{ classGroup.subject }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">
                                Professor
                            </p>

                            <p class="mt-1 font-semibold text-ink dark:text-white">
                                {{ classGroup.professor ?? "Unassigned" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">
                                Capacity
                            </p>

                            <p class="mt-1 font-mono font-semibold text-ink dark:text-white">
                                {{ classGroup.subject_enrollments_count }} / {{ classGroup.capacity ?? "Unlimited" }}
                            </p>
                        </div>

                        <div class="md:col-span-3">
                            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">
                                Schedule
                            </p>

                            <p class="mt-1 font-mono text-ink dark:text-white">
                                {{ scheduleSummary }}
                            </p>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-border-light p-6 dark:border-border-dark">
                        <h2 class="text-lg font-semibold text-ink dark:text-white">
                            Enrolled Students
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                            Active enrollment records for this class group.
                        </p>
                    </div>

                    <div class="p-6">
                        <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                            <template #cell-status="{ value }">
                                <StatusBadge :label="formatStatus(value)" :variant="enrollmentStatusVariant(value)" />
                            </template>

                            <template #cell-final_grade="{ value }">
                                {{ value ?? "Pending" }}
                            </template>

                            <template #cell-grade_state="{ value }">
                                {{ value ?? "Pending" }}
                            </template>

                            <template v-if="canManageEnrollments" #actions="{ row }">
                                <BaseButton size="sm" variant="danger" :disabled="processing"
                                    @click="removeEnrollment(row)">
                                    <i class="fa-solid fa-user-minus mr-2" />
                                    Remove
                                </BaseButton>
                            </template>
                        </DataTable>

                        <EmptyState v-else title="No active enrollments"
                            description="This class group does not have active enrolled students yet."
                            icon="fa-solid fa-user-graduate" />
                    </div>
                </SectionCard>

                <EnrollmentWorkspace
                    v-if="canManageEnrollments"
                    :class-group="classGroup"
                    :students="allStudents"
                    :enrolled-ids="enrolledIds"
                    :selected-student="selectedStudent"
                    :validation-result="validationResult"
                    :validation-loading="validationLoading || processing"
                    @select-student="selectedStudent = $event"
                    @enroll="enrollStudent"
                />
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
