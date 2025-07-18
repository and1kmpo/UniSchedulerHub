<template>
    <AppLayout>
        <div class="flex h-screen bg-gray-50 dark:bg-gray-900 transition-all duration-300">
            <aside
                :class="[isSidebarOpen ? 'w-64' : 'w-16', 'bg-indigo-700 dark:bg-indigo-900 text-white flex flex-col items-center lg:items-stretch transition-all duration-300 h-screen sticky top-0']">
                <div class="flex items-center justify-between p-4">
                    <span v-if="isSidebarOpen" class="text-xl font-bold">Admin Panel</span>
                    <button @click="toggleSidebar"
                        class="text-white hover:bg-indigo-600 dark:hover:bg-indigo-800 p-2 rounded">
                        <i :class="isSidebarOpen ? 'fas fa-chevron-left' : 'fas fa-bars'"></i>
                    </button>
                </div>

                <ul class="space-y-4 w-full mt-4 flex-1">
                    <li><button @click="viewSection('users')" class="menu-btn"><i class="fas fa-users"></i><span
                                v-if="isSidebarOpen">Users</span></button></li>
                    <li><button @click="viewSection('roles')" class="menu-btn"><i class="fas fa-user-shield"></i><span
                                v-if="isSidebarOpen">Roles</span></button></li>
                    <li><button @click="viewSection('permissions')" class="menu-btn"><i class="fas fa-key"></i><span
                                v-if="isSidebarOpen">Permissions</span></button></li>
                </ul>
            </aside>

            <div class="flex-1 flex flex-col">
                <header
                    class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center transition-all duration-300">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">{{ capitalizeSection(currentSection) }}
                    </h1>
                    <button @click="toggleTheme"
                        class="p-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-full">
                        <i :class="darkMode ? 'fas fa-sun' : 'fas fa-moon'" class="text-gray-700 dark:text-white"></i>
                    </button>
                </header>

                <main class="flex-1 p-6 overflow-auto">
                    <UserManagement v-if="currentSection === 'users'" :users="users" @go-to-page="goToPage" />
                    <RolesManagement v-if="currentSection === 'roles'" />
                    <PermissionManagement v-if="currentSection === 'permissions'" />
                </main>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import UserManagement from "@/Components/Users/UserManagement.vue";
import RolesManagement from "@/Components/Users/RoleManagement.vue";
import PermissionManagement from "@/Components/Users/PermissionManagement.vue";

export default {
    components: {
        AppLayout,
        UserManagement,
        RolesManagement,
        PermissionManagement,

    },
    props: {
        users: Object,
    },
    data() {
        return {
            currentSection: "users",
            isSidebarOpen: true,
            darkMode: localStorage.getItem("darkMode") === "true",
        };
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
        },
        toggleTheme() {
            this.darkMode = !this.darkMode;
            localStorage.setItem("darkMode", this.darkMode);
            document.documentElement.classList.toggle("dark", this.darkMode);
        },
        viewSection(section) {
            this.currentSection = section;
        },
        capitalizeSection(section) {
            return section.charAt(0).toUpperCase() + section.slice(1);
        },
        goToPage(pageNumber) {
            this.$inertia.get(`/users?page=${pageNumber}`);
        },
    },
};
</script>

<style scoped>
.menu-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
    text-align: center;
    padding: 0.75rem;
    border-radius: 0.75rem;
    transition: background 0.2s;
}

.menu-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
}
</style>
