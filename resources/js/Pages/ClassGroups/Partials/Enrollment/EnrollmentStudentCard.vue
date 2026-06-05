<script setup>
defineProps({
    student: Object,
    selected: Boolean,
});
</script>

<template>

    <button type="button" class="w-full rounded-xl border p-4 text-left transition" :class="selected
        ? 'border-indigo-500 bg-indigo-50 shadow-sm dark:bg-indigo-500/10'
        : 'border-gray-300 bg-white hover:border-indigo-300 hover:bg-indigo-50/40 dark:border-gray-700 dark:bg-gray-950 dark:hover:border-indigo-500 dark:hover:bg-indigo-500/10'
        ">

        <div class="flex items-center justify-between">

            <div>

                <h4 class="font-semibold text-gray-900 dark:text-white">
                    {{ student.name }}
                </h4>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ student.document }}
                </p>

            </div>

            <div v-if="selected" class="text-indigo-600">
                <i class="fa-solid fa-check" />
            </div>

        </div>

    </button>

</template>
