<script setup>
defineProps({
    student: Object,
    selected: Boolean,
});
</script>

<template>

    <button type="button" class="w-full rounded-lg border p-4 text-left transition" :class="selected
        ? 'border-brand-500 bg-brand-50 shadow-sm dark:bg-brand-500/10'
        : 'border-border-light bg-surface hover:border-brand-300 hover:bg-brand-50/40 dark:border-border-dark dark:bg-dark-bg dark:hover:border-brand-500 dark:hover:bg-brand-500/10'
        ">

        <div class="flex items-center justify-between">

            <div>

                <h4 class="font-semibold text-ink dark:text-white">
                    {{ student.name }}
                </h4>

                <p class="text-sm text-slate-600 dark:text-slate-400">
                    {{ student.document }}
                </p>

            </div>

            <div v-if="selected" class="text-brand-600">
                <i class="fa-solid fa-check" />
            </div>

        </div>

    </button>

</template>
