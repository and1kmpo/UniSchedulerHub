<script>
export default {
    name: "ProfessorsIndex",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import { Link } from "@inertiajs/vue3";


defineProps({
    professors: {
        type: Object,
        required: true,
    },
});

</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Professors
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mt-4 overflow-x-auto">
                        <div class="sm:overflow-x-auto">
                            <table class="min-w-full bg-white shadow-md rounded-xl text-center">
                                <thead>
                                    <tr class="bg-blue-gray-100 text-gray-700">
                                        <th class="py-3 px-4 hidden sm:table-cell">#</th>
                                        <th class="py-3 px-4">Document</th>
                                        <th class="py-3 px-4">Professor</th>
                                        <th class="py-3 px-4 hidden sm:table-cell">Phone</th>
                                        <th class="py-3 px-4 hidden sm:table-cell">Email</th>
                                        <th class="py-3 px-4 hidden sm:table-cell">Address</th>
                                        <th class="py-3 px-4 hidden sm:table-cell">City</th>
                                    </tr>
                                </thead>
                                <tbody class="text-blue-gray-900 divide-y divide-blue-gray-200">
                                    <tr v-for="(professor, i) in professors.data" :key="professor.id">
                                        <td class="py-3 px-4 hidden sm:table-cell">{{ (professors.current_page - 1) *
                                            professors.per_page + i + 1
                                            }}</td>
                                        <td class="py-3 px-4">{{ professor.professor.document }}</td>
                                        <td class="py-3 px-4">{{ professor.name }} </td>
                                        <td class="py-3 px-4 hidden sm:table-cell">{{ professor.professor.phone }}</td>
                                        <td class="py-3 px-4 hidden sm:table-cell">{{ professor.email }}</td>
                                        <td class="py-3 px-4 hidden sm:table-cell">{{ professor.professor.address }}
                                        </td>
                                        <td class="py-3 px-4 hidden sm:table-cell">{{ professor.professor.city }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="flex justify-center m-4">
                            <div class="flex items-center">
                                <Link v-if="professors.current_page > 1" :href="professors.prev_page_url"
                                    class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white mr-2">
                                <i class="fa-solid fa-angles-left"></i>
                                </Link>
                                <div class="text-sm mx-2">
                                    Page {{ professors.current_page }} of {{ professors.last_page }}
                                </div>
                                <Link v-if="professors.current_page < professors.last_page"
                                    :href="professors.next_page_url"
                                    class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white ml-2">
                                <i class="fa-solid fa-angles-right"></i>
                                </Link>
                            </div>
                        </div>


                    </div>


                </div>
            </div>
        </div>
    </AppLayout>
</template>
