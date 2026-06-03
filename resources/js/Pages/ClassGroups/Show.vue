<script setup>
import { Link } from "@inertiajs/vue3";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import GroupOverviewSection from "./Partials/Sections/GroupOverviewSection.vue";
import GroupScheduleSection from "./Partials/Sections/GroupScheduleSection.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/

const props = defineProps({
    classGroup: {
        type: Object,
        required: true,
    },

    allStudents: {
        type: Array,
        default: () => [],
    },

    enrolledIds: {
        type: Array,
        default: () => [],
    },

    classrooms: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>

    <CrudPageLayout :title="classGroup.code" :subtitle="classGroup.subject.name">
        <template #actions>
            <Link :href="route('admin.class-groups.enrollments', classGroup.id)">
                <BaseButton variant="primary">
                    <i class="fa-solid fa-user-graduate mr-2" />
                    Manage Enrollments
                </BaseButton>
            </Link>
        </template>

        <CrudContainer>

            <div class="space-y-8">

                <GroupOverviewSection :class-group="classGroup" />

                <GroupScheduleSection :class-group="classGroup" :classrooms="classrooms" />

            </div>

        </CrudContainer>

    </CrudPageLayout>

</template>
