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
                rounded-xl
                border
                border-amber-200
                bg-amber-50
                p-4
                text-amber-700
                dark:border-amber-500/20
                dark:bg-amber-500/10
                dark:text-amber-300
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