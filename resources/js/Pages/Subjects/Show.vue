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
  subject: {
    type: Object,
    required: true,
  },

  students: {
    type: Object,
    required: true,
  },
});

const columns = [
  { key: "name", label: "Name" },
  { key: "document", label: "Document" },
  { key: "email", label: "Email" },
  { key: "phone", label: "Phone" },
  { key: "program", label: "Program" },
];

const mappedStudents = computed(() => {
  return props.students.data.map((student) => ({
    id: student.id,
    name: student.user?.name,
    document: student.document,
    email: student.user?.email,
    phone: student.phone,
    program: student.program?.name ?? "N/A",
  }));
});
</script>

<template>
  <CrudPageLayout :title="subject.name" subtitle="Subject details and enrolled students">
    <template #actions>
      <Link :href="route('subjects.edit', subject.id)">
        <BaseButton variant="primary">
          <i class="fa-solid fa-pen mr-2"></i>
          Edit Subject
        </BaseButton>
      </Link>

      <Link :href="route('subjects.index')">
        <BaseButton variant="secondary">
          <i class="fa-solid fa-arrow-left mr-2"></i>
          Back
        </BaseButton>
      </Link>
    </template>

    <CrudContainer class="space-y-6">

      <!-- STATS -->
      <StatsGrid>

        <StatCard title="Credits" :value="subject.credits" icon="fa-solid fa-graduation-cap" />

        <StatCard title="Knowledge Area" :value="subject.knowledge_area" icon="fa-solid fa-book" />

        <StatCard title="Students" :value="students.total" icon="fa-solid fa-users" />

        <StatCard title="Elective" :value="subject.elective ? 'YES' : 'NO'" icon="fa-solid fa-circle-check" />

      </StatsGrid>

      <!-- SUBJECT INFO -->
      <ShowSection title="General Information" description="Main subject information">
        <InfoGrid>

          <InfoItem label="Subject Name" :value="subject.name" />

          <InfoItem label="Credits" :value="subject.credits" />

          <InfoItem label="Knowledge Area" :value="subject.knowledge_area" />

          <InfoItem label="Elective">
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="subject.elective
              ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
              : 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300'
              ">
              {{ subject.elective ? "YES" : "NO" }}
            </span>
          </InfoItem>

          <InfoItem label="Description" :value="subject.description" class="md:col-span-2 xl:col-span-3" />

        </InfoGrid>
      </ShowSection>

      <!-- STUDENTS -->
      <RelatedSection title="Enrolled Students" description="Students currently enrolled in this subject">

        <DataTable v-if="mappedStudents.length" :columns="columns" :rows="mappedStudents" />

        <EmptyState v-else title="No students enrolled" description="This subject currently has no enrolled students."
          icon="fa-solid fa-users" />

        <TablePagination v-if="mappedStudents.length" :data="students" />

      </RelatedSection>

    </CrudContainer>
  </CrudPageLayout>
</template>
