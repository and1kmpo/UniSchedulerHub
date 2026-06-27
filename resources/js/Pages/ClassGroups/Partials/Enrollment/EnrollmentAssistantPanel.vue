<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

defineProps({
    classGroup: Object,
    selectedStudent: {
        type: Object,
        default: null,
    },
    validationResult: {
        type: Object,
        default: () => ({
            allowed: true,
        }),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["enroll"]);
</script>

<template>

    <SectionCard class="p-6">

        <div class="flex items-start gap-4">

            <div
                class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                <i class="fa-solid fa-brain" />
            </div>

            <div>

                <h3 class="text-lg font-semibold text-ink dark:text-white">
                    Smart Enrollment Assistant
                </h3>

                <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-zinc-300">

                    <p>
                        • Monitor schedule conflicts
                    </p>

                    <p>
                        • Validate academic load
                    </p>

                    <p>
                        • Detect duplicate enrollments
                    </p>

                    <p>
                        • Analyze occupancy patterns
                    </p>

                </div>

                <button type="button" :disabled="!selectedStudent || !validationResult.allowed || loading"
                    @click="$emit('enroll')" class="
                        mt-6
                        inline-flex
                        w-full
                        items-center
                        justify-center
                        rounded-lg
                        bg-brand-600
                        px-4
                        py-2
                        text-sm
                        font-semibold
                        text-white
                        transition
                        hover:bg-brand-700
                        disabled:cursor-not-allowed
                        disabled:opacity-50
                    ">
                    <i class="fa-solid fa-user-plus mr-2" />
                    Confirm Enrollment
                </button>

            </div>

        </div>

    </SectionCard>

</template>

