    <script>
    export default {
        name: "StudentsIndex",
    };
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, onMounted } from "vue";
import Swal from "sweetalert2";
import { Link } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    students: {
        type: Object,
        required: true,
    },
});

onMounted(() => {
    console.log("Student data:", props.students);
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

            <div class="py-12 ">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between">
                            <Link :href="route('students.create')"
                                class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white"
                                v-if="$page.props.user.permissions.includes(
                                    'create students'
                                )
                                ">
                            Create Student
                            </Link>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <div class="sm:overflow-x-auto">
                                <table class="min-w-full bg-white shadow-md rounded-xl text-center">
                                    <!-- Encabezado de la tabla -->
                                    <thead>
                                        <tr class="bg-blue-gray-100 text-gray-700">
                                            <th class="py-3 px-4 text-center hidden sm:table-cell">#</th>
                                            <th class="py-3 px-4 text-center">
                                                Document
                                            </th>
                                            <th class="py-3 px-4 text-center">
                                                Student
                                            </th>
                                            <th class="py-3 px-4 text-center hidden sm:table-cell">Phone</th>
                                            <th class="py-3 px-4 text-center hidden sm:table-cell">Email</th>
                                            <th class="py-3 px-4 text-center hidden md:table-cell">
                                                Address
                                            </th>
                                            <th class="py-3 px-4 text-center hidden md:table-cell">City</th>
                                            <th class="py-3 px-4 text-center hidden md:table-cell">
                                                Semester
                                            </th>
                                            <th class="py-3 px-4 text-center hidden lg:table-cell">
                                                Program
                                            </th>
                                            <th class="py-3 px-4 text-center">Edit</th>
                                            <th class="py-3 px-4 text-center">Delete</th>
                                        </tr>
                                    </thead>
                                    <!-- Cuerpo de la tabla -->
                                    <tbody class="text-blue-gray-900">
                                        <tr class="border-b border-blue-gray-200" v-for="(user, i) in students.data"
                                            :key="user.id">
                                            <!-- Columna # -->
                                            <td class="py-3 px-4 hidden sm:table-cell">
                                                {{
                                                    (students.current_page - 1) *
                                                    students.per_page +
                                                    i +
                                                    1
                                                }}
                                            </td>
                                            <!-- Documento -->
                                            <td class="py-3 px-4">
                                                {{ user.student.document }}
                                            </td>
                                            <!-- Nombre del estudiante -->
                                            <td class="py-3 px-4">
                                                {{ user.name }}

                                            </td>
                                            <!-- Teléfono -->
                                            <td class="py-3 px-4 hidden sm:table-cell">
                                                {{ user.student.phone }}
                                            </td>
                                            <!-- Correo electrónico -->
                                            <td class="py-3 px-4 hidden sm:table-cell">
                                                {{ user.email }}
                                            </td>
                                            <!-- Dirección (visible en tablets y pantallas más grandes) -->
                                            <td class="py-3 px-4 hidden md:table-cell">
                                                {{ user.student.address }}
                                            </td>
                                            <!-- Ciudad (visible en tablets y pantallas más grandes) -->
                                            <td class="py-3 px-4 hidden md:table-cell">
                                                {{ user.student.city }}
                                            </td>
                                            <!-- Semestre (visible en tablets y pantallas más grandes) -->
                                            <td class="py-3 px-4 hidden md:table-cell">
                                                {{ user.student.semester }}
                                            </td>
                                            <!-- Programa (visible en pantallas más grandes) -->
                                            <td class="py-3 px-4 hidden lg:table-cell">
                                                {{ user.student.program.name }}
                                            </td>

                                            <!-- Editar -->
                                            <td class="py-3 px-4">
                                                <Link :href="route('students.edit', user.student.user_id)"
                                                    class="text-xs bg-blue-700 hover:bg-blue-400 hover:text-black rounded p-2 px-4 text-white"
                                                    v-if="$page.props.user.permissions.includes('update students')">
                                                <i class="fas fa-edit"></i>
                                                </Link>
                                            </td>
                                            <!-- Eliminar -->
                                            <td class="py-3 px-4">
                                                <Link href="#"
                                                    @click="deleteStudent(user.student.user_id, user.student.name)"
                                                    class="text-xs bg-red-700 hover:bg-red-400 hover:text-black rounded p-2 px-4 text-white"
                                                    v-if="$page.props.user.permissions.includes('delete students')">
                                                <i class="fas fa-trash"></i>
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <div class="flex justify-center m-4">
                                <div class="flex items-center">
                                    <Link v-if="students.current_page > 1" :href="students.prev_page_url"
                                        class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white">
                                    <i class="fa-solid fa-angles-left"></i>
                                    </Link>
                                    <div class="text-sm mx-2">
                                        Page {{ students.current_page }} of {{ students.last_page }}
                                    </div>
                                    <Link v-if="students.current_page < students.last_page"
                                        :href="students.next_page_url"
                                        class="bg-indigo-700 hover:bg-indigo-500 hover:text-black rounded p-2 px-4 text-white">
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
