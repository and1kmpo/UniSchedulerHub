<script setup>
defineProps({
    student: Object,
    selected: Boolean,
});
</script>

<template>

    <button type="button" class="w-full rounded-xl border p-4 text-left transition" :class="selected
        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10'
        : 'border-gray-200 hover:border-indigo-300 dark:border-gray-700'
        ">

        <div class="flex items-center justify-between">

            <div>

                <h4 class="font-semibold">
                    {{ student.name }}
                </h4>

                <p class="text-sm text-gray-500">
                    {{ student.document }}
                </p>

            </div>

            <div v-if="selected" class="text-indigo-600">
                <i class="fa-solid fa-check" />
            </div>

        </div>

    </button>

</template>