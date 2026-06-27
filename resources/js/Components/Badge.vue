<script setup>
defineProps({
    color: {
        type: String,
        default: 'gray',
    },
    text: {
        type: String,
        required: true,
    },
})
</script>

<template>
    <span :class="[
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize',
        {
            'bg-success/10 text-emerald-700 dark:text-emerald-300': color === 'green' || color === 'success',
            'bg-danger/10 text-danger dark:text-red-300': color === 'red' || color === 'danger',
            'bg-warning/10 text-amber-700 dark:text-amber-300': color === 'yellow' || color === 'warning',
            'bg-brand/10 text-brand dark:text-brand': color === 'blue' || color === 'brand',
            'bg-accent/10 text-cyan-700 dark:text-accent': color === 'purple' || color === 'accent',
            'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-200': color === 'gray',
        }
    ]">
        {{ text }}
    </span>
</template>
