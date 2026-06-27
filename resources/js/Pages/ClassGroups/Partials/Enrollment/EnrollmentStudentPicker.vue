<script setup>
import {
    computed,
    ref,
} from "vue";

import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

import EnrollmentSearch from "./EnrollmentSearch.vue";

import EnrollmentStudentCard from "./EnrollmentStudentCard.vue";

const props = defineProps({
    students: Array,
    enrolledIds: Array,
});

const emit = defineEmits([
    "select",
]);

const search = ref("");

const selectedStudent =
    ref(null);

const filteredStudents =
    computed(() => {

        return props.students.filter(
            (student) => {

                const matchesSearch =
                    student.name
                        .toLowerCase()
                        .includes(
                            search.value.toLowerCase()
                        );

                const alreadyEnrolled =
                    props.enrolledIds.includes(
                        student.id
                    );

                return (
                    matchesSearch &&
                    !alreadyEnrolled
                );
            }
        );
    });

const selectStudent = (
    student
) => {

    selectedStudent.value =
        student;

    emit(
        "select",
        student
    );
};
</script>

<template>

    <SectionCard class="p-6">

        <div class="mb-6">

            <h3 class="text-lg font-semibold text-ink dark:text-white">
                Enrollment Engine
            </h3>

            <p class="text-sm text-slate-600 dark:text-slate-400">
                Search and enroll students
            </p>

        </div>

        <EnrollmentSearch v-model="search" />

        <div class="mt-6 space-y-3">

            <EnrollmentStudentCard v-for="student in filteredStudents" :key="student.id" :student="student" :selected="selectedStudent?.id === student.id
                " @click="
                    selectStudent(
                        student
                    )
                    " />

        </div>

    </SectionCard>

</template>
