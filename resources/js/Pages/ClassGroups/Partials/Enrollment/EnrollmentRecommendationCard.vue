<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

defineProps({
    recommendations: {
        type: Array,
        default: () => [],
    },
});

const priorityClass = (priority) => ({
    high: "bg-danger/10 text-danger dark:bg-danger/10 dark:text-danger",
    medium: "bg-warning/10 text-amber-700 dark:bg-warning/10 dark:text-warning",
    low: "bg-success/10 text-success dark:bg-success/10 dark:text-success",
}[priority] || "bg-slate-100 text-slate-700 dark:bg-zinc-900 dark:text-zinc-300");
</script>

<template>
    <SectionCard class="p-6">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-ink dark:text-white">
                    Recommendations
                </h3>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Suggested next steps for this enrollment.
                </p>
            </div>

            <i class="fa-solid fa-lightbulb text-warning" />
        </div>

        <div v-if="recommendations.length" class="space-y-3">
            <div v-for="(recommendation, index) in recommendations" :key="index"
                class="rounded-lg border border-border-light p-4 dark:border-border-dark">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm text-slate-700 dark:text-zinc-200">
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

        <p v-else class="text-sm text-slate-500 dark:text-slate-400">
            No recommendations available.
        </p>
    </SectionCard>
</template>
