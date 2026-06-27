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
        bg-slate-100
        text-slate-700
        dark:bg-zinc-800
        dark:text-zinc-300
    `,

    brand: `
        bg-brand-50
        text-brand-700
        dark:bg-brand-500/15
        dark:text-brand-300
    `,

    info: `
        bg-cyan-50
        text-cyan-700
        dark:bg-cyan-500/15
        dark:text-cyan-300
    `,

    success: `
        bg-success/10
        text-success
        dark:bg-emerald-500/15
        dark:text-emerald-300
    `,

    danger: `
        bg-danger/10
        text-danger
        dark:bg-red-500/15
        dark:text-red-300
    `,

    warning: `
        bg-warning/10
        text-amber-700
        dark:bg-amber-500/15
        dark:text-amber-300
    `,
};
</script>

<template>
    <span :class="[
        'inline-flex items-center rounded-full border border-current/10 px-3 py-1 text-xs font-semibold',
        variants[variant],
    ]">
        {{ label }}
    </span>
</template>
