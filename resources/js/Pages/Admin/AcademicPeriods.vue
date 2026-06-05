<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseInput from "@/Components/UI/Base/BaseInput.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import { useAlert } from "@/Components/Composables/useAlert";
import { formatDate, toDateInput } from "@/Components/Composables/useDateTimeFormatter";

const { confirm, toastSuccess, toastError } = useAlert();

const props = defineProps({
    periods: {
        type: Object,
        required: true,
    },
});

const form = ref(emptyForm());
const editingId = ref(null);
const errors = ref({});
const isSubmitting = ref(false);

const rows = computed(() => props.periods?.data ?? []);
const activePeriod = computed(() => rows.value.find((period) => period.is_active) ?? null);

const summary = computed(() => ({
    total: props.periods?.total ?? rows.value.length,
    active: activePeriod.value?.name ?? "None",
    inProgress: rows.value.filter((period) => period.status?.code === "in_progress").length,
}));

const columns = [
    { key: "name", label: "Name" },
    { key: "date_range", label: "Dates" },
    { key: "deadlines", label: "Deadlines" },
    { key: "status", label: "Lifecycle" },
    { key: "activity", label: "Activity" },
];

function emptyForm() {
    return {
        name: "",
        start_date: "",
        end_date: "",
        enrollment_deadline: "",
        unenrollment_deadline: "",
        is_active: false,
    };
}

function resetForm() {
    form.value = emptyForm();
    editingId.value = null;
    errors.value = {};
}

function edit(period) {
    form.value = {
        name: period.name,
        start_date: toDateInput(period.start_date),
        end_date: toDateInput(period.end_date),
        enrollment_deadline: toDateInput(period.enrollment_deadline),
        unenrollment_deadline: toDateInput(period.unenrollment_deadline),
        is_active: Boolean(period.is_active),
    };
    editingId.value = period.id;
    errors.value = {};
}

async function submit() {
    isSubmitting.value = true;
    errors.value = {};

    try {
        if (editingId.value) {
            await axios.put(`/academic-periods/${editingId.value}`, form.value);
            toastSuccess("Academic period updated successfully");
        } else {
            await axios.post("/academic-periods", form.value);
            toastSuccess("Academic period created successfully");
        }

        resetForm();
        reload();
    } catch (exception) {
        errors.value = exception.response?.data?.errors ?? {};
        toastError(
            exception.response?.data?.error ||
            exception.response?.data?.message ||
            "The academic period could not be saved"
        );
    } finally {
        isSubmitting.value = false;
    }
}

async function destroy(period) {
    const confirmed = await confirm(
        "Only academic periods without groups or enrollments can be deleted.",
        "Delete academic period?"
    );

    if (!confirmed) {
        return;
    }

    router.delete(`/academic-periods/${period.id}`, {
        preserveScroll: true,
        onSuccess: () => toastSuccess("Academic period deleted successfully"),
        onError: () => toastError("The academic period could not be deleted"),
    });
}

async function runAction(period, action) {
    const confirmed = await confirm(action.confirm, action.label);

    if (!confirmed) {
        return;
    }

    router.post(action.url(period), {}, {
        preserveScroll: true,
        onSuccess: () => toastSuccess(action.success),
        onError: (response) => {
            toastError(Object.values(response ?? {})[0] || "The lifecycle action could not be completed");
        },
    });
}

function activate(period) {
    router.post(`/academic-periods/${period.id}/activate`, { _method: "patch" }, {
        preserveScroll: true,
        onSuccess: () => toastSuccess("Academic period activated successfully"),
        onError: () => toastError("The academic period could not be activated"),
    });
}

function reload() {
    router.reload({
        only: ["periods"],
        preserveScroll: true,
    });
}

function actionsFor(period) {
    const code = period.status?.code;

    return [
        {
            key: "open",
            label: "Open Enrollment",
            visible: code === "draft",
            url: (item) => `/academic-periods/${item.id}/open-enrollment`,
            confirm: "Students will be allowed to enroll during this academic period.",
            success: "Enrollment opened successfully",
        },
        {
            key: "close-enrollment",
            label: "Close Enrollment",
            visible: code === "enrollment_open",
            url: (item) => `/academic-periods/${item.id}/close-enrollment`,
            confirm: "Pre-enrollments will become active enrollments.",
            success: "Enrollment closed successfully",
        },
        {
            key: "start",
            label: "Start Period",
            visible: code === "enrollment_closed",
            url: (item) => `/academic-periods/${item.id}/start`,
            confirm: "Classes and grade management will move into active academic execution.",
            success: "Academic period started successfully",
        },
        {
            key: "close",
            label: "Close Academically",
            visible: code === "in_progress",
            url: (item) => `/academic-periods/${item.id}/close`,
            confirm: "Grades and academic changes will be locked for this period.",
            success: "Academic period closed successfully",
        },
        {
            key: "archive",
            label: "Archive",
            visible: code === "academically_closed",
            url: (item) => `/academic-periods/${item.id}/archive`,
            confirm: "This period will be archived for historical reference.",
            success: "Academic period archived successfully",
        },
    ].filter((action) => action.visible);
}

