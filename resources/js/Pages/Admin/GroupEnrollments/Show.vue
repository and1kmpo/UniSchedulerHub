<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    group: Object,
    enrollments: Array
})
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <Head title="Group Details" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                {{ group.subject }} — {{ group.code }} ({{ group.name }})
            </h1>
            <p class="text-gray-700 dark:text-gray-300">
                <strong>Profesor:</strong> {{ group.professor ?? 'No asignado' }}
            </p>
            <p class="text-gray-700 dark:text-gray-300 mt-2">
                <strong>Horarios:</strong>
                <span v-if="group.schedules.length > 0">
                    <ul class="list-disc ml-5 mt-1">
                        <li v-for="(schedule, i) in group.schedules" :key="i">
                            {{ schedule.day }}: {{ schedule.start_time }} - {{ schedule.end_time }}
                        </li>
                    </ul>
                </span>
                <span v-else class="text-gray-500 dark:text-gray-400">No definidos</span>
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Código</th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Nombre</th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="enrollment in enrollments" :key="enrollment.id">
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ enrollment.code }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ enrollment.student_name }}</td>
                        <td class="px-4 py-2 text-sm">
                            <Badge :text="enrollment.status" :color="enrollment.statusColor" />
                        </td>
                    </tr>
                    <tr v-if="enrollments.length === 0">
                        <td colspan="3" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                            No hay estudiantes inscritos.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <Link :href="route('admin.group-enrollments.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">
            ← Volver a la lista de grupos
            </Link>
        </div>
    </div>
</template>
