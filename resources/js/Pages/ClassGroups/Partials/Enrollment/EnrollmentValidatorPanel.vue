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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Enrollment Validation
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Duplicate, capacity, schedule and academic load checks.
                </p>
            </div>

            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="result.allowed
                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300'
                ">
                {{ loading ? "Checking" : result.allowed ? "Allowed" : "Blocked" }}
            </span>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Errors
                </p>

                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ result.errors?.length || 0 }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Conflicts
                </p>

                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ result.conflicts?.length || 0 }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Slots
                </p>

                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ result.available_slots ?? 0 }}
                </p>
            </div>
        </div>
    </SectionCard>
</template>
