<script setup>
import { Head, Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Welcome" />

    <main class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 text-ink dark:bg-dark-bg">
        <section
            class="w-full max-w-xl rounded-lg border border-border-light bg-surface p-8 shadow-sm dark:border-border-dark dark:bg-surface-dark"
        >
            <ApplicationLogo />

            <div class="mt-8 border-l-2 border-brand-600 pl-5">
                <h1 class="text-2xl font-semibold tracking-tight text-ink dark:text-white">
                    The operational network for the modern university.
                </h1>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-zinc-400">
                    TARRAYA connects students, professors, schedules, rooms, programs, grades and institutional data in one synchronized academic operating system.
                </p>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <Link
                    v-if="!$page.props.auth.user"
                    :href="route('login')"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 dark:focus:ring-offset-dark-bg"
                >
                    Log in
                </Link>

                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="inline-flex items-center justify-center rounded-lg border border-border-light bg-surface px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-offset-dark-bg"
                >
                    Register
                </Link>

                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 dark:focus:ring-offset-dark-bg"
                >
                    Open dashboard
                </Link>
            </div>
        </section>
    </main>
</template>
