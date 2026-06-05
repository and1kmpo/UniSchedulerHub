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
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import { useAlert } from "@/Components/Composables/useAlert";
import { formatDate, formatTime } from "@/Components/Composables/useDateTimeFormatter";
import WeeklySchedule from "@/Pages/Students/Partials/WeeklySchedule.vue";

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
    minCredits: {
        type: Number,
        default: 7,
    },
    maxCredits: {
        type: Number,
        default: 0,
    },
    canEnroll: {
        type: Boolean,
        default: false,
    },
    canUnenroll: {
        type: Boolean,
        default: false,
    },
    currentPeriod: {
        type: Object,
        default: null,
    },
    systemState: {
        type: String,
        default: "ready",
    },
});

const { toastSuccess, toastError, confirm } = useAlert();

const selectedSubject = ref(null);
const loadingGroups = ref(false);
const submitting = ref(false);
const confirmingEnrollment = ref(false);
const groupsError = ref(null);
const selectedProfessorFilter = ref("");

const columns = [
    { key: "name", label: "Subject" },
    { key: "credits", label: "Credits" },
    { key: "semester", label: "Semester" },
    { key: "availableGroupsCount", label: "Groups" },
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

const isEnrollmentOpen = computed(() => props.canEnroll);

const hasPendingEnrollment = computed(() =>
    props.subjects.some((subject) => subject.status === "pre_enrolled")
);

const meetsMinimumCredits = computed(() => props.currentCredits >= props.minCredits);

const missingCredits = computed(() =>
    Math.max(props.minCredits - props.currentCredits, 0)
);

const canConfirmEnrollment = computed(() =>
    isEnrollmentOpen.value
    && hasPendingEnrollment.value
    && meetsMinimumCredits.value
    && !confirmingEnrollment.value
);

const selectedGroups = computed(() => selectedSubject.value?.groups ?? []);

const professorOptions = computed(() => {
    const professors = selectedGroups.value
        .map((group) => group.professor)
        .filter(Boolean);

    return [...new Set(professors)]
        .sort()
        .map((professor) => ({
            value: professor,
            label: professor,
        }));
});

const filteredSelectedGroups = computed(() => {
    if (!selectedProfessorFilter.value) {
        return selectedGroups.value;
    }

    return selectedGroups.value.filter(
        (group) => group.professor === selectedProfessorFilter.value
    );
});

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
        .map((schedule) => `${formatStatus(schedule.day)} ${formatTime(schedule.start_time)}-${formatTime(schedule.end_time)}`)
        .join("; ");
}

function capacityVariant(group) {
    if (group.availableSeats <= 0) {
        return "danger";
    }

    if (group.availableSeats <= 5) {
        return "warning";
    }

    return "success";
}

function capacityLabel(group) {
    if (group.availableSeats <= 0) {
        return "FULL";
    }

    return `${group.availableSeats} seats left`;
}

function validationState(group) {
    if (group.isCurrent) {
        return {
            label: "CURRENT",
            variant: "success",
        };
    }

    if (!group.validation?.allowed) {
        return {
            label: "BLOCKED",
            variant: "danger",
        };
    }

    if (group.validation?.warnings?.length) {
        return {
            label: "REVIEW",
            variant: "warning",
        };
    }

    return {
        label: "AVAILABLE",
        variant: "success",
    };
}

function selectionButtonLabel(group) {
    if (group.isCurrent) {
        return "Selected";
    }

    if (!group.validation?.allowed) {
        return "Unavailable";
    }

    return "Select";
}

function validationMessages(group) {
    return [
        ...(group.validation?.errors ?? []).map((message) => ({
            type: "error",
            icon: "fa-solid fa-circle-exclamation",
            text: message,
        })),
        ...(group.validation?.warnings ?? []).map((message) => ({
            type: "warning",
            icon: "fa-solid fa-triangle-exclamation",
            text: message,
        })),
        ...(group.validation?.recommendations ?? [])
            .filter((recommendation) => recommendation.type !== "ready")
            .map((recommendation) => ({
                type: recommendation.priority === "high" ? "warning" : "info",
                icon: "fa-solid fa-lightbulb",
                text: recommendation.message ?? recommendation,
            })),
    ];
}

