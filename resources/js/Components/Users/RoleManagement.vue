<template>
    <div>
        <!-- Botón para agregar un nuevo rol -->
        <button @click="openModal('create')" class="bg-green-500 text-white px-4 py-2 rounded mb-4">
            Add Role
        </button>

        <!-- Tabla de roles -->
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-center rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Permissions
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="role in roles" :key="role.id"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ role.id }}
                            </th>
                            <td class="px-6 py-4">
                                {{ role.name }}
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="role.permissions.length">
                                    <span v-for="permission in role.permissions" :key="permission.id"
                                        class="bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded-full mr-1">
                                        {{ permission.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <button @click="openModal('edit', role)"
                                    class="bg-blue-500 text-white px-2 py-1 rounded mr-2">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="deleteRole(role)" class="bg-red-500 text-white px-2 py-1 rounded mr-2">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <button @click="managePermissions(role)"
                                    class="bg-purple-500 text-white px-2 py-1 rounded">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
                <h2 class="text-xl font-semibold mb-4">
                    {{ isEditing ? "Edit Role" : "Create Role" }}
                </h2>
                <form @submit.prevent="handleSubmit">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Name:</label>
                        <input v-model="form.name" type="text" class="w-full border rounded p-2" required />
                    </div>
                    <div class="flex justify-end">
                        <button @click="closeModal" type="button"
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div v-if="permissionModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-semibold mb-4">Manage Permissions for {{ selectedRole.name }}</h2>
            <div class="mb-4">
                <label v-for="permission in allPermissions" :key="permission.id"
                    class="mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 block">
                    <input type="checkbox" :value="permission.id" v-model="selectedPermissions"
                        class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                    {{ permission.name }}
                </label>
            </div>
            <div class="flex justify-end">
                <button @click="closePermissionModal" class="bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2">
                    Cancel
                </button>
                <button @click="savePermissions" class="bg-purple-500 text-white px-4 py-2 rounded">
                    Save
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import Swal from "sweetalert2";
import axios from "axios";

export default {
    data() {
        return {
            roles: [], // Lista de roles
            allPermissions: [], // Lista de permisos disponibles
            selectedRole: null, // Rol seleccionado para gestionar permisos
            selectedPermissions: [], // Permisos seleccionados para el rol
            permissionModalOpen: false, // Estado del modal de permisos
            isModalOpen: false, // Estado del modal de roles
            isEditing: false, // Determina si el modal está en modo edición
            form: { id: null, name: "" }, // Datos del formulario para crear/editar un rol
        };
    },
    methods: {
        async fetchRoles() {
            try {
                const response = await axios.get("/roles");
                this.roles = response.data.data;
            } catch (error) {
                Swal.fire("Error", "Failed to load roles.", "error");
            }
        },
        openModal(mode, role = null) {
            this.isModalOpen = true;
            this.isEditing = mode === "edit";
            if (this.isEditing && role) {
                this.form = { ...role };
            } else {
                this.form = { id: null, name: "" };
            }
        },
        closeModal() {
            this.isModalOpen = false;
        },
        async fetchPermissions() {
            try {
                const response = await axios.get("/permissions");
                this.allPermissions = response.data.data;
            } catch (error) {
                Swal.fire("Error", "Failed to load permissions.", "error");
            }
        },
        managePermissions(role) {
            this.selectedRole = role;
            this.selectedPermissions = role.permissions.map((perm) => perm.id);
            this.permissionModalOpen = true;
        },
        closePermissionModal() {
            this.permissionModalOpen = false;
        },
        async savePermissions() {
            try {
                await axios.post(`/roles/${this.selectedRole.id}/permissions`, {
                    permissions: this.selectedPermissions,
                });
                Swal.fire("Success", "Permissions updated successfully.", "success");
                this.closePermissionModal();
                await this.fetchRoles();
            } catch (error) {
                Swal.fire("Error", "Failed to update permissions.", "error");
            }
        },
        async handleSubmit() {
            try {
                if (this.isEditing) {
                    await axios.put(`/roles/${this.form.id}`, this.form);
                    Swal.fire("Success", "Role updated successfully.", "success");
                } else {
                    const response = await axios.post("/roles", this.form);
                    this.roles.push(response.data);
                    Swal.fire("Success", "Role created successfully.", "success");
                }
                this.closeModal();
                await this.fetchRoles();
            } catch (error) {
                Swal.fire("Error", "Failed to save the role.", "error");
            }
        },
        async deleteRole(role) {
            const confirmation = await Swal.fire({
                title: "Are you sure?",
                html: `Delete role "${role.name}" this action is <span class="text-red-500 font-bold">irreversible<span/>.?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
            });

            if (confirmation.isConfirmed) {
                try {
                    await axios.delete(`/roles/${role.id}`);
                    this.roles = this.roles.filter((r) => r.id !== role.id);
                    Swal.fire("Deleted!", "Role deleted successfully.", "success");
                } catch (error) {
                    Swal.fire("Error", "Failed to delete the role.", "error");
                }
            }
        },
    },
    async mounted() {
        await this.fetchRoles();
        await this.fetchPermissions();

    },
};
</script>
