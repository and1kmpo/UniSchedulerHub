<script setup>
import { Link } from "@inertiajs/vue3";

import ApplicationLogo from "@/Components/ApplicationLogo.vue";
</script>

<template>
    <Link href="/"
        class="inline-flex max-w-[17rem] items-center justify-center rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 dark:focus:ring-offset-dark-bg">
        <ApplicationLogo />
    </Link>
</template>
