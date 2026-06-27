<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },

    value: {
        type: [String, Number, Boolean],
        default: "-",
    },
});
</script>

<template>
    <div class="space-y-1">
        <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">
            {{ label }}
        </p>

        <div class="text-base font-semibold text-ink dark:text-white">
            <slot>
                {{ value || "-" }}
            </slot>
        </div>
    </div>
</template>
