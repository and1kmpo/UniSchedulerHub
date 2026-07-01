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

    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" class="block text-sm font-medium text-slate-700 dark:text-zinc-200">
            {{ label }}

            <span v-if="required" class="ml-1 text-danger">
                *
            </span>
        </label>

        <select :value="modelValue" :disabled="disabled" @change="$emit('update:modelValue', $event.target.value)"
            class="w-full rounded-lg border border-border-light bg-surface px-4 py-2.5 text-sm text-ink shadow-sm transition focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-100 dark:disabled:bg-zinc-900"
            :class="{
                'border-danger focus:border-danger focus:ring-danger/20':
                    error,
            }">
            <option value="">
                {{ placeholder }}
            </option>

            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>

        <p v-if="error" class="text-sm text-danger">
            {{ error }}
        </p>
    </div>
</template>
