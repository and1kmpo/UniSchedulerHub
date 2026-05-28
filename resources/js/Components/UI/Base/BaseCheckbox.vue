<script setup>
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
        <span v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ label }}
        </span>

        <label
            class="flex h-[42px] w-full cursor-pointer items-center gap-3 rounded-xl border bg-white px-4 shadow-sm transition hover:border-indigo-300 dark:bg-gray-900 dark:hover:border-indigo-500/60"
            :class="[
                modelValue
                    ? 'border-indigo-500 ring-2 ring-indigo-500/15 dark:border-indigo-400'
                    : 'border-gray-300 dark:border-gray-700',
                error
                    ? 'border-red-500 ring-2 ring-red-500/15 dark:border-red-400'
                    : '',
            ]"
        >
            <span
                class="flex h-5 w-5 shrink-0 items-center justify-center rounded border transition"
                :class="modelValue
                    ? 'border-indigo-600 bg-indigo-600 text-white dark:border-indigo-400 dark:bg-indigo-500'
                    : 'border-gray-300 bg-white text-transparent dark:border-gray-600 dark:bg-gray-950'
                "
            >
                <i class="fa-solid fa-check text-[10px]"></i>
            </span>

            <input
                type="checkbox"
                :checked="modelValue"
                @change="$emit('update:modelValue', $event.target.checked)"
                class="sr-only"
            />

            <span class="min-w-0 text-sm text-gray-700 dark:text-gray-200">
                <span
                    v-if="description"
                    class="block truncate text-gray-500 dark:text-gray-400"
                >
                    {{ description }}
                </span>

                <span v-else>
                    Enabled
                </span>
            </span>
        </label>

        <p v-if="error" class="text-sm text-red-500 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>
