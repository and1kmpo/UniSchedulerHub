<script setup>
defineProps({
    conflicts: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>

    <div v-if="conflicts.length"
        class="rounded-2xl border border-red-200 bg-red-50 p-6 dark:border-red-500/30 dark:bg-red-500/10">

        <div class="flex gap-4">

            <div class="text-red-500">
                <i class="fa-solid fa-triangle-exclamation text-2xl" />
            </div>

            <div>

                <h3 class="text-lg font-semibold text-red-700 dark:text-red-300">
                    Schedule Conflicts Detected
                </h3>

                <div class="mt-4 space-y-2">

                    <div v-for="conflict in conflicts" :key="conflict.message"
                        class="text-sm text-red-700 dark:text-red-300">
                        • {{ conflict.message }}
                    </div>

                </div>

            </div>

        </div>

    </div>

</template>