<template>
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Permission Management</h1>

        <!-- Botón para agregar un nuevo permiso -->
        <button @click="openModal('create')" class="bg-purple-500 text-white px-4 py-2 rounded mb-4">
            Add Permission
        </button>

        <!-- Tabla de permisos -->
        <table class="min-w-full border border-gray-300 shadow-md text-center">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-4 py-2">ID</th>
                    <th class="border px-4 py-2">Name</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="permission in permissions" :key="permission.id" class="hover:bg-gray-100">
                    <td class="border px-4 py-2">{{ permission.id }}</td>
                    <td class="border px-4 py-2">{{ permission.name }}</td>
                    <td class="border px-4 py-2">
                        <button @click="openModal('edit', permission)"
                            class="bg-blue-500 text-white px-2 py-1 rounded mr-2">
                            Edit
                        </button>
                        <button @click="deletePermission(permission)" class="bg-red-500 text-white px-2 py-1 rounded">
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Modal -->
        <div v-if="modalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
                <h2 class="text-xl font-semibold mb-4">
                    {{ isEditing ? "Edit Permission" : "Create Permission" }}
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
                        <button type="submit" class="bg-purple-500 text-white px-4 py-2 rounded">
                            Save
                        </button>
                    </div>
                </form>
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
            permissions: [], // Lista de permisos
            modalOpen: false, // Estado del modal
            isEditing: false, // Si el modal está en modo edición
            form: { id: null, name: "" }, // Formulario del modal
        };
    },
    methods: {
        async fetchPermissions() {
            try {
                const response = await axios.get("/permissions");
                this.permissions = response.data.data;
            } catch (error) {
                Swal.fire("Error", "Failed to load permissions.", "error");
            }
        },
        openModal(mode, permission = null) {
            this.modalOpen = true;
            this.isEditing = mode === "edit";
            if (this.isEditing && permission) {
                this.form = { ...permission };
            } else {
                this.form = { id: null, name: "" };
            }
        },
        closeModal() {
            this.modalOpen = false;
        },
        async handleSubmit() {
            try {
                if (this.isEditing) {
                    await axios.put(`/permissions/${this.form.id}`, this.form);
                    Swal.fire("Success", "Permission updated successfully.", "success");
                } else {
                    const response = await axios.post("/permissions", this.form);
                    this.permissions.push(response.data);
                    Swal.fire("Success", "Permission created successfully.", "success");
                }
                this.closeModal();
                await this.fetchPermissions();
            } catch (error) {
                Swal.fire("Error", "Failed to save the permission.", "error");
            }
        },
        async deletePermission(permission) {
            const confirmation = await Swal.fire({
                title: "Are you sure?",
                text: `Delete permission "${permission.name}"?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
            });

            if (confirmation.isConfirmed) {
                try {
                    await axios.delete(`/permissions/${permission.id}`);
                    this.permissions = this.permissions.filter((p) => p.id !== permission.id);
                    Swal.fire("Deleted!", "Permission deleted successfully.", "success");
                } catch (error) {
                    Swal.fire("Error", "Failed to delete the permission.", "error");
                }
            }
        },
    },
    async mounted() {
        await this.fetchPermissions();
    },
};
</script>
