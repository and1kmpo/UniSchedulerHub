<script setup>
defineOptions({
    inheritAttrs: false,
});

defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },

    label: {
        type: String,
        default: "",
    },

    description: {
        type: String,
        default: "",
    },

    error: {
        type: String,
        default: "",
    },
});

defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="space-y-2">
        <span v-if="label" class="block text-sm font-medium text-slate-700 dark:text-zinc-200">
            {{ label }}
        </span>

        <label
            class="flex min-h-[42px] w-full cursor-pointer items-center gap-3 rounded-lg border bg-surface px-4 py-2 transition hover:border-brand-300 dark:bg-surface-dark dark:hover:border-brand-500/60"
            :class="[
                modelValue
                    ? 'border-brand-600 ring-2 ring-brand-600/15 dark:border-brand-400'
                    : 'border-border-light dark:border-border-dark',
                error
                    ? 'border-danger ring-2 ring-danger/15 dark:border-red-400'
                    : '',
            ]"
        >
            <span
                class="flex h-5 w-5 shrink-0 items-center justify-center rounded border transition"
                :class="modelValue
                    ? 'border-brand-600 bg-brand-600 text-white dark:border-brand-400 dark:bg-brand-500'
                    : 'border-border-light bg-surface text-transparent dark:border-border-dark dark:bg-dark-bg'
                "
            >
                <i class="fa-solid fa-check text-[10px]"></i>
            </span>

            <input
                v-bind="$attrs"
                type="checkbox"
                :checked="modelValue"
                @change="$emit('update:modelValue', $event.target.checked)"
                class="sr-only"
            />

            <span class="min-w-0 text-sm text-slate-700 dark:text-zinc-200">
                <slot>
                    <span
                        v-if="description"
                        class="block text-slate-500 dark:text-zinc-400"
                    >
                        {{ description }}
                    </span>

                    <span v-else>
                        Enabled
                    </span>
                </slot>
            </span>
        </label>

        <p v-if="error" class="text-sm text-danger dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>
