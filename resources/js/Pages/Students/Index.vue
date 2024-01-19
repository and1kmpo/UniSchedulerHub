<script>
export default {
    name: "StudentsIndex",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";
import Swal from "sweetalert2";
import { Link } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";

defineProps({
    students: {
        type: Object,
        required: true,
    },
});

const deleteStudent = (id, name) => {
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
            Inertia.delete(route("students.destroy", { student: id }))
                .then(() => {
                    Swal.fire(
                        'Deleted"',
                        "Student deleted successfully!",
                        "success"
                    );
                })
                .catch((error) => {
                    console.error(error);
                    Swal.fire("Error", "Error deleting student", "error");
                });
        }
    });
};
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Students
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between">
                        <Link :href="route('students.create')"
                            class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white" v-if="$page.props.user.permissions.includes(
                                'create students'
                            )
                                ">
                        Create Student
                        </Link>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full bg-white shadow-md rounded-xl text-center">
                            <thead>
                                <tr class="bg-blue-gray-100 text-gray-700">
                                    <th class="py-3 px-4 text-center">#</th>
                                    <th class="py-3 px-4 text-center">
                                        Document
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        Student
                                    </th>
                                    <th class="py-3 px-4 text-center">Phone</th>
                                    <th class="py-3 px-4 text-center">Email</th>
                                    <th class="py-3 px-4 text-center">
                                        Address
                                    </th>
                                    <th class="py-3 px-4 text-center">City</th>
                                    <th class="py-3 px-4 text-center">
                                        Semester
                                    </th>
                                    <th class="py-3 px-4 text-center">
                                        Program
                                    </th>
                                    <th class="py-3 px-4 text-center">Edit</th>
                                    <th class="py-3 px-4 text-center">
                                        Delete
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-blue-gray-900">
                                <tr class="border-b border-blue-gray-200" v-for="(student, i) in students.data"
                                    :key="student.id">
                                    <td class="py-3 px-4">
                                        {{
                                            (students.current_page - 1) *
                                            students.per_page +
                                            i +
                                            1
                                        }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ student.document }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{
                                            student.first_name +
                                            " " +
                                            student.last_name
                                        }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ student.phone }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ student.email }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ student.address }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ student.city }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ student.semester }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ student.program.name }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <Link :href="route(
                                            'students.edit',
                                            student.id
                                        )
                                            "
                                            class="text-xs bg-blue-700 hover:bg-blue-400 hover:text-black rounded p-2 px-4 text-white"
                                            v-if="$page.props.user.permissions.includes(
                                                'update students'
                                            )
                                                ">
                                        <i class="fas fa-edit"></i>
                                        </Link>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Link href="#" @click="
                                            deleteStudent(
                                                student.id,
                                                student.first_name
                                            )
                                            "
                                            class="text-xs bg-red-700 hover:bg-red-400 hover:text-black rounded p-2 px-4 text-white"
                                            v-if="$page.props.user.permissions.includes(
                                                'delete students'
                                            )
                                                ">
                                        <i class="fas fa-trash"></i>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="flex items-center justify-center m-4">
                            <Link v-if="students.current_page > 1" :href="students.prev_page_url"
                                class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white">
                            <i class="fa-solid fa-angles-left"></i>
                            </Link>

                            <div class="text-sm mx-4">
                                Page {{ students.current_page }} of
                                {{ students.last_page }}
                            </div>

                            <Link v-if="students.current_page < students.last_page
                                " :href="students.next_page_url"
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
