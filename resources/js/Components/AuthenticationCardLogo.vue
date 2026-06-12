<script setup>
import { Link } from "@inertiajs/vue3";

import ApplicationLogo from "@/Components/ApplicationLogo.vue";
</script>

<template>
    <Link href="/"
        class="inline-flex max-w-[17rem] items-center justify-center rounded-2xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <ApplicationLogo />
    </Link>
</template>
