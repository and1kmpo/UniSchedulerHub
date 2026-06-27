<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

defineProps({
    result: {
        type: Object,
        default: () => ({
            allowed: true,
            errors: [],
            conflicts: [],
            warnings: [],
        }),
    },

    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <SectionCard class="p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-ink dark:text-white">
                    Enrollment Validation
                </h3>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Duplicate, capacity, schedule and academic load checks.
                </p>
            </div>

            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="result.allowed
                ? 'bg-success/10 text-success dark:bg-success/10 dark:text-success'
                : 'bg-danger/10 text-danger dark:bg-danger/10 dark:text-danger'
                ">
                {{ loading ? "Checking" : result.allowed ? "Allowed" : "Blocked" }}
            </span>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-border-light p-4 dark:border-border-dark">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Errors
                </p>

                <p class="mt-1 text-2xl font-bold text-ink dark:text-white">
                    {{ result.errors?.length || 0 }}
                </p>
            </div>

            <div class="rounded-lg border border-border-light p-4 dark:border-border-dark">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Conflicts
                </p>

                <p class="mt-1 text-2xl font-bold text-ink dark:text-white">
                    {{ result.conflicts?.length || 0 }}
                </p>
            </div>

            <div class="rounded-lg border border-border-light p-4 dark:border-border-dark">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Slots
                </p>

                <p class="mt-1 text-2xl font-bold text-ink dark:text-white">
                    {{ result.available_slots ?? 0 }}
                </p>
            </div>
        </div>
    </SectionCard>
</template>

