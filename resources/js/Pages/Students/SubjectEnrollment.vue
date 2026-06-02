<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import { useAlert } from "@/Components/Composables/useAlert";

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
    enrollmentDeadline: {
        type: String,
        default: null,
    },
    unenrollmentDeadline: {
        type: String,
        default: null,
    },
    currentSchedules: {
        type: Array,
        default: () => [],
    },
    currentCredits: {
        type: Number,
        default: 0,
    },
    maxCredits: {
        type: Number,
        default: 0,
    },
    systemState: {
        type: String,
        default: "ready",
    },
});

const { toastSuccess, toastError } = useAlert();

const selectedSubject = ref(null);
const loadingGroups = ref(false);
const submitting = ref(false);
const groupsError = ref(null);

const columns = [
    { key: "name", label: "Subject" },
    { key: "credits", label: "Credits" },
    { key: "semester", label: "Semester" },
    { key: "status_label", label: "Status" },
    { key: "block_reason", label: "Academic Rule" },
];

const rows = computed(() =>
    props.subjects.map((subject) => ({
        ...subject,
        status_label: formatStatus(subject.status || subject.audit?.status),
        status_variant: statusVariant(subject.status || subject.audit?.status),
        block_reason: blockReason(subject),
    }))
);

const availableSubjects = computed(() =>
    props.subjects.filter((subject) => subject.canEnroll).length
);

const enrolledSubjects = computed(() =>
    props.subjects.filter((subject) => subject.alreadyEnrolled).length
);

const isEnrollmentOpen = computed(() => {
    if (!props.enrollmentDeadline) {
        return true;
    }

    return new Date() <= endOfDay(props.enrollmentDeadline);
});

const selectedGroups = computed(() => selectedSubject.value?.groups ?? []);

function endOfDay(date) {
    const value = new Date(date);
    value.setHours(23, 59, 59, 999);

    return value;
}

function formatDate(date) {
    if (!date) {
        return "Not defined";
    }

    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    }).format(new Date(date));
}

function formatStatus(status) {
    return status ? status.replaceAll("_", " ").toUpperCase() : "PENDING";
}

function statusVariant(status) {
    return {
        approved: "success",
        available: "success",
        enrolled: "success",
        in_progress: "success",
        pre_enrolled: "warning",
        available_but_repeating: "warning",
        blocked: "danger",
        failed: "danger",
        cancelled: "gray",
        withdrawn: "gray",
    }[status] || "gray";
}

function blockReason(subject) {
    if (subject.audit?.status === "blocked") {
        const blockedBy = subject.audit?.blockedBy ?? [];

        return blockedBy.length
            ? `Requires ${blockedBy.join(", ")}`
            : "Missing prerequisites";
    }

    if (!isEnrollmentOpen.value) {
        return "Enrollment closed";
    }

    if (subject.alreadyEnrolled) {
        return "Already selected";
    }

    if (!subject.canEnroll) {
        return "Not eligible";
    }

    return "Eligible";
}

function scheduleSummary(schedules = []) {
    if (!schedules.length) {
        return "Schedule pending";
    }

    return schedules
        .map((schedule) => `${formatStatus(schedule.day)} ${schedule.start_time}-${schedule.end_time}`)
        .join("; ");
}

async function openGroupModal(subject) {
    selectedSubject.value = {
        ...subject,
        groups: [],
    };
    groupsError.value = null;
    loadingGroups.value = true;

    try {
        const { data } = await axios.get(
            route("student.subject-enrollment.groups", subject.id)
        );

        selectedSubject.value.groups = data.groups ?? [];
        selectedSubject.value.currentGroupId =
            selectedSubject.value.groups.find((group) => group.isCurrent)?.id ?? null;
    } catch (error) {
        groupsError.value = error.response?.data?.error || "Unable to load available groups.";
    } finally {
        loadingGroups.value = false;
    }
}

function closeGroupModal() {
    selectedSubject.value = null;
    groupsError.value = null;
}

async function enrollInGroup(group) {
    if (!selectedSubject.value || group.isCurrent) {
        return;
    }

    submitting.value = true;

    try {
        const { data } = await axios.post(
            route("student.subject-enrollment.enroll", { classGroup: group.id })
        );

        toastSuccess(data.message || "Enrollment updated successfully.");
        closeGroupModal();
        router.reload({ only: ["subjects", "currentSchedules", "currentCredits"] });
    } catch (error) {
        toastError(error.response?.data?.error || "Enrollment failed.");
    } finally {
        submitting.value = false;
    }
}

