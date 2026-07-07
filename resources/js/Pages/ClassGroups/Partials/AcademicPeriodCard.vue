<script setup>
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

defineProps({
    period: Object,
});
</script>

<template>

    <SectionCard class="p-6">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-sm font-medium text-slate-600 dark:text-slate-400">
                    Academic Period
                </p>

                <h3 class="mt-1 text-lg font-semibold text-ink dark:text-white">
                    {{ period.name }}
                </h3>

            </div>

            <StatusBadge :label="period.is_active
                    ? 'Active'
                    : 'Closed'
                " :variant="period.is_active
                        ? 'success'
                        : 'gray'
                    " />

        </div>

    </SectionCard>

</template>

