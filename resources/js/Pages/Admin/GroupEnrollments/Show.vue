<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";

import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";

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

    canManageEnrollments: {
        type: Boolean,
        default: false,
    },
});

const selectedStudentId = ref("");
const processing = ref(false);

const columns = [
    { key: "student_name", label: "Student" },
    { key: "document", label: "Document" },
    { key: "email", label: "Email" },
    { key: "status", label: "Status" },
];

const rows = computed(() => props.enrollments);

const studentOptions = computed(() =>
    props.allStudents.map((student) => ({
        label: `${student.name} (${student.document})`,
        value: student.id,
    }))
);

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

const enrollmentStatusVariant = (status) => ({
    pre_enrolled: "warning",
    enrolled: "success",
    withdrawn: "gray",
    cancelled: "gray",
}[status] || "gray");

function formatStatus(status) {
    return status ? status.replaceAll("_", " ").toUpperCase() : "PENDING";
}

async function enrollStudent() {
    if (!selectedStudentId.value) {
        return;
    }

    processing.value = true;

    try {
        await axios.post(route("class-groups.enroll", props.classGroup.id), {
            student_id: selectedStudentId.value,
        });

        toastSuccess("Student enrolled successfully");
        router.reload({ preserveScroll: true });
    } catch (exception) {
        toastError(exception.response?.data?.code || exception.response?.data?.message || "Enrollment failed");
    } finally {
        processing.value = false;
        selectedStudentId.value = "";
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
</script>

<template>
    <CrudPageLayout :title="`Enrollments - ${classGroup.code}`"
        subtitle="Review and manage active enrollments for this group">
        <CrudContainer>
            <div class="space-y-6">
                <SectionCard>
                    <div class="grid gap-4 p-6 md:grid-cols-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Subject
                            </p>

                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                {{ classGroup.subject }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Professor
                            </p>

                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                {{ classGroup.professor ?? "Unassigned" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Capacity
                            </p>

                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                {{ classGroup.subject_enrollments_count }} / {{ classGroup.capacity }}
                            </p>
                        </div>

                        <div class="md:col-span-3">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Schedule
                            </p>

                            <p class="mt-1 text-gray-900 dark:text-white">
                                {{ scheduleSummary }}
                            </p>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Enrolled Students
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Active enrollment records for this class group.
                        </p>
                    </div>

                    <div class="p-6">
                        <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                            <template #cell-status="{ value }">
                                <StatusBadge :label="formatStatus(value)" :variant="enrollmentStatusVariant(value)" />
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

                <SectionCard v-if="canManageEnrollments">
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Enroll Student
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Administrative enrollment for exceptional or assisted cases.
                        </p>
                    </div>

                    <div class="grid gap-4 p-6 md:grid-cols-[1fr_auto] md:items-end">
                        <BaseSelect v-model="selectedStudentId" label="Student" placeholder="Select a student"
                            :options="studentOptions" />

                        <BaseButton type="button" variant="primary" :disabled="!selectedStudentId || processing"
                            @click="enrollStudent">
                            <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2" />
                            <i v-else class="fa-solid fa-user-plus mr-2" />
                            Enroll
                        </BaseButton>
                    </div>
                </SectionCard>
            </div>
        </CrudContainer>
    </CrudPageLayout>
</template>
