<template>
    <div>

        <div class="w-full flex items-center space-x-4 mb-4">
            <!-- Botón para agregar usuario -->
            <button @click="openModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded">
                Add User
            </button>
            <!-- Formulario de búsqueda -->
            <form class="flex-1 relative">
                <label for="default-search"
                    class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input type="search" id="default-search" v-model="searchQuery" class=" block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg
                        bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600
                        dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="Search user..." />
                </div>
            </form>
        </div>


        <div class="relative">
            <!-- Tabla de usuarios -->
            <table class="min-w-full table-auto dark:bg-gray-800 dark:text-white relative">
                <thead class="bg-gray-200 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-center">ID</th>
                        <th class="px-4 py-2 text-center">Name</th>
                        <th class="px-4 py-2 text-center">Email</th>
                        <th class="px-4 py-2 text-center">Role</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in localUsers.data" :key="user.id" class="border-t dark:border-gray-700">
                        <td class="px-4 py-2 text-center">{{ user.id }}</td>
                        <td class="px-4 py-2 text-center">{{ user.name }}</td>
                        <td class="px-4 py-2 text-center">{{ user.email }}</td>
                        <td class="px-4 py-2 text-center">
                            <span v-for="role in user.roles" :key="role.id">{{ role.name }}</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <label class="inline-flex items-center me-5 cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer" :checked="user.status === '1'"
                                    @change="toggleStatus(user)">
                                <div
                                    class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-transform after:duration-300 dark:border-gray-600 peer-checked:bg-purple-600">
                                </div>
                                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ user.status
                                    ===
                                    '1' ? 'Active' : user.status === '2' ? 'Inactive' : 'No data status' }}</span>
                            </label>
                        </td>
                        <td class="px-4 py-2 flex gap-4 justify-center text-center">
                            <button @click="editUser(user)"
                                class="dark:text-indigo-400 text-indigo-800 transition ease-in-out hover:-translate-y-1 hover:scale-110 duration-300">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button @click="deleteUser(user)"
                                class="text-red-600 dark:text-red-400 hover:text-red-800 transition ease-in-out hover:-translate-y-1 hover:scale-110 duration-300">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Spinner -->
            <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-50 z-10">
                <i class="fa-solid fa-spinner fa-spin-pulse text-xl text-blue-500"></i>
            </div>
        </div>


        <!-- Paginación -->
        <div class="mt-4 flex flex-wrap justify-center items-center gap-2">
            <!-- Botón de página anterior -->
            <button @click="changePage(localUsers.prev_page_url)" :disabled="!localUsers.prev_page_url"
                class="px-4 py-2 border rounded bg-blue-600 text-white disabled:bg-gray-400 disabled:cursor-not-allowed transition">
                <i class="fas fa-angles-left"></i>
            </button>

            <!-- Texto de página actual -->
            <span class="px-4 py-2 text-center dark:text-white">
                {{ users.current_page }} of {{ users.last_page }}
            </span>


            <!-- Botón de página siguiente -->
            <button @click="changePage(localUsers.next_page_url)" :disabled="!localUsers.next_page_url"
                class="px-4 py-2 border rounded bg-blue-600 text-white disabled:bg-gray-400 disabled:cursor-not-allowed transition">
                <i class="fas fa-angles-right"></i>
            </button>
        </div>

        <!-- Modal para creación -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ form.id ? 'Edit User' :
                        'Create User' }}</h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="form.id ? updateUser() : createUser()">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 dark:text-gray-300">Name:</label>
                        <input v-model="form.name" type="text" id="name"
                            class="w-full border-gray-300 rounded-md dark:bg-gray-600 dark:border-gray-500" required />
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 dark:text-gray-300">Email:</label>
                        <input v-model="form.email" type="email" id="email"
                            class="w-full border-gray-300 rounded-md dark:bg-gray-600 dark:border-gray-500" required />
                    </div>
                    <div class="mb-4">
                        <label for="document" class="block text-gray-700 dark:text-gray-300">Document:</label>
                        <input v-model="form.document" type="text" id="document"
                            class="w-full border-gray-300 rounded-md dark:bg-gray-600 dark:border-gray-500"
                            pattern="\d*">
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 dark:text-gray-300">Phone:</label>
                        <input v-model="form.phone" type="text" id="phone"
                            class="w-full border-gray-300 rounded-md dark:bg-gray-600 dark:border-gray-500"
                            pattern="\d*" />
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 dark:text-gray-300">Address:</label>
                        <input v-model="form.address" type="text" id="address"
                            class="w-full border-gray-300 rounded-md dark:bg-gray-600 dark:border-gray-500" />
                    </div>
                    <div class="mb-4">
                        <label for="city" class="block text-gray-700 dark:text-gray-300">City:</label>
                        <input v-model="form.city" type="text" id="city"
                            class="w-full border-gray-300 rounded-md dark:bg-gray-600 dark:border-gray-500" />
                    </div>
                    <div class="mb-4">
                        <label for="role" class="block text-gray-700 dark:text-gray-300">Role:</label>
                        <select v-model="form.role" id="role"
                            class="w-full border-gray-300 rounded-md dark:bg-gray-600 dark:border-gray-500">
                            <option value="student">Student</option>
                            <option value="professor">Professor</option>
                            <option value="admin">Admin</option>
                        </select>

                    </div>

                    <!-- Campos dinámicos -->
                    <div v-if="form.role === 'student'">
                        <div class="mb-4">
                            <label for="semester" class="block text-gray-700 dark:text-gray-300">Semester:</label>
                            <input v-model="form.semester" type="number" id="semester"
                                class="w-full border-gray-300 rounded-md dark:bg-gray-600 dark:border-gray-500" />
                        </div>
                        <div class="mb-4">
                            <label for="program_id" class="block text-gray-700 dark:text-gray-300">Program ID:</label>
                            <input v-model="form.program_id" type="number" id="program_id"
                                class="w-full border-gray-300 rounded-md dark:bg-gray-600 dark:border-gray-500" />
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md">
                        {{ form.id ? 'Update' : 'Save' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import Swal from 'sweetalert2';
import debounce from 'lodash.debounce';

export default {
    props: {
        users: Object,
    },
    data() {
        return {
            searchQuery: '',
            loading: false,
            localUsers: this.users,

            isModalOpen: false,
            form: {
                name: "",
                email: "",
                role: "",
                semester: null,
                program_id: null,
                document: "",
                phone: "",
                address: "",
                city: ""
            },
        };
    },
    methods: {
        searchData() {
            this.loading = true;
            axios
                .get('/users', { params: { search: this.searchQuery } })
                .then((res) => {
                    this.localUsers = res.data;
                })
                .catch((error) => {
                    console.error('Error fetching users:', error);
                    Swal.fire('Error', 'Failed to fetch users.', 'error');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        changePage(url) {
            if (!url) return;
            this.loading = true;
            axios
                .get(url, { params: { search: this.searchQuery } })
                .then((res) => {
                    this.localUsers = res.data;
                })
                .catch((error) => {
                    console.error('Error fetching users:', error);
                    Swal.fire('Error', 'Failed to fetch users.', 'error');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        openModal(mode) {
            if (mode === "create") {
                this.form = {
                    id: null,
                    name: "",
                    email: "",
                    role: "",
                    semester: null,
                    program_id: null,
                    document: "",
                    phone: "",
                    address: "",
                    city: ""
                };
            }
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
        },
        async createUser() {
            try {
                const response = await axios.post('/users', this.form);
                this.$inertia.reload({ only: ['users'] });
                Swal.fire({
                    title: 'Success!',
                    text: response.data.message,
                    icon: 'success',
                    timer: 3000,
                    timerProgressBar: true
                });
                this.closeModal();
            } catch (error) {
                this.handleFormErrors(error);
            }
        },
        async deleteUser(user) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "This action is irreversible!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/users/${user.id}`);
                    this.$inertia.reload({ only: ['users'] });
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'User deleted successfully.',
                        icon: 'success',
                        timer: 3000,
                        timerProgressBar: true,
                    });
                } catch (error) {
                    Swal.fire('Error', 'Error deleting user.', 'error');
                }
            }
        },
        async editUser(user) {
            try {
                const response = await axios.get(`/users/${user.id}/edit`);
                const userData = response.data;

                this.form = {
                    id: userData.id,
                    name: userData.name,
                    email: userData.email,
                    role: userData.roles[0]?.name || "",
                    semester: null,
                    program_id: null,
                    document: "",
                    phone: "",
                    address: "",
                    city: "",
                };

                if (this.form.role === "professor" && userData.professor) {
                    Object.assign(this.form, {
                        document: userData.professor.document,
                        phone: userData.professor.phone,
                        address: userData.professor.address,
                        city: userData.professor.city,
                    });
                } else if (this.form.role === "student" && userData.student) {
                    Object.assign(this.form, {
                        document: userData.student.document,
                        phone: userData.student.phone,
                        address: userData.student.address,
                        city: userData.student.city,
                        semester: userData.student.semester,
                        program_id: userData.student.program_id,
                    });
                }
                this.openModal("edit");
            } catch (error) {
                Swal.fire('Error', 'Failed to load user data.', 'error');
            }
        },
        async updateUser() {
            try {
                const response = await axios.put(`/users/${this.form.id}`, this.form);
                const updatedUser = response.data;

                const index = this.localUsers.data.findIndex(user => user.id === updatedUser.id);
                if (index !== -1) {
                    this.localUsers.data[index] = { ...this.localUsers.data[index], ...updatedUser };
                }

                this.$inertia.reload({ only: ['users'] });
                Swal.fire({
                    title: 'Success!',
                    text: 'User updated successfully.',
                    icon: 'success',
                    timer: 3000,
                    timerProgressBar: true,
                });
                this.closeModal();
            } catch (error) {
                this.handleFormErrors(error);
            }
        },
        async toggleStatus(user) {
            try {
                const route = user.status === '1'
                    ? `/users/${user.id}/deactivate`
                    : `/users/${user.id}/activate`;

                await axios.patch(route);
                user.status = user.status === '1' ? '2' : '1';
                Swal.fire({
                    icon: 'success',
                    title: 'Status updated!',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            } catch (error) {
                Swal.fire('Error', 'Error updating status.', 'error');
            }
        },
        handleFormErrors(error) {
            if (error.response && error.response.status === 422) {
                const errorMessages = Object.values(error.response.data.errors)
                    .flat()
                    .map(msg => `<div><i class="fas fa-exclamation-circle text-red-600"></i> ${msg}</div>`)
                    .join('<br/>');

                Swal.fire({
                    title: 'Validation Errors',
                    html: errorMessages,
                    icon: 'error',
                });
            } else {
                console.error("Error:", error);
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            }
        }
    },
    watch: {
        searchQuery: debounce(function () {
            this.searchData();
        }, 1000),
    },
    mounted() {
        console.log(this.users);
    }
};
</script>
