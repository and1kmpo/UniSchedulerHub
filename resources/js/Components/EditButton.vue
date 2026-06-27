<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String
});
</script>

<template>
    <Link :href="href"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-brand transition duration-200 hover:bg-brand/10 hover:text-brand-dark dark:text-blue-300 dark:hover:bg-brand/15"
        title="Edit">
    <i class="fas fa-edit"></i>
    </Link>
</template>
