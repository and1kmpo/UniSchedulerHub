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

const props = defineProps({
    professor: {
        type: Object,
        required: true,
    },

    classGroups: {
        type: Object,
        required: true,
    },
});

const groupColumns = [
    { key: "code", label: "Group" },
    { key: "subject", label: "Subject" },
    { key: "period", label: "Academic Period" },
    { key: "students", label: "Students" },
];

const capabilityColumns = [
    { key: "name", label: "Subject" },
    { key: "credits", label: "Credits" },
    { key: "knowledge_area", label: "Knowledge Area" },
];

const groups = computed(() =>
    props.classGroups.data.map((group) => ({
        id: group.id,
        code: group.code ?? group.name,
        subject: group.subject?.name ?? "N/A",
        period: group.academic_period?.name ?? group.academic_period?.code ?? "N/A",
        students: group.subject_enrollments_count,
    }))
);
</script>

<template>
    <CrudPageLayout :title="professor.user?.name" subtitle="Professor profile, capabilities and assigned groups">
        <template #actions>
            <Link :href="route('professors.edit', professor.id)">
                <BaseButton variant="primary">
                    <i class="fa-solid fa-pen mr-2"></i>
                    Edit Professor
                </BaseButton>
            </Link>

            <Link :href="route('professors.index')">
                <BaseButton variant="secondary">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back
                </BaseButton>
            </Link>
        </template>

        <CrudContainer class="space-y-6">

            <StatsGrid>

                <StatCard title="Capabilities" :value="professor.subjects_count" icon="fa-solid fa-book" />

                <StatCard title="Groups" :value="professor.class_groups_count" icon="fa-solid fa-users-rectangle" />

                <StatCard title="Grades" :value="professor.grades_count" icon="fa-solid fa-chart-line" />

                <StatCard title="City" :value="professor.city" icon="fa-solid fa-location-dot" />

            </StatsGrid>

            <ShowSection title="Professor Information" description="Identity and contact information">
                <InfoGrid>

                    <InfoItem label="Name" :value="professor.user?.name" />

                    <InfoItem label="Email" :value="professor.user?.email" />

                    <InfoItem label="Document" :value="professor.document" />

                    <InfoItem label="Phone" :value="professor.phone" />

                    <InfoItem label="City" :value="professor.city" />

                    <InfoItem label="Address" :value="professor.address" />

                </InfoGrid>
            </ShowSection>

            <RelatedSection title="Teaching Capabilities"
                description="Subjects this professor is eligible to teach">

                <DataTable v-if="professor.subjects.length" :columns="capabilityColumns" :rows="professor.subjects" />

                <EmptyState v-else title="No capabilities assigned"
                    description="This professor has no subject capabilities assigned yet." icon="fa-solid fa-book" />

            </RelatedSection>

            <RelatedSection title="Assigned Class Groups"
                description="Class groups assigned to this professor">

                <DataTable v-if="groups.length" :columns="groupColumns" :rows="groups" />

                <EmptyState v-else title="No class groups assigned"
                    description="This professor is not assigned to any class group yet."
                    icon="fa-solid fa-users-rectangle" />

                <TablePagination v-if="groups.length" :data="classGroups" />

            </RelatedSection>

        </CrudContainer>
    </CrudPageLayout>
</template>

