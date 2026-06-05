<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

const props = defineProps({
    classGroup: Object,
});

const enrolled =
    props.classGroup
        .subject_enrollments_count;

const capacity =
    props.classGroup.capacity;

const available =
    capacity - enrolled;

const occupancy = Math.round(
    (enrolled / capacity) * 100
);
</script>

<template>

    <div class="grid gap-4 md:grid-cols-4">

        <SectionCard class="p-6">

            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Enrolled
            </p>

            <h3 class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">
                {{ enrolled }}
            </h3>

        </SectionCard>

        <SectionCard class="p-6">

            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Capacity
            </p>

            <h3 class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">
                {{ capacity }}
            </h3>

        </SectionCard>

        <SectionCard class="p-6">

            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Available Seats
            </p>

            <h3 class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">
                {{ available }}
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

    </div>

</template>
