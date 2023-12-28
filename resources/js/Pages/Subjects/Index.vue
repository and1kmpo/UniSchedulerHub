<script>
export default {
    name: "SubjectsIndex",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import Swal from "sweetalert2";
import { Link } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";

defineProps({
    subjects: {
        type: Object,
        required: true,
    },
});

const deleteSubject = (id, name) => {
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
            Inertia.delete(route("subjects.destroy", { subject: id }))
                .then(() => {
                    Swal.fire(
                        'Deleted"',
                        "Subject deleted successfully!",
                        "success"
                    );
                })
                .catch((error) => {
                    console.error(error);
                    Swal.fire("Error", "Error deleting program", "error");
                });
        }
    });
};
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Subjects
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between">
                        <Link
                            :href="route('subjects.create')"
                            class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white"
                            v-if="
                                $page.props.user.permissions.includes(
                                    'create subjects'
                                )
                            "
                        >
                            Create Subject
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
                                        Subject
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        Description
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        Credits
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        Knowledge area
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        Elective
                                    </th>
                                    <th class="py-3 px-4 text-center">Edit</th>
                                    <th class="py-3 px-4 text-center">
                                        Delete
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-blue-gray-900">
                                <tr
                                    class="border-b border-blue-gray-200"
                                    v-for="(subject, i) in subjects.data"
                                    :key="subject.id"
                                >
                                    <td class="py-3 px-4">
                                        {{
                                            (subjects.current_page - 1) *
                                                subjects.per_page +
                                            i +
                                            1
                                        }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ subject.name }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ subject.description }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ subject.credits }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ subject.knowledge_area }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ subject.elective == 1 ? "S" : "N" }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <Link
                                            :href="
                                                route(
                                                    'subjects.edit',
                                                    subject.id
                                                )
                                            "
                                            class="text-xs bg-blue-700 hover:bg-blue-400 hover:text-black rounded p-2 px-4 text-white"
                                            v-if="
                                                $page.props.user.permissions.includes(
                                                    'update subjects'
                                                )
                                            "
                                        >
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Link
                                            @click="
                                                deleteSubject(
                                                    subject.id,
                                                    subject.name
                                                )
                                            "
                                            class="text-xs bg-red-700 hover:bg-red-400 hover:text-black rounded p-2 px-4 text-white"
                                            v-if="
                                                $page.props.user.permissions.includes(
                                                    'delete subjects'
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
                                v-if="subjects.current_page > 1"
                                :href="subjects.prev_page_url"
                                class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white"
                            >
                                <i class="fa-solid fa-angles-left"></i>
                            </Link>

                            <div class="text-sm mx-4">
                                Page {{ subjects.current_page }} of
                                {{ subjects.last_page }}
                            </div>

                            <Link
                                v-if="
                                    subjects.current_page < subjects.last_page
                                "
                                :href="subjects.next_page_url"
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
