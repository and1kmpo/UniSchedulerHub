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
        class="rounded-lg border border-danger/20 bg-danger/10 p-6 dark:border-danger/30 dark:bg-danger/10">

        <div class="flex gap-4">

            <div class="text-danger">
                <i class="fa-solid fa-triangle-exclamation text-2xl" />
            </div>

            <div>

                <h3 class="text-lg font-semibold text-danger dark:text-danger">
                    Schedule Conflicts Detected
                </h3>

                <div class="mt-4 space-y-2">

                    <div v-for="conflict in conflicts" :key="conflict.message"
                        class="text-sm text-danger dark:text-danger">
                        • {{ conflict.message }}
                    </div>

                </div>

            </div>

        </div>

    </div>

</template>