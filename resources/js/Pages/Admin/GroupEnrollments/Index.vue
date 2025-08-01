<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    classGroups: Object,
})

const groups = computed(() => props.classGroups?.data || [])
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4">
            Class Groups & Enrollments
        </h1>

        <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow-sm rounded">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Code</th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Name</th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Subject</th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Professor</th>
                        <th
                            class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Enrolled</th>
                        <th
                            class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="group in groups" :key="group.id">
                        <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100">{{ group.code }}</td>
                        <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100">{{ group.name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100">{{ group.subject?.name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100">{{ group.professor?.name ?? '—'
                            }}</td>
                        <td class="px-4 py-2 text-sm text-center">
                            <Badge :text="group.subject_enrollments_count + ' enrolled'" color="blue" />
                        </td>
                        <td class="px-4 py-2 text-sm text-center">
                            <Link :href="route('admin.class-groups.enrollments', group.id)"
                                class="inline-flex items-center px-3 py-1 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700 transition">
                            Ver detalles
                            </Link>
                        </td>
                    </tr>

                    <tr v-if="groups.length === 0">
                        <td colspan="6" class="p-4 text-center text-gray-500 dark:text-gray-400">
                            No class groups found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
