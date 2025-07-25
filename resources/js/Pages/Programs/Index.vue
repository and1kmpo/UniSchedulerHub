<script>
export default {
    name: "ProgramsIndex",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import Swal from "sweetalert2";
import { Link } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";

defineProps({
    programs: {
        type: Object,
        required: true,
    },
});

const deleteProgram = (id, name) => {
    Swal.fire({
        title: '¿Are you sure to delete "' + name + '"?',
        text: "You won't be able to reverse this",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#4338CA",
        cancelButtonColor: "#d33",
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete',
        cancelButtonText: '<i class="fas fa-ban"></i> No, cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            // Utiliza el ID del programa en la URL de la solicitud DELETE
            axios.delete(`/programs/${id}`)
                .then(() => {
                    Swal.fire(
                        'Deleted"',
                        "Program deleted successfully!",
                        "success"
                    ).then(() => {
                        location.reload();
                    });
                })
                .catch((error) => {
                    console.error(error);
                    Swal.fire("Error", error.response.data.error, "error");
                });
        }
    });
};
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Programs
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between">
                        <Link :href="route('programs.create')"
                            class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white">
                        Create Program
                        </Link>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full bg-white shadow-md rounded-xl text-center">
                            <thead>
                                <tr class="bg-blue-gray-100 text-gray-700">
                                    <th class="py-3 px-4 text-center">#</th>
                                    <th class="py-3 px-4 text-center">
                                        Program
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        Description
                                    </th>
                                    <th class="py-3 px-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-blue-gray-900">
                                <tr class="border-b border-blue-gray-200" v-for="(program, i) in programs.data"
                                    :key="program.id">
                                    <td class="py-3 px-4">
                                        {{
                                            (programs.current_page - 1) *
                                            programs.per_page +
                                            i +
                                            1
                                        }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ program.name }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ program.description }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <Link :href="route('programs.edit', program.id)"
                                            class="inline-flex items-center justify-center p-2 text-indigo-800 dark:text-indigo-400 hover:scale-110 hover:-translate-y-1 transition-transform duration-300">
                                        <i class="fas fa-edit"></i>
                                        </Link>

                                        <Link href="#" @click="deleteProgram(program.id, program.name)"
                                            class="inline-flex items-center justify-center text-red-600 dark:text-red-400 hover:scale-110 hover:-translate-y-1 transition-transform duration-300">
                                        <i class="fas fa-trash"></i>
                                        </Link>
                                    </td>
                                    <td class="py-3 px-4">

                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Paginación -->
                        <div class="flex items-center justify-center m-4">
                            <Link v-if="programs.current_page > 1" :href="programs.prev_page_url" href="#"
                                class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white">
                            <i class="fa-solid fa-angles-left"></i>
                            </Link>

                            <div class="text-sm mx-4">
                                Page {{ programs.current_page }} of
                                {{ programs.last_page }}
                            </div>

                            <Link v-if="programs.current_page < programs.last_page
                            " :href="programs.next_page_url"
                                class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white">
                            <i class="fa-solid fa-angles-right"></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
