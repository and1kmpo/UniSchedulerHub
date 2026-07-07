<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseTextarea from "@/Components/UI/Base/BaseTextarea.vue";
import ContextHelp from "@/Components/UI/Feedback/ContextHelp.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import { useTranslations } from "@/Components/Composables/useTranslations";
import { formatDateTime } from "@/Components/Composables/useDateTimeFormatter";

const props = defineProps({
    requests: {
        type: Object,
        required: true,
    },
    canReview: {
        type: Boolean,
        default: false,
    },
});

const { t } = useTranslations();

const columns = [
    { key: "submitted_at", label: t("academic_requests.submitted") },
    { key: "type", label: t("academic_requests.type") },
    { key: "student", label: t("academic_requests.student") },
    { key: "title", label: t("academic_requests.request") },
    { key: "status", label: t("academic_requests.status") },
];

const selectedRequest = ref(null);
const decision = ref("approve");

const decisionForm = useForm({
    decision_reason: "",
});

function statusVariant(status) {
    if (status === "approved") return "success";
    if (status === "rejected" || status === "cancelled") return "danger";
    if (status === "under_review") return "warning";
    return "brand";
}

function openDecision(request, action) {
    selectedRequest.value = request;
    decision.value = action;
    decisionForm.reset();
    decisionForm.clearErrors();
}

function closeDecision() {
    selectedRequest.value = null;
    decisionForm.reset();
}

function submitDecision() {
    if (!selectedRequest.value) return;

    decisionForm.patch(
        route(`academic-requests.${decision.value}`, selectedRequest.value.id),
        {
            preserveScroll: true,
            onSuccess: closeDecision,
        }
    );
}
</script>

<template>
    <CrudPageLayout
        :title="t('academic_requests.title')"
        :subtitle="t('academic_requests.subtitle')"
    >
        <template #actions>
            <BaseButton
                v-if="!canReview"
                as="a"
                :href="route('academic-requests.create')"
            >
                <i class="fa-solid fa-plus mr-2" />
                {{ t("academic_requests.new_request") }}
            </BaseButton>
        </template>

        <CrudContainer>
            <SectionCard class="mb-6">
                <div class="p-5">
                    <ContextHelp
                        :title="t('academic_requests.how_title')"
                        :description="t('academic_requests.how_description')"
                        icon="fa-solid fa-route"
                    />
                </div>
            </SectionCard>

            <DataTable
                v-if="requests.data.length"
                :columns="columns"
                :rows="requests.data"
            >
                <template #cell-submitted_at="{ value }">
                    <span class="font-mono text-sm text-slate-600 dark:text-zinc-300">
                        {{ formatDateTime(value) }}
                    </span>
                </template>

                <template #cell-type="{ row }">
                    <div class="space-y-1">
                        <p class="font-medium text-ink dark:text-white">
                            {{ row.type_label }}
                        </p>
                        <p class="font-mono text-xs uppercase tracking-wider text-slate-500 dark:text-zinc-400">
                            {{ row.type }}
                        </p>
                    </div>
                </template>

                <template #cell-student="{ row }">
                    <div class="space-y-1">
                        <p class="font-medium text-ink dark:text-white">
                            {{ row.student.name || t("academic_requests.student_profile") }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-zinc-400">
                            {{ row.student.program || t("academic_requests.no_program") }}
                        </p>
                    </div>
                </template>

                <template #cell-title="{ row }">
                    <div class="max-w-xl space-y-1">
                        <p class="font-medium text-ink dark:text-white">
                            {{ row.title }}
                        </p>
                        <p class="line-clamp-2 text-sm text-slate-500 dark:text-zinc-400">
                            {{ row.description }}
                        </p>
                        <p v-if="row.decision_reason" class="text-xs text-slate-500 dark:text-zinc-400">
                            {{ t("academic_requests.decision") }}: {{ row.decision_reason }}
                        </p>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <StatusBadge
                        :label="row.status_label"
                        :variant="statusVariant(row.status)"
                    />
                </template>

                <template v-if="canReview" #actions="{ row }">
                    <div v-if="canReview && !['approved', 'rejected'].includes(row.status)" class="flex justify-center gap-2">
                        <BaseButton
                            size="sm"
                            variant="success"
                            @click="openDecision(row, 'approve')"
                        >
                            <i class="fa-solid fa-check mr-2" />
                            {{ t("academic_requests.approve") }}
                        </BaseButton>

                        <BaseButton
                            size="sm"
                            variant="danger"
                            @click="openDecision(row, 'reject')"
                        >
                            <i class="fa-solid fa-xmark mr-2" />
                            {{ t("academic_requests.reject") }}
                        </BaseButton>
                    </div>

                    <span v-else class="text-sm text-slate-500 dark:text-zinc-400">
                        {{ t("academic_requests.reviewed") }}
                    </span>
                </template>
            </DataTable>

            <EmptyState
                v-else
                :title="t('academic_requests.empty_title')"
                :description="t('academic_requests.empty_description')"
                icon="fa-solid fa-inbox"
            />

            <TablePagination v-if="requests.data.length" :data="requests" />
        </CrudContainer>

        <div
            v-if="selectedRequest"
            class="fixed inset-0 z-50 flex items-end bg-ink/60 px-4 py-6 sm:items-center sm:justify-center"
            role="dialog"
            aria-modal="true"
            @click.self="closeDecision"
        >
            <section class="w-full max-w-xl rounded-xl border border-border-light bg-surface dark:border-border-dark dark:bg-surface-dark">
                <header class="border-b border-border-light px-6 py-5 dark:border-border-dark">
                    <h2 class="text-lg font-semibold text-ink dark:text-white">
                        {{ decision === "approve" ? t("academic_requests.approve_title") : t("academic_requests.reject_title") }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                        {{ t("academic_requests.decision_help") }}
                    </p>
                </header>

                <form class="space-y-5 px-6 py-5" @submit.prevent="submitDecision">
                    <BaseTextarea
                        v-model="decisionForm.decision_reason"
                        :label="t('academic_requests.decision_reason')"
                        required
                        :rows="5"
                        :placeholder="t('academic_requests.decision_placeholder')"
                        :error="decisionForm.errors.decision_reason"
                    />

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <BaseButton
                            type="button"
                            variant="secondary"
                            @click="closeDecision"
                        >
                            {{ t("academic_requests.cancel") }}
                        </BaseButton>

                        <BaseButton
                            type="submit"
                            :variant="decision === 'approve' ? 'success' : 'danger'"
                            :disabled="decisionForm.processing"
                        >
                            {{ t("academic_requests.save_decision") }}
                        </BaseButton>
                    </div>
                </form>
            </section>
        </div>
    </CrudPageLayout>
</template>
