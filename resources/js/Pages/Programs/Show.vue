<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";
import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";

import ShowSection from "@/Components/UI/Show/ShowSection.vue";
import InfoGrid from "@/Components/UI/Show/InfoGrid.vue";
import InfoItem from "@/Components/UI/Show/InfoItem.vue";
import StatsGrid from "@/Components/UI/Show/StatsGrid.vue";
import RelatedSection from "@/Components/UI/Show/RelatedSection.vue";
import { formatDateTime } from "@/Components/Composables/useDateTimeFormatter";

const props = defineProps({
    program: {
        type: Object,
        required: true,
    },

    students: {
        type: Object,
        required: true,
    },

    subjects: {
        type: Object,
        required: true,
    },
});

const studentColumns = [
    { key: "name", label: "Name" },
    { key: "document", label: "Document" },
    { key: "email", label: "Email" },
    { key: "phone", label: "Phone" },
];

const subjectColumns = [
    { key: "name", label: "Subject" },
    { key: "credits", label: "Credits" },
    { key: "knowledge_area", label: "Knowledge Area" },
    { key: "elective", label: "Elective" },
];

const mappedStudents = computed(() => {
    return props.students.data.map((student) => ({
        id: student.id,
        name: student.user?.name,
        document: student.document,
        email: student.user?.email,
        phone: student.phone,
    }));
});
</script>

<template>
    <CrudPageLayout :title="program.name" subtitle="Academic program details and related records">
        <template #actions>
            <Link :href="route('programs.edit', program.id)">
                <BaseButton variant="primary">
                    <i class="fa-solid fa-pen mr-2"></i>
                    Edit Program
                </BaseButton>
            </Link>

            <Link :href="route('programs.index')">
                <BaseButton variant="secondary">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back
                </BaseButton>
            </Link>
        </template>

        <CrudContainer class="space-y-6">

            <StatsGrid>

                <StatCard title="Students" :value="program.students_count" icon="fa-solid fa-users" />

                <StatCard title="Subjects" :value="program.subjects_count" icon="fa-solid fa-book" />

                <StatCard title="Curricula" :value="program.curricula_count" icon="fa-solid fa-list-check" />

                <StatCard title="Active Curriculum" :value="program.active_curriculum ? 'YES' : 'NO'"
                    icon="fa-solid fa-circle-check" />

            </StatsGrid>

            <ShowSection title="General Information" description="Main academic program information">
                <InfoGrid>

                    <InfoItem label="Program Name" :value="program.name" />

                    <InfoItem label="Program ID" :value="program.id" />

                    <InfoItem label="Active Curriculum" :value="program.active_curriculum?.name ?? 'N/A'" />

                    <InfoItem label="Created At" :value="formatDateTime(program.created_at)" />

                    <InfoItem label="Description" :value="program.description" class="md:col-span-2 xl:col-span-3" />

                </InfoGrid>
            </ShowSection>

            <RelatedSection title="Program Subjects" description="Subjects assigned to this academic program">

                <DataTable v-if="subjects.data.length" :columns="subjectColumns" :rows="subjects.data">

                    <template #cell-elective="{ row }">
                        {{ row.elective ? "YES" : "NO" }}
                    </template>

                </DataTable>

                <EmptyState v-else title="No subjects assigned" description="This program currently has no subjects."
                    icon="fa-solid fa-book" />

                <TablePagination v-if="subjects.data.length" :data="subjects" />

            </RelatedSection>

            <RelatedSection title="Program Students" description="Students currently assigned to this academic program">

                <DataTable v-if="mappedStudents.length" :columns="studentColumns" :rows="mappedStudents" />

                <EmptyState v-else title="No students assigned" description="This program currently has no students."
                    icon="fa-solid fa-users" />

                <TablePagination v-if="mappedStudents.length" :data="students" />

            </RelatedSection>

        </CrudContainer>
    </CrudPageLayout>
</template>
