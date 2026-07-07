<script setup>
defineProps({
    submitting: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="
            sticky bottom-0 inset-x-0 z-10
            flex flex-col-reverse gap-3
            border-t border-border-light
            bg-surface/95 px-4 py-4
            backdrop-blur supports-[backdrop-filter]:bg-white/80
            dark:border-border-dark
            dark:bg-surface-dark/95
            dark:supports-[backdrop-filter]:bg-surface-dark/80
            sm:flex-row sm:items-center sm:justify-end
            sm:px-6
        ">
        <slot />
    </div>
</template>
