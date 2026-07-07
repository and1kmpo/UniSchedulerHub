<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-danger px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2 dark:focus:ring-offset-dark-bg">
        <slot />
    </button>
</template>
