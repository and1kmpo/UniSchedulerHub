<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
    },

    placeholder: {
        type: String,
        default: "",
    },

    type: {
        type: String,
        default: "text",
    },
});

defineEmits(["update:modelValue"]);
</script>

<template>
    <input :value="modelValue" :type="type" :placeholder="placeholder"
        @input="$emit('update:modelValue', $event.target.value)"
        class="w-full rounded-lg border border-border-light bg-surface px-4 py-2.5 text-sm text-ink transition placeholder:text-slate-400 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-100 dark:placeholder:text-zinc-500" />
</template>
