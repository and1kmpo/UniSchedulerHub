<script setup>
defineOptions({
    inheritAttrs: false,
});

defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
    },

    label: {
        type: String,
        default: "",
    },

    type: {
        type: String,
        default: "text",
    },

    placeholder: {
        type: String,
        default: "",
    },

    error: {
        type: String,
        default: "",
    },

    disabled: {
        type: Boolean,
        default: false,
    },

    required: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ label }}

            <span v-if="required" class="ml-1 text-red-500">
                *
            </span>
        </label>

        <input v-bind="$attrs" :value="modelValue" :type="type" :placeholder="placeholder" :disabled="disabled"
            @input="$emit('update:modelValue', $event.target.value)"
            class="base-input w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-indigo-400"
            :class="{
                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                    error,
            }" />

        <p v-if="error" class="text-sm text-red-500">
            {{ error }}
        </p>
    </div>
</template>

<style scoped>
.base-input[type="date"]::-webkit-calendar-picker-indicator {
    opacity: 0.75;
}

.dark .base-input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    opacity: 0.85;
}
</style>
