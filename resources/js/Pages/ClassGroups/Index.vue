<script setup>
import { Link, router } from "@inertiajs/vue3";
import Layout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue";

defineProps({
    classGroups: Object,
});
</script>

<template>
    <Layout title="Class Groups">
        <div class="flex justify-between mb-6">
            <h1 class="text-xl font-bold">Class Groups</h1>
            <Link :href="route('class-groups.create')" class="btn-indigo">Create</Link>
        </div>

        <table class="table-auto w-full bg-white dark:bg-gray-800 rounded shadow">
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-700 text-left">
                    <th class="p-2">Code</th>
                    <th class="p-2">Name</th>
                    <th class="p-2">Subject</th>
                    <th class="p-2">Professor</th>
                    <th class="p-2">Capacity</th>
                    <th class="p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="group in classGroups.data" :key="group.id"
                    class="border-t border-gray-300 dark:border-gray-600">
                    <td class="p-2">{{ group.code }}</td>
                    <td class="p-2">{{ group.name }}</td>
                    <td class="p-2">{{ group.subject.name }}</td>
                    <td class="p-2">{{ group.professor.name }}</td>
                    <td class="p-2">{{ group.capacity }}</td>
                    <td class="p-2">
                        <Link :href="route('class-groups.edit', group.id)" class="text-blue-500">Edit</Link>
                        <button @click="destroy(group.id)" class="ml-2 text-red-500">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <Pagination :links="classGroups.links" />

    </Layout>
</template>

<script>
function destroy(id) {
    if (confirm('Are you sure?')) {
        router.delete(route('class-groups.destroy', id));
    }
}
</script>
