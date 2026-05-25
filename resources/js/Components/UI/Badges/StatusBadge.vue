<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },

    variant: {
        type: String,
        default: "gray",
    },
});

const variants = {
    gray: `
        bg-gray-100
        text-gray-700
        dark:bg-gray-800
        dark:text-gray-300
    `,

    success: `
        bg-emerald-100
        text-emerald-700
        dark:bg-emerald-500/15
        dark:text-emerald-300
    `,

    danger: `
        bg-red-100
        text-red-700
        dark:bg-red-500/15
        dark:text-red-300
    `,

    warning: `
        bg-amber-100
        text-amber-700
        dark:bg-amber-500/15
        dark:text-amber-300
    `,
};
</script>

<template>
    <span :class="[
        'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
        variants[variant],
    ]">
        {{ label }}
    </span>
</template>