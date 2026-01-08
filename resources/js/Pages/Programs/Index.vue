<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';
import { useAlert } from '@/Components/Composables/useAlert';
import EditButton from '@/Components/EditButton.vue';
import DeleteButton from '@/Components/DeleteButton.vue';

const { confirm, success, error } = useAlert();

const props = defineProps({
    programs: Array
});

const programList = ref([...props.programs]);

// Watch for changes in props
watch(() => props.programs, (newVal) => {
    programList.value = [...newVal];
});

const confirmDelete = async (program) => {
    const confirmed = await confirm(`Delete "${program.name}"?`, "This action cannot be undone.");
    if (!confirmed) return;

    axios.post(`/programs/${program.id}`, {
        _method: 'DELETE'
    })
        .then(() => {
            success("The program was successfully deleted.", "Deleted");
            router.reload({ only: ['programs'] });
        })
        .catch((err) => {
            console.error("Error deleting:", err);
            error(err.response?.data?.message || "An error occurred while deleting", "Error");
        });
};
</script>

<template>
    <AppLayout title="Programs">
        <template #header>
            <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                Program Management
            </h2>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">

                <Link :href="route('programs.create')"
                    class="inline-flex items-center bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-2 rounded-md transition duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 dark:focus:ring-offset-gray-900">
                <i class="fa-solid fa-plus">&nbsp;</i> New Program
                </Link>
            </div>

            <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow-md">
                <table
                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm text-left text-gray-700 dark:text-gray-100">
                    <thead
                        class="bg-gray-50 dark:bg-gray-800 uppercase text-xs text-gray-600 dark:text-gray-300 tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">#</th>
                            <th scope="col" class="px-6 py-4">Name</th>
                            <th scope="col" class="px-6 py-4">ID</th>
                            <th scope="col" class="px-6 py-4">Description</th>
                            <th scope="col" class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(program, i) in programList" :key="program.id"
                            class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">
                                {{ i + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ program.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ program.id }}
                            </td>
                            <td class="px-6 py-4 break-words max-w-sm">
                                {{ program.description }}
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <EditButton :href="route('programs.edit', program.id)" />
                                <DeleteButton :onClick="() => confirmDelete(program)" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
