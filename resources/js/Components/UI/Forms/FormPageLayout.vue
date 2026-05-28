<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    title: {
        type: String,
        required: true,
    },

    subtitle: {
        type: String,
        default: "",
    },

    backHref: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <AppLayout :title="title">

        <template #header>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ title }}
                    </h1>

                    <p v-if="subtitle" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ subtitle }}
                    </p>

                </div>

                <div v-if="$slots.actions">
                    <slot name="actions" />
                </div>

            </div>

        </template>

        <div class="px-4 py-6 sm:px-6 lg:px-8">

            <div class="mx-auto max-w-5xl">

                <div class="overflow-hidden rounded-2xl bg-white shadow dark:bg-gray-900">

                    <slot />

                </div>

            </div>

        </div>

    </AppLayout>
</template>