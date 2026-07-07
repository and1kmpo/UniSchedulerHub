<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },

    variant: {
        type: String,
        default: "gray",
    },

    type: {
        type: String,
        default: "",
    },
});

const variants = {
    gray: `
        bg-slate-100 text-slate-600
        dark:bg-slate-400/10 dark:text-slate-400
    `,

    brand: `
        bg-brand-500/10 text-brand-700
        dark:bg-brand-500/15 dark:text-brand-300
    `,

    info: `
        bg-accent/10 text-cyan-700
        dark:bg-accent/15 dark:text-accent
    `,

    success: `
        bg-success/10 text-success
        dark:bg-success/15 dark:text-success
    `,

    danger: `
        bg-danger/10 text-danger
        dark:bg-danger/15 dark:text-danger
    `,

    warning: `
        bg-warning/10 text-amber-700
        dark:bg-warning/15 dark:text-warning
    `,
};

const typeVariantMap = {
    active: "success",
    "in-progress": "brand",
    in_progress: "brand",
    progress: "brand",
    completed: "gray",
    cancelled: "danger",
    canceled: "danger",
};

const dotVariants = {
    gray: "bg-slate-400",
    brand: "bg-brand-600",
    info: "bg-accent",
    success: "bg-success",
    danger: "bg-danger",
    warning: "bg-warning",
};
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider',
            variants[typeVariantMap[type] || variant] || variants.gray,
        ]"
    >
        <span :class="['h-1.5 w-1.5 rounded-full', dotVariants[typeVariantMap[type] || variant] || dotVariants.gray]" />
        {{ label }}
    </span>
</template>
