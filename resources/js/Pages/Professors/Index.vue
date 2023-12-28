<script>
export default {
    name: "ProfessorsIndex",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import Swal from "sweetalert2";
import { Link } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";

defineProps({
    professors: {
        type: Object,
        required: true,
    },
});

const deleteProfessor = (id, name) => {
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
            Inertia.delete(route("professors.destroy", { professor: id }))
                .then(() => {
                    Swal.fire(
                        'Deleted"',
                        "Professor deleted successfully!",
                        "success"
                    );
                })
                .catch((error) => {
                    console.error(error);
                    Swal.fire("Error", "Error deleting professor", "error");
                });
        }
    });
};
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
                    <div class="flex justify-between">
                        <Link
                            :href="route('professors.create')"
                            class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white"
                            v-if="
                                $page.props.user.permissions.includes(
                                    'create professors'
                                )
                            "
                        >
                            Create Professor
                        </Link>
                    </div>

                    <div class="mt-4">
                        <table
                            class="min-w-full bg-white shadow-md rounded-xl text-center"
                        >
                            <thead>
                                <tr class="bg-blue-gray-100 text-gray-700">
                                    <th class="py-3 px-4 text-center">#</th>
                                    <th class="py-3 px-4 text-center">
                                        Document
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        Professor
                                    </th>
                                    <th class="py-3 px-4 text-center">Phone</th>
                                    <th class="py-3 px-4 text-center">Email</th>
                                    <th class="py-3 px-4 text-center">
                                        Address
                                    </th>
                                    <th class="py-3 px-4 text-center">City</th>
                                    <th class="py-3 px-4 text-center">Edit</th>
                                    <th class="py-3 px-4 text-center">
                                        Delete
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-blue-gray-900">
                                <tr
                                    class="border-b border-blue-gray-200"
                                    v-for="(professor, i) in professors.data"
                                    :key="professor.id"
                                >
                                    <td class="py-3 px-4">
                                        {{
                                            (professors.current_page - 1) *
                                                professors.per_page +
                                            i +
                                            1
                                        }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ professor.document }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{
                                            professor.first_name +
                                            " " +
                                            professor.last_name
                                        }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ professor.phone }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ professor.email }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ professor.address }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ professor.city }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <Link
                                            :href="
                                                route(
                                                    'professors.edit',
                                                    professor.id
                                                )
                                            "
                                            class="text-xs bg-blue-700 hover:bg-blue-400 hover:text-black rounded p-2 px-4 text-white"
                                            v-if="
                                                $page.props.user.permissions.includes(
                                                    'update professors'
                                                )
                                            "
                                        >
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Link
                                            @click="
                                                deleteProfessor(
                                                    professor.id,
                                                    professor.first_name
                                                )
                                            "
                                            class="text-xs bg-red-700 hover:bg-red-400 hover:text-black rounded p-2 px-4 text-white"
                                            v-if="
                                                $page.props.user.permissions.includes(
                                                    'delete professors'
                                                )
                                            "
                                        >
                                            <i class="fas fa-trash"></i>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="flex items-center justify-center m-4">
                            <Link
                                v-if="professors.current_page > 1"
                                :href="professors.prev_page_url"
                                class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white"
                            >
                                <i class="fa-solid fa-angles-left"></i>
                            </Link>

                            <div class="text-sm mx-4">
                                Page {{ professors.current_page }} of
                                {{ professors.last_page }}
                            </div>

                            <Link
                                v-if="
                                    professors.current_page <
                                    professors.last_page
                                "
                                :href="professors.next_page_url"
                                class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white"
                            >
                                <i class="fa-solid fa-angles-right"></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
