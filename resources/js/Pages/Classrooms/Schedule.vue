<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    classroom: Object
})


</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Schedule for {{ classroom.name }}
            </h1>
        </template>

        <div class="p-6 bg-white dark:bg-gray-900 rounded shadow">
            <table class="min-w-full text-sm text-center">
                <thead>
                    <tr
                        class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 uppercase text-xs tracking-wider">
                        <th class="px-4 py-3">Day</th>
                        <th class="px-4 py-3">Start</th>
                        <th class="px-4 py-3">End</th>
                        <th class="px-4 py-3">Class Group</th>
                        <th class="px-4 py-3">Subject</th>
                        <th class="px-4 py-3">Professor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="sched in classroom.schedules" :key="sched.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-4 py-3 capitalize">{{ sched.day }}</td>
                        <td class="px-4 py-3">{{ sched.start_time }}</td>
                        <td class="px-4 py-3">{{ sched.end_time }}</td>
                        <td class="px-4 py-3">{{ sched.class_group?.name || '—' }}</td>
                        <td class="px-4 py-3">{{ sched.class_group?.subject?.name || '—' }}</td>
                        <td class="px-4 py-3">{{ sched.class_group?.professor?.name || '—' }}</td>
                    </tr>
                </tbody>

            </table>
        </div>
    </AppLayout>
</template>
