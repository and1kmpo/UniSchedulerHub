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
        <label v-if="label" class="block text-sm font-medium text-slate-700 dark:text-zinc-200">
            {{ label }}

            <span v-if="required" class="ml-1 text-danger">
                *
            </span>
        </label>

        <textarea :value="modelValue" :placeholder="placeholder" :rows="rows"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full rounded-lg border border-border-light bg-surface px-4 py-2.5 text-sm text-ink shadow-sm transition placeholder:text-slate-400 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-100 dark:placeholder:text-zinc-500"
            :class="{
                'border-danger focus:border-danger focus:ring-danger/20':
                    error,
            }" />

        <p v-if="error" class="text-sm text-danger">
            {{ error }}
        </p>
    </div>
</template>
