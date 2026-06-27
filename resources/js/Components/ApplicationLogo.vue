<script setup>
import ApplicationMark from "@/Components/ApplicationMark.vue";
</script>

<template>
    <div class="inline-flex max-w-full items-center gap-3.5 select-none">
        <!-- Isotipo Core Computado -->
        <ApplicationMark class="h-12 w-12 shrink-0 transition-transform duration-300 hover:scale-105" />

        <!-- Bloque Editorial de Naming -->
        <div class="min-w-0 leading-tight">
            <!-- Token Slate-950 mapea directamente al #0F172A de tu manual -->
            <h1 class="text-xl font-bold tracking-tight text-ink dark:text-white font-sans">
                TARRAYA
            </h1>
            <p class="max-w-[12rem] truncate text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400 font-mono">
                Academic Operating System
            </p>
        </div>
    </div>
</template>
