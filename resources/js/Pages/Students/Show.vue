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
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

import ShowSection from "@/Components/UI/Show/ShowSection.vue";
import InfoGrid from "@/Components/UI/Show/InfoGrid.vue";
import InfoItem from "@/Components/UI/Show/InfoItem.vue";
import StatsGrid from "@/Components/UI/Show/StatsGrid.vue";
import RelatedSection from "@/Components/UI/Show/RelatedSection.vue";

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },

    enrollments: {
        type: Object,
        required: true,
    },
});

const columns = [
    { key: "subject", label: "Subject" },
    { key: "period", label: "Academic Period" },
    { key: "group", label: "Group" },
    { key: "status", label: "Status" },
    { key: "final_grade", label: "Final Grade" },
];

const rows = computed(() =>
    props.enrollments.data.map((enrollment) => ({
        id: enrollment.id,
        subject: enrollment.subject?.name ?? "N/A",
        period: enrollment.academic_period?.name ?? enrollment.academic_period?.code ?? "N/A",
        group: enrollment.class_group?.code ?? enrollment.class_group?.name ?? "N/A",
        status: enrollment.status?.code ?? "N/A",
        final_grade: enrollment.grade?.final_grade ?? "Pending",
    }))
);
</script>

<template>
    <CrudPageLayout :title="student.user?.name" subtitle="Student profile and academic history">
        <template #actions>
            <Link :href="route('students.edit', student.id)">
                <BaseButton variant="primary">
                    <i class="fa-solid fa-pen mr-2"></i>
                    Edit Student
                </BaseButton>
            </Link>

            <Link :href="route('students.index')">
                <BaseButton variant="secondary">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back
                </BaseButton>
            </Link>
        </template>

        <CrudContainer class="space-y-6">

            <StatsGrid>

                <StatCard title="Semester" :value="student.semester" icon="fa-solid fa-layer-group" />

                <StatCard title="Enrollments" :value="student.enrollments_count" icon="fa-solid fa-book-open" />

                <StatCard title="Grades" :value="student.enrollment_grades_count" icon="fa-solid fa-chart-line" />

                <StatCard title="Status" :value="student.academic_status?.replace('_', ' ').toUpperCase()"
                    icon="fa-solid fa-circle-check" />

            </StatsGrid>

            <ShowSection title="Student Information" description="Identity, contact and academic placement">
                <InfoGrid>

                    <InfoItem label="Name" :value="student.user?.name" />

                    <InfoItem label="Document" :value="student.document" />

                    <InfoItem label="Email" :value="student.user?.email" />

                    <InfoItem label="Phone" :value="student.phone" />

                    <InfoItem label="Program" :value="student.program?.name ?? 'N/A'" />

                    <InfoItem label="Curriculum" :value="student.curriculum?.name ?? 'N/A'" />

                    <InfoItem label="City" :value="student.city" />

                    <InfoItem label="Address" :value="student.address" />

                    <InfoItem label="Academic Status">
                        <StatusBadge :label="student.academic_status?.replace('_', ' ').toUpperCase() || 'N/A'"
                            :variant="{
                                active: 'success',
                                probation: 'warning',
                                suspended: 'danger',
                                graduated: 'success',
                                withdrawn: 'gray',
                            }[student.academic_status] || 'gray'" />
                    </InfoItem>

                </InfoGrid>
            </ShowSection>

            <RelatedSection title="Academic History" description="Enrollments, groups and grades connected to this student">

                <DataTable v-if="rows.length" :columns="columns" :rows="rows">

                    <template #cell-status="{ value }">
                        <StatusBadge :label="value.replace('_', ' ').toUpperCase()" :variant="{
                            enrolled: 'success',
                            pre_enrolled: 'warning',
                            approved: 'success',
                            failed: 'danger',
                            cancelled: 'gray',
                            withdrawn: 'gray',
                        }[value] || 'gray'" />
                    </template>

                </DataTable>

                <EmptyState v-else title="No academic history" description="This student has no enrollments yet."
                    icon="fa-solid fa-book-open" />

                <TablePagination v-if="rows.length" :data="enrollments" />

            </RelatedSection>

        </CrudContainer>
    </CrudPageLayout>
</template>

