<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";

import StudentRosterTable from "../StudentRosterTable.vue";

defineProps({
    students: {
        type: Array,
        default: () => [],
    },
});

defineEmits(["unenroll"]);
</script>

<template>
    <section>
        <SectionCard class="p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-ink dark:text-white">
                    Enrolled Students
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Active roster for this class group.
                </p>
            </div>

            <StudentRosterTable :students="students" @unenroll="$emit('unenroll', $event)" />
        </SectionCard>
    </section>
</template>

