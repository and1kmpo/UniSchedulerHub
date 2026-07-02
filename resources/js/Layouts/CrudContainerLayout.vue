<script setup>
defineProps({
    padded: {
        type: Boolean,
        default: true,
    },

    shadow: {
        type: Boolean,
        default: false,
    },

    bordered: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div :class="[
        'overflow-hidden rounded-xl bg-surface transition duration-200 dark:bg-surface-dark',

        padded ? 'p-4 sm:p-6' : '',

        shadow
            ? 'shadow-sm'
            : '',

        bordered
            ? 'border border-border-light dark:border-border-dark'
            : '',
    ]">
        <slot />
    </div>
</template>
