<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PageHeader from "@/Components/UI/PageHeader.vue";

defineProps({
    title: {
        type: String,
        required: true,
    },

    subtitle: {
        type: String,
        default: "",
    },

    maxWidth: {
        type: String,
        default: "max-w-7xl",
    },

    padded: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <AppLayout :title="title">
        <template #header>
            <PageHeader :title="title" :subtitle="subtitle">
                <template v-if="$slots.actions" #actions>
                    <slot name="actions" />
                </template>
            </PageHeader>
        </template>

        <div :class="[
            'min-h-screen bg-slate-50 transition-colors duration-300 dark:bg-dark-bg',
            padded ? 'py-8 sm:py-10' : '',
        ]">
            <div :class="[
                maxWidth,
                'mx-auto w-full px-4 sm:px-6 lg:px-8',
            ]">
                <slot />
            </div>
        </div>
    </AppLayout>
</template>
