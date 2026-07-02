<script setup>
import { computed, onMounted, ref, useAttrs } from "vue";

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

const attrs = useAttrs();
const input = ref(null);

const rootClass = computed(() => attrs.class);

const inputAttrs = computed(() => {
    const { class: _class, ...rest } = attrs;

    return rest;
});

onMounted(() => {
    if (input.value?.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div class="space-y-2" :class="rootClass">
        <label v-if="label" class="block text-sm font-medium text-slate-700 dark:text-zinc-200">
            {{ label }}

            <span v-if="required" class="ml-1 text-danger">
                *
            </span>
        </label>

        <input ref="input" v-bind="inputAttrs" :value="modelValue" :type="type" :placeholder="placeholder" :disabled="disabled"
            @input="$emit('update:modelValue', $event.target.value)"
            class="base-input w-full rounded-lg border border-border-light bg-surface px-4 py-2.5 text-sm text-ink transition placeholder:text-slate-400 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-100 dark:placeholder:text-zinc-500 dark:disabled:bg-zinc-900"
            :class="{
                'border-danger focus:border-danger focus:ring-danger/20':
                    error,
            }" />

        <p v-if="error" class="text-sm text-danger">
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
