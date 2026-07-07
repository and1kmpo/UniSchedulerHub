<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center px-4 py-2 bg-surface border border-border-light rounded-md font-semibold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-offset-dark-bg">
        <slot />
    </button>
</template>
