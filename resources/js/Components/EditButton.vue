<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String
});
</script>

<template>
    <Link :href="href"
        class="inline-flex items-center justify-center text-indigo-800 dark:text-indigo-400 transition ease-in-out hover:-translate-y-1 hover:scale-110 duration-300"
        title="Edit">
    <i class="fas fa-edit"></i>
    </Link>
</template>
