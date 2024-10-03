<template>
    <AppLayout>
        <div class="flex min-h-screen bg-gray-100">
            <!-- Sidebar -->
            <div
                class="min-h-screen w-14 md:w-56 overflow-hidden border-r bg-white md:hover:w-56 md:hover:bg-white md:hover:shadow-lg transition-all ease-in duration-300">
                <div class="flex flex-col justify-between h-full pt-2 pb-6">
                    <div>
                        <ul class="mt-6 space-y-2 tracking-wide">
                            <li class="min-w-ma hover:bg-indigo-600 transition ease-in duration-150">
                                <a href="#" class="group flex items-center space-x-4 rounded-full px-4 py-3 text-gray-600"
                                    @click="showUsers">
                                    <i class="fa-solid fa-users mr-2 group-hover:text-white"></i>
                                    <span class="group-hover:text-white">Users</span>
                                </a>
                            </li>
                            <li class="min-w-max hover:bg-indigo-600 transition ease-in duration-150">
                                <a href="#" class="group flex items-center space-x-4 rounded-md px-4 py-3 text-gray-600"
                                    @click="showRoles">
                                    <i class="fa fa-crown mr-2 group-hover:text-white"></i>
                                    <span class="group-hover:text-white">Roles</span>
                                </a>
                            </li>
                            <li class="min-w-max hover:bg-indigo-600 transition ease-in duration-150">
                                <a href="#" class="group flex items-center space-x-4 rounded-md px-4 py-3 text-gray-600"
                                    @click="showPermissions">
                                    <i class="fa fa-key mr-2 group-hover:text-white"></i>
                                    <span class="group-hover:text-white">Permissions</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Main Content -->
            <main class="main flex-grow flex flex-col p-4 md:ml-0">
                <div class="bg-white text-center shadow-md h-full">
                    <div class="mb-4">
                        <div class="container mx-auto">
                            <template v-if="currentView === 'users'">
                                <UsersManagement />
                            </template>
                            <template v-else-if="currentView === 'roles'">
                                <RolesManagement />
                            </template>
                            <template v-else-if="currentView === 'permissions'">
                                <PermissionsManagement />
                            </template>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AppLayout>
</template>
  
<script setup>
import { ref } from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import UsersManagement from "@/components/Security/Users.vue";
import RolesManagement from "@/components/Security/Roles.vue";
import PermissionsManagement from "@/components/Security/Permissions.vue";


const currentView = ref('users');

function showUsers() {
    currentView.value = 'users';
}

function showRoles() {
    currentView.value = 'roles';
}

function showPermissions() {
    currentView.value = 'permissions';
}
</script>
  