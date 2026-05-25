<script setup>
defineProps({
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
        "bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500",

    secondary:
        "bg-gray-200 hover:bg-gray-300 text-gray-800 focus:ring-gray-400",

    danger:
        "bg-red-600 hover:bg-red-700 text-white focus:ring-red-500",

    success:
        "bg-emerald-600 hover:bg-emerald-700 text-white focus:ring-emerald-500",

    warning:
        "bg-amber-500 hover:bg-amber-600 text-white focus:ring-amber-400",
};

const sizes = {
    sm: "px-3 py-1.5 text-sm",
    md: "px-4 py-2 text-sm",
    lg: "px-6 py-3 text-base",
};
</script>

<template>
    <button :type="type" :disabled="disabled" :class="[
        'inline-flex items-center justify-center rounded-xl font-medium transition-all duration-200 focus:outline-none focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed',
        variants[variant],
        sizes[size],
    ]">
        <slot />
    </button>
</template>