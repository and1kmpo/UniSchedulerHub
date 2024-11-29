<template>
    <div>
        <button @click="openModal('create')" class="bg-purple-500 text-white px-4 py-2 rounded">Add Permission</button>
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="permission in permissions" :key="permission.id">
                    <td>{{ permission.id }}</td>
                    <td>{{ permission.name }}</td>
                    <td>
                        <button @click="openModal('edit', permission)">Edit</button>
                        <button @click="deletePermission(permission)">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <permission-modal v-if="modalOpen" :permission="selectedPermission" @close="closeModal" />
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
