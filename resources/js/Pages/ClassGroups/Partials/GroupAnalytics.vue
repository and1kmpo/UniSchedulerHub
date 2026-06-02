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

            <p class="text-sm text-gray-500">
                Capacity
            </p>

            <h3 class="mt-2 text-3xl font-bold">
                {{ classGroup.capacity }}
            </h3>

        </SectionCard>

        <SectionCard class="p-6">

            <p class="text-sm text-gray-500">
                Enrolled Students
            </p>

            <h3 class="mt-2 text-3xl font-bold">
                {{
                    classGroup.subject_enrollments_count
                }}
            </h3>

        </SectionCard>

        <SectionCard class="p-6">

            <p class="text-sm text-gray-500">
                Occupancy
            </p>

            <h3 class="mt-2 text-3xl font-bold">
                {{ occupancy }}%
            </h3>

        </SectionCard>

        <SectionCard class="p-6">

            <p class="text-sm text-gray-500">
                Schedules
            </p>

            <h3 class="mt-2 text-3xl font-bold">
                {{
                    classGroup.schedules.length
                }}
            </h3>

        </SectionCard>

    </div>

</template>