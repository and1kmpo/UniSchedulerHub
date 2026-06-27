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
        bg-success/10
        border-success/30
        text-success
        dark:bg-success/10
        dark:border-success/30
        dark:text-success
    `,

    warning: `
        bg-warning/10
        border-warning/30
        text-amber-700
        dark:bg-warning/10
        dark:border-warning/30
        dark:text-warning
    `,

    danger: `
        bg-danger/10
        border-danger/20
        text-danger
        dark:bg-danger/10
        dark:border-danger/20
        dark:text-danger
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
        'rounded-lg border p-4',
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

