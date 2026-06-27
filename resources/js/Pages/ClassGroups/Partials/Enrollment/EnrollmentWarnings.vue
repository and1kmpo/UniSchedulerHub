<script setup>
defineProps({
    warnings: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <div v-if="warnings.length" class="space-y-3">
        <div v-for="(warning, index) in warnings" :key="index" class="
                rounded-lg
                border
                border-warning/30
                bg-warning/10
                p-4
                text-amber-700
                dark:border-warning/30
                dark:bg-warning/10
                dark:text-warning
            ">
            <div class="flex items-start gap-3">

                <i class="fa-solid fa-triangle-exclamation mt-1" />

                <div>
                    <p class="font-semibold">
                        Warning
                    </p>

                    <p class="text-sm">
                        {{ warning }}
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>