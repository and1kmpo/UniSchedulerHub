<template>
    <AppLayout title="Class Groups">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Class Groups</h1>
                <Link :href="route('class-groups.create')"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Group
                </Link>
            </div>
        </template>


        <!-- Table -->
        <div class="overflow-x-auto">
            <!-- Table -->
            <div class="mt-6 px-4 sm:px-6 lg:px-8">
                <div
                    class="rounded-lg shadow ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900 overflow-x-auto mb-8">
                    <table class="min-w-full text-sm text-left">
                        <thead>
                            <tr
                                class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 uppercase text-xs tracking-wider">
                                <th class="px-4 py-3">Code</th>
                                <th class="px-4 py-3">Subject</th>
                                <th class="px-4 py-3">Professor</th>
                                <th class="px-4 py-3 hidden md:table-cell">Shift</th>
                                <th class="px-4 py-3 hidden md:table-cell">Modality</th>
                                <th class="px-4 py-3">Capacity</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="group in classGroups.data" :key="group.id"
                                class="even:bg-gray-50 dark:even:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <td class="px-4 py-3 font-mono font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ group.code }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ group.subject.name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ group.name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                    {{ group.professor.name }}
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <span class="badge" :class="shiftClass(group.shift)">
                                        {{ group.shift }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <span
                                        class="badge bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-white">
                                        {{ group.modality }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ group.capacity }} students
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-3">
                                        <Link :href="route('class-groups.edit', group.id)"
                                            class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-200"
                                            title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        </Link>
                                        <button @click="destroy(group.id)"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200"
                                            title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="m-4">
                    <Pagination :links="classGroups.links" />
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue";

defineProps({
    classGroups: Object,
});

function destroy(id) {
    if (confirm("Are you sure you want to delete this group?")) {
        router.delete(route("class-groups.destroy", id));
    }
}

function shiftClass(shift) {
    const map = {
        Day: "bg-green-100 text-green-800 dark:bg-green-800 dark:text-white",
        Night: "bg-purple-100 text-purple-800 dark:bg-purple-700 dark:text-white",
        Intensive: "bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-white",
    };
    return `badge ${map[shift] || "bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-white"}`;
}
</script>

<style scoped>
.badge {
    @apply inline-block px-2 py-1 rounded-full text-xs font-medium;
}
</style>
