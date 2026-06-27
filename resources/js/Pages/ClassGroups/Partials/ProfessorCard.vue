<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
</script>

<template>

    <SectionCard class="p-6">

        <div class="flex items-center gap-4">

            <div
                class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-100 text-xl font-bold text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                {{
                    professor.name
                        ?.charAt(0)
                }}
            </div>

            <div>

                <p class="text-sm font-medium text-slate-600 dark:text-slate-400">
                    Professor
                </p>

                <h3 class="text-lg font-semibold text-ink dark:text-white">
                    {{
                        professor.name
                    }}
                </h3>

            </div>

        </div>

    </SectionCard>

</template>

<script>
export default {
    props: {
        professor: Object,
    },
};
</script>

