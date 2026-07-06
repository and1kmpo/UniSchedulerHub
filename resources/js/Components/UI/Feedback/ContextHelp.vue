<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
    icon: {
        type: String,
        default: "fa-solid fa-circle-info",
    },
    tone: {
        type: String,
        default: "info",
    },
});

const toneClasses = {
    info: "border-brand-600/20 bg-brand-600/10 text-brand-700 dark:text-brand-200",
    neutral: "border-border-light bg-slate-50 text-slate-700 dark:border-border-dark dark:bg-zinc-900 dark:text-zinc-300",
    warning: "border-warning/30 bg-warning/10 text-warning",
};
</script>

<template>
    <aside
        :class="[
            'rounded-xl border p-4',
            toneClasses[tone] || toneClasses.info,
        ]"
    >
        <div class="flex gap-3">
            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface/80 text-brand-600 dark:bg-dark-bg/60 dark:text-brand-300">
                <i :class="icon" />
            </span>

            <div>
                <h3 class="text-sm font-semibold text-ink dark:text-white">
                    {{ title }}
                </h3>

                <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-zinc-400">
                    {{ description }}
                </p>
            </div>
        </div>
    </aside>
</template>
