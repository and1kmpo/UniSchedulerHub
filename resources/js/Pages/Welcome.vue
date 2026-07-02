<script setup>
import { Head } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const modules = [
    { name: "Core", description: "Students, professors, programs, subjects and class groups." },
    { name: "Sync", description: "Schedules, academic periods, conflicts and calendar operations." },
    { name: "Rooms", description: "Buildings, classrooms, capacity and occupancy intelligence." },
    { name: "Grades", description: "Grading workflows, locks, status and academic history." },
    { name: "Insights", description: "Dashboards, reports, audit events and operational metrics." },
    { name: "Flow", description: "Enrollment, withdrawals, group changes and academic validations." },
];

const metrics = [
    { label: "Academic modules", value: "6" },
    { label: "Role workspaces", value: "4" },
    { label: "Operational reports", value: "6" },
];
</script>

<template>
    <Head title="TARRAYA" />

    <main class="min-h-screen bg-slate-50 text-ink dark:bg-dark-bg">
        <section class="mx-auto grid min-h-screen w-full max-w-7xl gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_0.9fr] lg:items-center lg:px-8">
            <div>
                <ApplicationLogo />

                <div class="mt-10 max-w-3xl border-l-2 border-brand-600 pl-5">
                    <p class="font-mono text-xs font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-300">
                        Academic Operating System
                    </p>

                    <h1 class="mt-4 text-4xl font-semibold tracking-tight text-ink dark:text-white sm:text-5xl">
                        The operational network for the modern university.
                    </h1>

                    <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 dark:text-zinc-400">
                        TARRAYA connects students, professors, schedules, classrooms, programs, grades and institutional data in one modular academic infrastructure.
                    </p>
                </div>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <BaseButton
                        v-if="!$page.props.auth.user && canLogin"
                        as="a"
                        :href="route('login')"
                        size="lg"
                    >
                        <i class="fa-solid fa-arrow-right-to-bracket mr-2" />
                        Explore platform
                    </BaseButton>

                    <BaseButton
                        v-if="$page.props.auth.user"
                        as="a"
                        :href="route('dashboard')"
                        size="lg"
                    >
                        <i class="fa-solid fa-grid-2 mr-2" />
                        Open dashboard
                    </BaseButton>

                    <BaseButton
                        v-if="canRegister"
                        as="a"
                        variant="secondary"
                        :href="route('register')"
                        size="lg"
                    >
                        Register
                    </BaseButton>
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    <div
                        v-for="metric in metrics"
                        :key="metric.label"
                        class="rounded-lg border border-border-light bg-surface p-4 dark:border-border-dark dark:bg-surface-dark"
                    >
                        <p class="font-mono text-2xl font-semibold text-ink dark:text-white">
                            {{ metric.value }}
                        </p>
                        <p class="mt-1 text-xs font-medium uppercase text-slate-500 dark:text-zinc-400">
                            {{ metric.label }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-border-light bg-surface p-5 dark:border-border-dark dark:bg-surface-dark">
                <div class="flex items-center justify-between border-b border-border-light pb-4 dark:border-border-dark">
                    <div>
                        <p class="text-sm font-semibold text-ink dark:text-white">
                            TARRAYA Network Map
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                            Modular academic operations
                        </p>
                    </div>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                        <i class="fa-solid fa-diagram-project" />
                    </span>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <article
                        v-for="module in modules"
                        :key="module.name"
                        class="rounded-lg border border-border-light bg-slate-50 p-4 dark:border-border-dark dark:bg-dark-bg"
                    >
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-accent" />
                            <h2 class="font-mono text-sm font-semibold text-brand-600 dark:text-brand-300">
                                TARRAYA {{ module.name }}
                            </h2>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-zinc-400">
                            {{ module.description }}
                        </p>
                    </article>
                </div>
            </div>
        </section>
    </main>
</template>
