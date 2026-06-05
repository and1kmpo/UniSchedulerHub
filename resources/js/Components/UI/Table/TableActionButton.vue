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
        text-gray-600
        hover:bg-gray-50
        hover:text-gray-900
        dark:text-gray-300
        dark:hover:bg-gray-500/10
    `,

    sky: `
        text-sky-600
        hover:bg-sky-50
        hover:text-sky-800
        dark:text-sky-300
        dark:hover:bg-sky-500/10
    `,

    indigo: `
        text-indigo-600
        hover:bg-indigo-50
        hover:text-indigo-800
        dark:text-indigo-300
        dark:hover:bg-indigo-500/10
    `,

    red: `
        text-red-600
        hover:bg-red-50
        hover:text-red-800
        dark:text-red-300
        dark:hover:bg-red-500/10
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
