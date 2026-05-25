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
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
</template>