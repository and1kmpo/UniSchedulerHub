<script setup>
import { Link } from "@inertiajs/vue3";

import ApplicationLogo from "@/Components/ApplicationLogo.vue";
</script>

<template>
    <Link href="/" class="inline-flex rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <ApplicationLogo />
    </Link>
</template>
