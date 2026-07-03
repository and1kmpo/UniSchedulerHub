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
        "border-transparent bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-600 dark:bg-brand-600 dark:hover:bg-brand-700",

    secondary:
        "border-border-light bg-transparent text-slate-700 hover:border-brand-600 hover:bg-brand-50 hover:text-brand-700 focus:ring-brand-600 dark:border-border-dark dark:text-zinc-200 dark:hover:border-brand-500 dark:hover:bg-brand-500/10 dark:hover:text-white",

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
        'inline-flex items-center justify-center rounded-lg border font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-dark-bg',
        { 'pointer-events-none opacity-50': as !== 'button' && disabled },
        variants[variant],
        sizes[size],
    ]">
        <slot />
    </component>
</template>
