<script setup>
defineProps({
    conflicts: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <div v-if="conflicts.length" class="space-y-3">
        <div v-for="(conflict, index) in conflicts" :key="index" class="
                rounded-xl
                border
                border-red-200
                bg-red-50
                p-4
                dark:border-red-500/20
                dark:bg-red-500/10
            ">
            <div class="flex items-start gap-3">

                <i class="fa-solid fa-ban text-red-600 dark:text-red-300 mt-1" />

                <div>

                    <p class="font-semibold text-red-700 dark:text-red-300">
                        Conflict detected
                    </p>

                    <p class="text-sm text-red-600 dark:text-red-200">
                        {{
                            conflict.subject
                                ? `${conflict.subject} overlaps on ${conflict.day} from ${conflict.existing.start} to ${conflict.existing.end}`
                                : conflict.message ?? conflict
                        }}
                    </p>

                </div>

            </div>
        </div>
    </div>
</template>