async function unenrollFromSubject() {
    if (!selectedSubject.value?.enrollmentId) {
        toastError("Invalid enrollment.");
        return;
    }

    submitting.value = true;

    try {
        const { data } = await axios.delete(
            route("student.subject-enrollment.unenroll", {
                enrollment: selectedSubject.value.enrollmentId,
            })
        );

        toastSuccess(data.message || "Unenrollment successful.");
        closeGroupModal();
        router.reload({ only: ["subjects", "currentSchedules", "currentCredits"] });
    } catch (error) {
        toastError(error.response?.data?.error || "Unenrollment failed.");
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <CrudPageLayout title="Subject Enrollment" subtitle="Select available subjects and class groups for the active academic period">
        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <StatCard title="Current Credits" :value="currentCredits" icon="fa-solid fa-layer-group" />
                    <StatCard title="Credit Limit" :value="maxCredits || '-'" icon="fa-solid fa-gauge-high" />
                    <StatCard title="Available Subjects" :value="availableSubjects" icon="fa-solid fa-book-open" />
                </section>

                <SectionCard>
                    <div class="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Enrollment Window
                            </h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Enrolled subjects: {{ enrolledSubjects }}. Changes are allowed only while enrollment is open.
                            </p>
                        </div>

                        <StatusBadge
                            :label="isEnrollmentOpen ? 'OPEN' : 'CLOSED'"
                            :variant="isEnrollmentOpen ? 'success' : 'gray'"
                        />
                    </div>

                    <div class="grid gap-4 border-t border-gray-200 p-6 text-sm dark:border-gray-800 md:grid-cols-2">
                        <div>
                            <p class="font-medium text-gray-500 dark:text-gray-400">Enrollment deadline</p>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ formatDate(enrollmentDeadline) }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-500 dark:text-gray-400">Unenrollment deadline</p>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ formatDate(unenrollmentDeadline) }}</p>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard v-if="systemState === 'no_curriculum'">
                    <EmptyState
                        title="Academic setup pending"
                        description="Your student profile does not have an active curriculum assigned. Please contact academic administration."
                        icon="fa-solid fa-triangle-exclamation"
                    />
                </SectionCard>

                <SectionCard v-else>
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Curriculum Subjects
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Choose a class group for eligible subjects. The backend validates capacity, duplicates and schedule conflicts.
                        </p>
                    </div>

                    <div class="p-6">
                        <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                            <template #cell-status_label="{ row }">
                                <StatusBadge :label="row.status_label" :variant="row.status_variant" />
                            </template>

                            <template #cell-block_reason="{ value }">
                                <span class="block max-w-sm whitespace-normal text-sm text-gray-600 dark:text-gray-300">
                                    {{ value }}
                                </span>
                            </template>

                            <template #actions="{ row }">
                                <BaseButton
                                    v-if="row.canEnroll && isEnrollmentOpen"
                                    size="sm"
                                    variant="primary"
                                    @click="openGroupModal(row)"
                                >
                                    <i class="fa-solid fa-layer-group mr-2" />
                                    {{ row.enrollmentId ? "Change Group" : "Select Group" }}
                                </BaseButton>

                                <BaseButton
                                    v-else-if="row.enrollmentId && isEnrollmentOpen"
                                    size="sm"
                                    variant="secondary"
                                    @click="openGroupModal(row)"
                                >
                                    <i class="fa-solid fa-eye mr-2" />
                                    View Group
                                </BaseButton>

                                <span v-else class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ row.block_reason }}
                                </span>
                            </template>
                        </DataTable>

                        <EmptyState
                            v-else
                            title="No curriculum subjects found"
                            description="Subjects will appear when the assigned curriculum has an academic plan."
                            icon="fa-solid fa-book"
                        />
                    </div>
                </SectionCard>
            </div>
        </CrudContainer>

        <div
            v-if="selectedSubject"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            role="dialog"
            aria-label="Class group selection"
        >
            <div class="max-h-[90vh] w-full max-w-3xl overflow-hidden rounded-lg bg-white shadow-xl dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 p-6 dark:border-gray-800">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ selectedSubject.name }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Select a published group with available capacity and a valid schedule.
                        </p>
                    </div>

                    <button class="text-gray-500 hover:text-red-500" type="button" @click="closeGroupModal">
                        <i class="fa-solid fa-xmark" />
                    </button>
                </div>

                <div class="max-h-[60vh] overflow-y-auto p-6">
                    <div v-if="loadingGroups" class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        Loading groups...
                    </div>

                    <div v-else-if="groupsError" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                        {{ groupsError }}
                    </div>

                    <EmptyState
                        v-else-if="!selectedGroups.length"
                        title="No groups available"
                        description="This subject has no published groups with schedules for the active academic period."
                        icon="fa-solid fa-calendar-xmark"
                    />

                    <div v-else class="space-y-4">
                        <div
                            v-for="group in selectedGroups"
                            :key="group.id"
                            class="rounded-lg border border-gray-200 p-4 dark:border-gray-800"
                            :class="group.isCurrent ? 'bg-indigo-50 dark:bg-indigo-500/10' : 'bg-white dark:bg-gray-900'"
                        >
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">
                                            {{ group.code }} - {{ group.name }}
                                        </h3>
                                        <StatusBadge v-if="group.isCurrent" label="CURRENT" variant="success" />
                                    </div>

                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Professor: {{ group.professor || "TBD" }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Capacity: {{ group.enrolled }}/{{ group.capacity }}
                                    </p>
                                    <p class="mt-1 max-w-xl text-sm text-gray-600 dark:text-gray-300">
                                        {{ scheduleSummary(group.schedules) }}
                                    </p>
                                </div>

                                <BaseButton
                                    size="sm"
                                    :variant="group.isCurrent ? 'secondary' : 'primary'"
                                    :disabled="group.isCurrent || submitting"
                                    @click="enrollInGroup(group)"
                                >
                                    <i class="fa-solid fa-check mr-2" />
                                    {{ group.isCurrent ? "Selected" : "Select" }}
                                </BaseButton>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 p-6 dark:border-gray-800 sm:flex-row sm:justify-between">
                    <BaseButton
                        v-if="selectedSubject.enrollmentId && selectedSubject.currentGroupId && isEnrollmentOpen"
                        variant="danger"
                        :disabled="submitting"
                        @click="unenrollFromSubject"
                    >
                        <i class="fa-solid fa-user-minus mr-2" />
                        Withdraw
                    </BaseButton>

                    <div class="sm:ml-auto">
                        <BaseButton variant="secondary" :disabled="submitting" @click="closeGroupModal">
                            Close
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </CrudPageLayout>
</template>
