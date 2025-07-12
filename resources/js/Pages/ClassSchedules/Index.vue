<script setup>
import Layout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";

defineProps({
    classGroup: Object,
    schedules: Array,
});

function destroySchedule(id) {
    if (confirm("Are you sure?")) {
        router.delete(route('class-schedules.destroy', [classGroup.id, id]));
    }
}
</script>

<template>
    <Layout :title="`Schedules for ${classGroup.name}`">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-bold">Schedules for {{ classGroup.name }}</h1>
            <Link :href="route('class-schedules.create', classGroup.id)" class="btn-indigo">Add Schedule</Link>
        </div>

        <table class="table-auto w-full bg-white dark:bg-gray-800 rounded shadow">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="p-2">Day</th>
                    <th class="p-2">Start</th>
                    <th class="p-2">End</th>
                    <th class="p-2">Classroom</th>
                    <th class="p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="s in schedules" :key="s.id" class="border-t dark:border-gray-600">
                    <td class="p-2 capitalize">{{ s.day }}</td>
                    <td class="p-2">{{ s.start_time }}</td>
                    <td class="p-2">{{ s.end_time }}</td>
                    <td class="p-2">{{ s.classroom }}</td>
                    <td class="p-2">
                        <Link :href="route('class-schedules.edit', [classGroup.id, s.id])" class="text-blue-500">Edit
                        </Link>
                        <button @click="destroySchedule(s.id)" class="ml-2 text-red-500">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </Layout>
</template>
