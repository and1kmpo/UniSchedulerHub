<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
});

const classes = computed(() => {
    return props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-600 text-start text-base font-medium text-brand-700 bg-brand-50 focus:outline-none focus:text-brand-800 focus:bg-brand-100 focus:border-brand-700 transition duration-150 ease-in-out dark:bg-brand-950/30 dark:text-brand-200'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:text-ink hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:text-ink focus:bg-slate-50 focus:border-slate-300 transition duration-150 ease-in-out dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white dark:hover:border-zinc-700';
});
</script>

<template>
    <div>
        <button v-if="as == 'button'" :class="classes" class="w-full text-start">
            <slot />
        </button>

        <Link v-else :href="href" :class="classes">
            <slot />
        </Link>
    </div>
</template>
