<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

const props = defineProps({
    classGroup: Object,
});

const occupancy = Math.round(
    (
        props.classGroup
            .subject_enrollments_count /
        props.classGroup.capacity
    ) * 100
);
</script>

<template>

    <div class="grid gap-4 md:grid-cols-4">

        <SectionCard class="p-6">

            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Capacity
            </p>

            <h3 class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">
                {{ classGroup.capacity }}
            </h3>

        </SectionCard>

        <SectionCard class="p-6">

            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Enrolled Students
            </p>

            <h3 class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">
                {{
                    classGroup.subject_enrollments_count
                }}
            </h3>

        </SectionCard>

        <SectionCard class="p-6">

            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Occupancy
            </p>

            <h3 class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">
                {{ occupancy }}%
            </h3>

        </SectionCard>

        <SectionCard class="p-6">

            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Schedules
            </p>

            <h3 class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">
                {{
                    classGroup.schedules.length
                }}
            </h3>

        </SectionCard>

    </div>

</template>