function messageClasses(type) {
    return {
        error: "border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300",
        warning: "border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300",
        info: "border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300",
    }[type] || "border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300";
}

async function openGroupModal(subject) {
    selectedSubject.value = {
        ...subject,
        groups: [],
    };
    groupsError.value = null;
    selectedProfessorFilter.value = "";
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
    selectedProfessorFilter.value = "";
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
    if (!props.canUnenroll) {
        toastError("Unenrollment is closed for the active academic period.");
        return;
    }

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

async function confirmPeriodEnrollment() {
    if (!hasPendingEnrollment.value) {
        toastError("There are no pending enrollments to confirm.");
        return;
    }

    if (!meetsMinimumCredits.value) {
        toastError(`Add ${missingCredits.value} more credits before confirming enrollment.`);
        return;
    }

    const accepted = await confirm(
        "This will confirm your selected subjects for the active academic period.",
        "Confirm enrollment"
    );

    if (!accepted) {
        return;
    }

    confirmingEnrollment.value = true;

    try {
        const { data } = await axios.post(route("api.enrollments.confirm-period"));

        toastSuccess(data.message || "Enrollment confirmed successfully.");
        router.reload({ only: ["subjects", "currentSchedules", "currentCredits"] });
    } catch (error) {
        toastError(
            error.response?.data?.message
            || error.response?.data?.error
            || "Enrollment confirmation failed."
        );
    } finally {
        confirmingEnrollment.value = false;
    }
}
</script>

<template>
    <CrudPageLayout title="Subject Enrollment" subtitle="Select available subjects and class groups for the active academic period">
        <CrudContainer>
            <div class="space-y-6">
                <section class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    <StatCard title="Current Credits" :value="currentCredits" icon="fa-solid fa-layer-group" />
                    <StatCard title="Minimum Credits" :value="minCredits" icon="fa-solid fa-list-check" />
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
                                {{ currentPeriod?.name ?? "No active academic period" }}. Enrolled subjects: {{ enrolledSubjects }}.
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
                            <p class="mt-1 text-gray-900 dark:text-white">{{ formatDate(enrollmentDeadline, "Not defined") }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-500 dark:text-gray-400">Unenrollment deadline</p>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ formatDate(unenrollmentDeadline, "Not defined") }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 border-t border-gray-200 p-6 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                Enrollment confirmation
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <span v-if="meetsMinimumCredits">
                                    Minimum credit load met. Confirm pending selections when your schedule is ready.
                                </span>
                                <span v-else>
                                    Add {{ missingCredits }} more credits to reach the minimum required load.
                                </span>
                            </p>
                        </div>

                        <BaseButton
                            variant="success"
                            :disabled="!canConfirmEnrollment"
                            @click="confirmPeriodEnrollment"
                        >
                            <i class="fa-solid fa-circle-check mr-2" />
                            {{ confirmingEnrollment ? "Confirming..." : "Confirm Enrollment" }}
                        </BaseButton>
                    </div>
                </SectionCard>

                <SectionCard>
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            My Weekly Schedule
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Review your current selections before confirming the academic period enrollment.
                        </p>
                    </div>

                    <div class="p-6">
                        <WeeklySchedule :schedules="currentSchedules" />
                    </div>
                </SectionCard>

                <SectionCard v-if="systemState === 'no_curriculum'">
                    <EmptyState
                        title="Academic setup pending"
                        description="Your student profile does not have an active curriculum assigned. Please contact academic administration."
                        icon="fa-solid fa-triangle-exclamation"
                    />
                </SectionCard>

                <SectionCard v-else-if="systemState === 'no_period'">
                    <EmptyState
                        title="No active academic period"
                        description="Enrollment will be available when academic administration activates a period."
                        icon="fa-solid fa-calendar-xmark"
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

                            <template #cell-availableGroupsCount="{ value }">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ value || 0 }} {{ value === 1 ? "group" : "groups" }}
                                </span>
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
                                    v-else-if="row.enrollmentId"
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
            <div class="max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-lg bg-white shadow-xl dark:bg-gray-900">
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
                        <div class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Compare available groups
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Filter by professor and compare schedule, capacity, modality and shift before selecting.
                                </p>
                            </div>

                            <div class="w-full sm:max-w-xs">
                                <BaseSelect
                                    v-model="selectedProfessorFilter"
                                    :options="professorOptions"
                                    placeholder="All professors"
                                />
                            </div>
                        </div>

                        <EmptyState
                            v-if="!filteredSelectedGroups.length"
                            title="No groups match this filter"
                            description="Change the professor filter to review other available groups."
                            icon="fa-solid fa-filter-circle-xmark"
                        />

                        <div
                            v-for="group in filteredSelectedGroups"
                            :key="group.id"
                            class="rounded-lg border border-gray-200 p-4 dark:border-gray-800"
                            :class="group.isCurrent ? 'bg-indigo-50 dark:bg-indigo-500/10' : 'bg-white dark:bg-gray-900'"
                        >
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">
                                            {{ group.code }} - {{ group.name }}
                                        </h3>
                                        <StatusBadge v-if="group.isCurrent" label="CURRENT" variant="success" />
                                        <StatusBadge v-else :label="validationState(group).label" :variant="validationState(group).variant" />
                                        <StatusBadge :label="capacityLabel(group)" :variant="capacityVariant(group)" />
                                    </div>

                                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                            <dt class="font-medium text-gray-500 dark:text-gray-400">Professor</dt>
                                            <dd class="mt-1 text-gray-900 dark:text-white">{{ group.professor || "TBD" }}</dd>
                                        </div>

                                        <div>
                                            <dt class="font-medium text-gray-500 dark:text-gray-400">Capacity</dt>
                                            <dd class="mt-1 text-gray-900 dark:text-white">{{ group.enrolled }}/{{ group.capacity }}</dd>
                                        </div>

                                        <div>
                                            <dt class="font-medium text-gray-500 dark:text-gray-400">Modality</dt>
                                            <dd class="mt-1 text-gray-900 dark:text-white">{{ group.modality || "TBD" }}</dd>
                                        </div>

                                        <div>
                                            <dt class="font-medium text-gray-500 dark:text-gray-400">Shift</dt>
                                            <dd class="mt-1 text-gray-900 dark:text-white">{{ group.shift || "TBD" }}</dd>
                                        </div>
                                    </dl>

                                    <div class="mt-4 rounded-lg bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        <span class="font-medium text-gray-500 dark:text-gray-400">Schedule:</span>
                                        {{ scheduleSummary(group.schedules) }}
                                    </div>

                                    <div v-if="validationMessages(group).length" class="mt-4 space-y-2">
                                        <div
                                            v-for="message in validationMessages(group)"
                                            :key="`${message.type}-${message.text}`"
                                            class="flex gap-2 rounded-lg border px-3 py-2 text-xs"
                                            :class="messageClasses(message.type)"
                                        >
                                            <i :class="[message.icon, 'mt-0.5']" />
                                            <span>{{ message.text }}</span>
                                        </div>
                                    </div>
                                </div>

                                <BaseButton
                                    size="sm"
                                    :variant="group.isCurrent ? 'secondary' : 'primary'"
                                    :disabled="group.isCurrent || submitting || !group.canSelect"
                                    @click="enrollInGroup(group)"
                                >
                                    <i class="fa-solid fa-check mr-2" />
                                    {{ selectionButtonLabel(group) }}
                                </BaseButton>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 p-6 dark:border-gray-800 sm:flex-row sm:justify-between">
                    <BaseButton
                        v-if="selectedSubject.enrollmentId && selectedSubject.currentGroupId && canUnenroll"
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
