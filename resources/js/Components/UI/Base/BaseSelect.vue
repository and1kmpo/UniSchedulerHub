<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
    },

    options: {
        type: Array,
        default: () => [],
    },

    placeholder: {
        type: String,
        default: "Select option",
    },
});

defineEmits(["update:modelValue"]);
</script>

<template>
    <select :value="modelValue" @change="$emit('update:modelValue', $event.target.value)"
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        <option value="">
            {{ placeholder }}
        </option>

        <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>