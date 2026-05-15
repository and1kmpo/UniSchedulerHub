<script setup>
defineProps({
    padded: {
        type: Boolean,
        default: true,
    },

    shadow: {
        type: Boolean,
        default: true,
    },

    bordered: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div :class="[
        'overflow-hidden rounded-2xl bg-white transition-all duration-300 dark:bg-gray-800',

        padded ? 'p-4 sm:p-6' : '',

        shadow
            ? 'shadow-sm hover:shadow-md'
            : '',

        bordered
            ? 'border border-gray-200 dark:border-gray-700'
            : '',
    ]">
        <slot />
    </div>
</template>