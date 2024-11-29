<template>
    <div>
        <button @click="openModal('create')" class="bg-green-500 text-white px-4 py-2 rounded">Add Role</button>
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="role in roles" :key="role.id">
                    <td>{{ role.id }}</td>
                    <td>{{ role.name }}</td>
                    <td>
                        <span v-for="permission in role.permissions" :key="permission.id">
                            {{ permission.name }}
                        </span>
                    </td>
                    <td>
                        <button @click="openModal('edit', role)">Edit</button>
                        <button @click="deleteRole(role)">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <role-modal v-if="modalOpen" :role="selectedRole" @close="closeModal" />
    </div>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
});

const addUser = () => {
    // Logic for adding a user
    console.log("Add User");
};

const editUser = (user) => {
    // Logic for editing a user
    console.log("Edit User:", user);
};

const deleteUser = (id) => {
    // Logic for deleting a user
    console.log("Delete User ID:", id);
};
</script>
