<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

defineProps({
    recommendations: {
        type: Array,
        default: () => [],
    },
});

const priorityClass = (priority) => ({
    high: "bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300",
    medium: "bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300",
    low: "bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300",
}[priority] || "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300");
</script>

<template>
    <SectionCard class="p-6">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Recommendations
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Suggested next steps for this enrollment.
                </p>
            </div>

            <i class="fa-solid fa-lightbulb text-amber-500" />
        </div>

        <div v-if="recommendations.length" class="space-y-3">
            <div v-for="(recommendation, index) in recommendations" :key="index"
                class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm text-gray-700 dark:text-gray-200">
                        {{ recommendation.message ?? recommendation }}
                    </p>

                    <span v-if="recommendation.priority"
                        class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold"
                        :class="priorityClass(recommendation.priority)">
                        {{ recommendation.priority }}
                    </span>
                </div>
            </div>
        </div>

        <p v-else class="text-sm text-gray-500 dark:text-gray-400">
            No recommendations available.
        </p>
    </SectionCard>
</template>
