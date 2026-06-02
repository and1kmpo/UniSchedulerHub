<script setup>
import { computed } from "vue";

const props = defineProps({
    result: {
        type: Object,
        default: () => ({
            allowed: true,
            errors: [],
            warnings: [],
        }),
    },

    loading: {
        type: Boolean,
        default: false,
    },
});

const variants = {
    ready: `
        bg-emerald-50
        border-emerald-200
        text-emerald-700
        dark:bg-emerald-500/10
        dark:border-emerald-500/20
        dark:text-emerald-300
    `,

    warning: `
        bg-amber-50
        border-amber-200
        text-amber-700
        dark:bg-amber-500/10
        dark:border-amber-500/20
        dark:text-amber-300
    `,

    danger: `
        bg-red-50
        border-red-200
        text-red-700
        dark:bg-red-500/10
        dark:border-red-500/20
        dark:text-red-300
    `,
}

const status = computed(() => {
    if (props.loading) {
        return "warning";
    }

    if (!props.result.allowed) {
        return "danger";
    }

    if (props.result.warnings?.length) {
        return "warning";
    }

    return "ready";
});

const message = computed(() => {
    if (props.loading) {
        return "Validating enrollment...";
    }

    if (!props.result.allowed) {
        return props.result.errors?.[0] || "Enrollment cannot be confirmed.";
    }

    if (props.result.warnings?.length) {
        return props.result.warnings[0];
    }

    return "Enrollment is ready to be confirmed.";
});
</script>

<template>
    <div :class="[
        'rounded-2xl border p-4',
        variants[status],
    ]">
        <div class="flex items-center gap-3">

            <i class="fa-solid fa-circle-info text-lg" />

            <p class="font-medium">
                {{ message }}
            </p>

        </div>
    </div>
</template>
