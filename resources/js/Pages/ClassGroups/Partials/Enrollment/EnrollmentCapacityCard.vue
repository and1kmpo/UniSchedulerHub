<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

const props = defineProps({
    classGroup: Object,
});

const percentage = Math.min(
    100,
    Math.round(
        (
            props.classGroup.subject_enrollments_count /
            props.classGroup.capacity
        ) * 100
    )
);
</script>

<template>

    <SectionCard class="p-6">

        <div class="flex items-center justify-between">

            <div>

                <h3 class="text-lg font-semibold">
                    Enrollment Capacity
                </h3>

                <p class="text-sm text-gray-500">
                    Real-time occupancy monitoring
                </p>

            </div>

            <div class="text-2xl font-bold">
                {{ percentage }}%
            </div>

        </div>

        <div class="mt-6 h-3 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">

            <div class="h-full rounded-full bg-indigo-600 transition-all" :style="{
                width: `${percentage}%`
            }" />

        </div>

    </SectionCard>

</template>