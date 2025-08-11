<template>
    <AppLayout>
        <template #header>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">New Classroom</h1>
        </template>
        <div class="mt-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg shadow ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900 p-6">
                <Form :buildings="buildings" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Form from './Form.vue'

defineProps({ buildings: Array })
</script>
