<template>
    <div class="role-management">
        <h1>Gestión de Roles de Usuarios</h1>

        <!-- Selección de Usuario -->
        <div class="user-selection">
            <label for="user">Selecciona un Usuario:</label>
            <select v-model="selectedUser" @change="fetchUserRoles">
                <option v-for="user in users" :key="user.id" :value="user">
                    {{ user.name }} ({{ user.email }})
                </option>
            </select>
        </div>

        <!-- Roles Asignados -->
        <div v-if="selectedUser" class="user-roles">
            <h2>Roles Asignados a {{ selectedUser.name }}</h2>
            <ul>
                <li v-for="role in assignedRoles" :key="role">
                    {{ role }}
                    <button @click="removeRole(role)">Eliminar</button>
                </li>
            </ul>
        </div>

        <!-- Asignar Nuevo Rol -->
        <div v-if="selectedUser" class="assign-role">
            <h3>Asignar Nuevo Rol</h3>
            <select v-model="newRole">
                <option v-for="role in availableRoles" :key="role" :value="role">
                    {{ role }}
                </option>
            </select>
            <button @click="assignRole">Asignar Rol</button>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            users: [],          // Lista de usuarios
            selectedUser: null, // Usuario seleccionado
            assignedRoles: [],  // Roles asignados al usuario seleccionado
            availableRoles: [], // Lista de roles disponibles
            newRole: null,      // Nuevo rol a asignar
        };
    },
    created() {
        this.fetchUsers();
        this.fetchAvailableRoles();
    },
    methods: {
        // Cargar usuarios desde el backend
        async fetchUsers() {
            try {
                const response = await axios.get('/api/users');
                this.users = response.data;
            } catch (error) {
                console.error('Error al cargar usuarios:', error);
            }
        },
        // Cargar roles disponibles desde el backend
        async fetchAvailableRoles() {
            try {
                const response = await axios.get('/api/roles');
                this.availableRoles = response.data;
            } catch (error) {
                console.error('Error al cargar roles disponibles:', error);
            }
        },
        // Cargar roles asignados al usuario seleccionado
        async fetchUserRoles() {
            if (!this.selectedUser) return;
            try {
                const response = await axios.get(`/api/users/${this.selectedUser.id}/roles`);
                this.assignedRoles = response.data;
            } catch (error) {
                console.error('Error al cargar roles del usuario:', error);
            }
        },
        // Asignar un rol al usuario seleccionado
        async assignRole() {
            if (!this.newRole || !this.selectedUser) return;
            try {
                await axios.post(`/assign-role/${this.selectedUser.id}`, { role: this.newRole });
                this.assignedRoles.push(this.newRole); // Agregar rol a la lista en el frontend
                this.newRole = null;
            } catch (error) {
                console.error('Error al asignar rol:', error);
            }
        },
        // Eliminar un rol del usuario seleccionado
        async removeRole(role) {
            try {
                await axios.delete(`/remove-role/${this.selectedUser.id}`, { data: { role } });
                this.assignedRoles = this.assignedRoles.filter(r => r !== role);
            } catch (error) {
                console.error('Error al eliminar rol:', error);
            }
        },
    },
};
</script>

<style scoped>
.role-management {
    max-width: 600px;
    margin: auto;
}

.user-selection,
.assign-role {
    margin-bottom: 20px;
}

button {
    margin-left: 10px;
}
</style>
