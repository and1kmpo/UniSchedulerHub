<script setup>
defineProps({
    modelValue: String,
});

defineEmits([
    "update:modelValue",
]);
</script>

<template>

    <div class="relative">

        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />

        <input :value="modelValue" type="text" placeholder="Search students..."
            class="w-full rounded-lg border border-border-light bg-surface py-2.5 pl-10 pr-4 text-sm text-ink shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-600/20 dark:border-border-dark dark:bg-dark-bg dark:text-white dark:placeholder:text-slate-500"
            @input="
                $emit(
                    'update:modelValue',
                    $event.target.value
                )
                " />

    </div>

</template>
