<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';

defineProps({
    terms: String,
});
</script>

<template>
    <Head title="Terms of Service" />

    <div class="font-sans text-ink antialiased dark:text-white">
        <div class="bg-slate-50 pt-4 dark:bg-dark-bg">
            <div class="flex min-h-screen flex-col items-center pt-6 sm:pt-0">
                <div>
                    <AuthenticationCardLogo />
                </div>

                <div class="prose mt-6 w-full overflow-hidden border border-border-light bg-surface p-6 dark:border-border-dark dark:bg-surface-dark dark:prose-invert sm:max-w-2xl sm:rounded-lg" v-html="terms" />
            </div>
        </div>
    </div>
</template>