function statusVariant(period) {
    return {
        draft: "gray",
        enrollment_open: "warning",
        enrollment_closed: "warning",
        in_progress: "success",
        academically_closed: "danger",
        archived: "gray",
    }[period.status?.code] || "gray";
}

function statusLabel(period) {
    return period.status?.name || period.status?.code || "No status";
}

function firstError(field) {
    return Array.isArray(errors.value[field]) ? errors.value[field][0] : errors.value[field];
}
</script>

<template>
    <CrudPageLayout title="Academic Periods"
        subtitle="Manage enrollment windows, active execution and academic closure">
        <div class="space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <SectionCard class="p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Periods</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ summary.total }}</p>
                </SectionCard>

                <SectionCard class="p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Period</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ summary.active }}</p>
                </SectionCard>

                <SectionCard class="p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">In Progress</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ summary.inProgress }}</p>
                </SectionCard>
            </div>

            <SectionCard>
                <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ editingId ? "Edit Period" : "Create Period" }}
                    </h2>
                </div>

                <form class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3" @submit.prevent="submit">
                    <BaseInput v-model="form.name" label="Name" placeholder="2026-I" required
                        :error="firstError('name')" />
                    <BaseInput v-model="form.start_date" label="Start Date" type="date" required
                        :error="firstError('start_date')" />
                    <BaseInput v-model="form.end_date" label="End Date" type="date" required
                        :error="firstError('end_date')" />
                    <BaseInput v-model="form.enrollment_deadline" label="Enrollment Deadline" type="date"
                        :error="firstError('enrollment_deadline')" />
                    <BaseInput v-model="form.unenrollment_deadline" label="Unenrollment Deadline" type="date"
                        :error="firstError('unenrollment_deadline')" />

                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-700">
                        <input id="is_active" v-model="form.is_active" type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Mark as active
                        </label>
                    </div>

                    <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-3">
                        <BaseButton type="submit" :disabled="isSubmitting">
                            <i class="fa-solid fa-floppy-disk mr-2" />
                            {{ editingId ? "Update" : "Create" }}
                        </BaseButton>
                        <BaseButton type="button" variant="secondary" @click="resetForm">
                            Clear
                        </BaseButton>
                    </div>
                </form>
            </SectionCard>

            <SectionCard>
                <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lifecycle Board</h2>
                </div>

                <DataTable v-if="rows.length" :columns="columns" :rows="rows">
                    <template #cell-date_range="{ row }">
                        <div class="font-medium text-gray-900 dark:text-white">{{ formatDate(row.start_date) }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(row.end_date) }}</div>
                    </template>

                    <template #cell-deadlines="{ row }">
                        <div>Enroll: {{ formatDate(row.enrollment_deadline) }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Unenroll: {{ formatDate(row.unenrollment_deadline) }}
                        </div>
                    </template>

                    <template #cell-status="{ row }">
                        <div class="flex flex-col gap-2">
                            <StatusBadge :label="statusLabel(row)" :variant="statusVariant(row)" />
                            <span class="text-xs font-medium"
                                :class="row.is_active ? 'text-emerald-600' : 'text-gray-500 dark:text-gray-400'">
                                {{ row.is_active ? "Active" : "Inactive" }}
                            </span>
                        </div>
                    </template>

                    <template #cell-activity="{ row }">
                        <div>{{ row.class_groups_count ?? 0 }} groups</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ row.subject_enrollments_count ?? 0 }} enrollments
                        </div>
                    </template>

                    <template #actions="{ row }">
                        <div class="flex flex-wrap justify-center gap-2">
                            <BaseButton v-if="!row.is_active && !['academically_closed', 'archived'].includes(row.status?.code)"
                                type="button" size="sm" variant="secondary" @click="activate(row)">
                                Activate
                            </BaseButton>

                            <BaseButton v-for="action in actionsFor(row)" :key="action.key" type="button" size="sm"
                                variant="primary" @click="runAction(row, action)">
                                {{ action.label }}
                            </BaseButton>

                            <BaseButton type="button" size="sm" variant="secondary" @click="edit(row)">
                                Edit
                            </BaseButton>

                            <BaseButton type="button" size="sm" variant="danger"
                                :disabled="(row.class_groups_count ?? 0) > 0 || (row.subject_enrollments_count ?? 0) > 0"
                                @click="destroy(row)">
                                Delete
                            </BaseButton>
                        </div>
                    </template>
                </DataTable>

                <div v-else class="p-6">
                    <EmptyState title="No academic periods"
                        description="Create the first academic period to start enrollment planning."
                        icon="fa-solid fa-calendar-days" />
                </div>

                <TablePagination v-if="rows.length" :data="periods" />
            </SectionCard>
        </div>
    </CrudPageLayout>
</template>
