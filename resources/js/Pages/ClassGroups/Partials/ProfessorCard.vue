<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
</script>

<template>

    <SectionCard class="p-6">

        <div class="flex items-center gap-4">

            <div
                class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100 text-xl font-bold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                {{
                    professor.name
                        ?.charAt(0)
                }}
            </div>

            <div>

                <p class="text-sm text-gray-500">
                    Professor
                </p>

                <h3 class="text-lg font-semibold">
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