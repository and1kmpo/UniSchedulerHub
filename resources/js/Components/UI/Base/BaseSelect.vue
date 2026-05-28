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

    label: {
        type: String,
        default: "",
    },

    placeholder: {
        type: String,
        default: "Select option",
    },

    error: {
        type: String,
        default: "",
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

        <select :value="modelValue" @change="$emit('update:modelValue', $event.target.value)"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            :class="{
                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                    error,
            }">
            <option value="">
                {{ placeholder }}
            </option>

            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>

        <p v-if="error" class="text-sm text-red-500">
            {{ error }}
        </p>
    </div>
</template>
