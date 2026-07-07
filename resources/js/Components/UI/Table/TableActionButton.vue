<script setup>
defineProps({
    icon: {
        type: String,
        required: true,
    },

    label: {
        type: String,
        required: true,
    },

    color: {
        type: String,
        default: "gray",
    },

    type: {
        type: String,
        default: "button",
    },

    disabled: {
        type: Boolean,
        default: false,
    },
});

const colors = {
    gray: `
        text-slate-600
        hover:bg-slate-100
        hover:text-ink
        dark:text-zinc-300
        dark:hover:bg-zinc-800
    `,

    sky: `
        text-accent
        hover:bg-accent/10
        hover:text-cyan-700
        dark:text-accent
        dark:hover:bg-accent/10
    `,

    indigo: `
        text-brand
        hover:bg-brand/10
        hover:text-brand-dark
        dark:text-brand
        dark:hover:bg-brand/15
    `,

    brand: `
        text-brand
        hover:bg-brand/10
        hover:text-brand-dark
        dark:text-brand
        dark:hover:bg-brand/15
    `,

    red: `
        text-danger
        hover:bg-danger/10
        hover:text-red-700
        dark:text-red-300
        dark:hover:bg-danger/15
    `,

    danger: `
        text-danger
        hover:bg-danger/10
        hover:text-red-700
        dark:text-red-300
        dark:hover:bg-danger/15
    `,
};
</script>

<template>
    <button :type="type" :title="label" :aria-label="label" :disabled="disabled" :class="[
        'inline-flex h-9 w-9 items-center justify-center rounded-lg transition',
        disabled ? 'cursor-not-allowed opacity-40' : '',
        colors[color],
    ]">
        <i :class="icon" aria-hidden="true"></i>
        <span class="sr-only">{{ label }}</span>
    </button>
</template>
