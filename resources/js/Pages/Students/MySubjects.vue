<script setup>
import { ref } from "vue";
import axios from "axios";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";
import RelatedSection from "@/Components/UI/Show/RelatedSection.vue";

const props = defineProps({
    subjects: {
        type: Array,
        default: () => [],
    },
});

const columns = [
    { key: "name", label: "Subject" },
    { key: "credits", label: "Credits" },
    { key: "group", label: "Group" },
    { key: "professor_name", label: "Professor" },
    { key: "status", label: "Status" },
    { key: "final_grade", label: "Final Grade" },
];

const selectedGrade = ref(null);
const selectedSubject = ref(null);
const isModalOpen = ref(false);
const showSummary = ref(false);
const allGrades = ref([]);

const rows = props.subjects.map((subject) => ({
    ...subject,
    final_grade: subject.grade?.final_grade ?? "Pending",
}));

const viewGrades = async (subject) => {
    selectedSubject.value = subject;

    try {
        const response = await axios.get(route("student.subject.grades.json", subject.id));
        selectedGrade.value = response.data.grade;
        isModalOpen.value = true;
    } catch (error) {
        selectedGrade.value = null;
        isModalOpen.value = true;
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedGrade.value = null;
    selectedSubject.value = null;
};

const loadAllGrades = async () => {
    const response = await axios.get(route("student.grades.summary"));
    allGrades.value = response.data.grades;
};

const handleOpenSummary = async () => {
    await loadAllGrades();
    showSummary.value = true;
};
</script>

<template>
    <CrudPageLayout title="My Subjects" subtitle="Current enrollments, groups and grades">
        <template #actions>
            <BaseButton variant="secondary" @click="handleOpenSummary">
                <i class="fa-solid fa-list mr-2"></i>
                Grade Summary
            </BaseButton>
        </template>

        <CrudContainer>

            <RelatedSection title="Enrolled Subjects" description="Subjects connected to your academic enrollments">

                <DataTable v-if="rows.length" :columns="columns" :rows="rows">

                    <template #cell-status="{ value }">
                        <StatusBadge :label="value ? value.replace('_', ' ').toUpperCase() : 'PENDING'" :variant="{
                            enrolled: 'success',
                            pre_enrolled: 'warning',
                            approved: 'success',
                            failed: 'danger',
                            cancelled: 'gray',
                            withdrawn: 'gray',
                        }[value] || 'gray'" />
                    </template>

                    <template #actions="{ row }">
                        <BaseButton size="sm" variant="secondary" @click="viewGrades(row)">
                            <i class="fa-solid fa-eye mr-2"></i>
                            Grades
                        </BaseButton>
                    </template>

                </DataTable>

                <EmptyState v-else title="No subjects enrolled"
                    description="Use subject enrollment to select available subjects and groups."
                    icon="fa-solid fa-book" />

            </RelatedSection>

        </CrudContainer>

        <div v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            role="dialog" aria-label="Subject grades modal">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900 dark:text-gray-200">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ selectedSubject?.name }}
                    </h2>

                    <button class="text-gray-500 hover:text-red-500" @click="closeModal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Partial 1</dt>
                        <dd>{{ selectedGrade?.partial_1 ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Partial 2</dt>
                        <dd>{{ selectedGrade?.partial_2 ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Partial 3</dt>
                        <dd>{{ selectedGrade?.partial_3 ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Activities</dt>
                        <dd>{{ selectedGrade?.activities ?? "Pending" }}</dd>
                    </div>

                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-500">Final Grade</dt>
                        <dd>{{ selectedGrade?.final_grade ?? "Pending" }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div v-if="showSummary"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
            role="dialog" aria-label="Grades summary modal">
            <div class="w-full max-w-5xl rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900 dark:text-gray-200">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Grade Summary
                    </h2>

                    <button class="text-gray-500 hover:text-red-500" @click="showSummary = false" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <DataTable :columns="[
                    { key: 'subject_name', label: 'Subject' },
                    { key: 'partial_1', label: 'P1' },
                    { key: 'partial_2', label: 'P2' },
                    { key: 'partial_3', label: 'P3' },
                    { key: 'activities', label: 'Activities' },
                    { key: 'final_grade', label: 'Final' },
                    { key: 'state', label: 'Status' },
                ]" :rows="allGrades.map((grade) => ({
                    id: grade.subject.id,
                    subject_name: grade.subject.name,
                    partial_1: grade.partial_1 ?? '-',
                    partial_2: grade.partial_2 ?? '-',
                    partial_3: grade.partial_3 ?? '-',
                    activities: grade.activities ?? '-',
                    final_grade: grade.final_grade ?? '-',
                    state: grade.state?.label ?? 'Pending',
                }))" />
            </div>
        </div>
    </CrudPageLayout>
</template>
