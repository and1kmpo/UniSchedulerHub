<script setup>
import { computed, ref } from "vue";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import RelatedSection from "@/Components/UI/Show/RelatedSection.vue";

const props = defineProps({
    groups: {
        type: Array,
        default: () => [],
    },

    period: {
        type: Object,
        default: null,
    },
});

const selectedGroup = ref(null);

const columns = [
    { key: "subject", label: "Subject" },
    { key: "code", label: "Group" },
    { key: "knowledge_area", label: "Knowledge Area" },
    { key: "credits", label: "Credits" },
    { key: "students", label: "Students" },
];

const rows = computed(() =>
    props.groups.map((group) => ({
        id: group.id,
        subject: group.subject?.name ?? "N/A",
        code: group.code ?? group.name,
        knowledge_area: group.subject?.knowledge_area ?? "N/A",
        credits: group.subject?.credits ?? "N/A",
        students: group.subject_enrollments_count,
        source: group,
    }))
);

const openModal = (row) => {
    selectedGroup.value = row.source;
};

const closeModal = () => {
    selectedGroup.value = null;
};
</script>

<template>
    <CrudPageLayout title="My Class Groups" :subtitle="period
        ? `Assigned groups for ${period.name}`
        : 'Assigned groups for the active academic period'
        ">
        <CrudContainer>

            <RelatedSection title="Assigned Class Groups"
                description="Subjects and groups currently assigned to you">

                <DataTable v-if="rows.length" :columns="columns" :rows="rows">

                    <template #actions="{ row }">
                        <div class="flex items-center justify-center gap-2">
                            <BaseButton size="sm" variant="secondary" @click="openModal(row)">
                                <i class="fa-solid fa-eye mr-2"></i>
                                Details
                            </BaseButton>

                            <a :href="route('groups.grades.index', row.id)">
                                <BaseButton size="sm" variant="primary">
                                    <i class="fa-solid fa-clipboard-list mr-2"></i>
                                    Grades
                                </BaseButton>
                            </a>
                        </div>
                    </template>

                </DataTable>

                <EmptyState v-else title="No assigned groups"
                    description="You have no assigned class groups in the active academic period."
                    icon="fa-solid fa-users-rectangle" />

            </RelatedSection>

        </CrudContainer>

        <div v-if="selectedGroup"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            role="dialog" aria-label="Group students modal">
            <div class="w-full max-w-3xl rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900 dark:text-gray-200">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ selectedGroup.subject?.name }} - {{ selectedGroup.name }}
                    </h2>

                    <button class="text-gray-500 hover:text-red-500" @click="closeModal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <DataTable :columns="[
                    { key: 'name', label: 'Student' },
                    { key: 'document', label: 'Document' },
                    { key: 'email', label: 'Email' },
                ]" :rows="selectedGroup.subject_enrollments.map((enrollment) => ({
                    id: enrollment.id,
                    name: enrollment.student?.user?.name ?? 'N/A',
                    document: enrollment.student?.document ?? 'N/A',
                    email: enrollment.student?.user?.email ?? 'N/A',
                }))" />
            </div>
        </div>
    </CrudPageLayout>
</template>
