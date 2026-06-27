<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() => {
    return props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-brand-600 text-sm font-medium leading-5 text-ink focus:outline-none focus:border-brand-700 transition duration-150 ease-in-out dark:text-white'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-ink hover:border-slate-300 focus:outline-none focus:text-ink focus:border-slate-300 transition duration-150 ease-in-out dark:text-zinc-400 dark:hover:text-white dark:hover:border-zinc-600';
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
