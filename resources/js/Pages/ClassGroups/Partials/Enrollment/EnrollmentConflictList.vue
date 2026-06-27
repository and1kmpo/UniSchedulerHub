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
                rounded-lg
                border
                border-danger/20
                bg-danger/10
                p-4
                dark:border-danger/20
                dark:bg-danger/10
            ">
            <div class="flex items-start gap-3">

                <i class="fa-solid fa-ban text-danger dark:text-danger mt-1" />

                <div>

                    <p class="font-semibold text-danger dark:text-danger">
                        Conflict detected
                    </p>

                    <p class="text-sm text-danger dark:text-danger">
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
