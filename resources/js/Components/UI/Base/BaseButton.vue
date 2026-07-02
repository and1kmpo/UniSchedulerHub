<script setup>
defineProps({
    as: {
        type: String,
        default: "button",
    },

    href: {
        type: String,
        default: "",
    },

    type: {
        type: String,
        default: "button",
    },

    variant: {
        type: String,
        default: "primary",
    },

    size: {
        type: String,
        default: "md",
    },

    disabled: {
        type: Boolean,
        default: false,
    },
});

const variants = {
    primary:
        "border-transparent bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-600",

    secondary:
        "border-border-light bg-surface text-slate-700 hover:bg-slate-50 focus:ring-brand-600 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-200 dark:hover:bg-zinc-800",

    danger:
        "border-transparent bg-danger text-white hover:brightness-95 focus:ring-danger",

    success:
        "border-transparent bg-success text-white hover:brightness-95 focus:ring-success",

    warning:
        "border-transparent bg-warning text-white hover:brightness-95 focus:ring-warning",
};

const sizes = {
    sm: "px-3 py-1.5 text-sm",
    md: "px-4 py-2 text-sm",
    lg: "px-6 py-3 text-base",
};
</script>

<template>
    <component :is="as" :type="as === 'button' ? type : undefined" :href="as !== 'button' ? href : undefined"
        :disabled="as === 'button' ? disabled : undefined" :aria-disabled="as !== 'button' && disabled ? 'true' : undefined"
        :class="[
        'inline-flex items-center justify-center rounded-lg border font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-dark-bg',
        { 'pointer-events-none opacity-50': as !== 'button' && disabled },
        variants[variant],
        sizes[size],
    ]">
        <slot />
    </component>
</template>
