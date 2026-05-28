<script setup>
defineProps({
    modelValue: {
        type: String,
        default: "",
    },

    label: {
        type: String,
        default: "",
    },

    placeholder: {
        type: String,
        default: "",
    },

    rows: {
        type: Number,
        default: 4,
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

        <textarea :value="modelValue" :placeholder="placeholder" :rows="rows"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            :class="{
                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                    error,
            }" />

        <p v-if="error" class="text-sm text-red-500">
            {{ error }}
        </p>
    </div>
</template>