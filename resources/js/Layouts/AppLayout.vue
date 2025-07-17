<script setup>
import { ref, computed, onMounted } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";

const page = usePage();
const darkMode = ref(false);
const showingMenu = ref(false);
const userDropdownOpen = ref(false);
const dropdownOpen = ref(null);

const userRole = computed(() => {
    return page.props.auth.user?.roles?.[0].name || null;
});

const logout = () => {
    router.post(route("logout"));
};

onMounted(() => {
    // Recuperamos la preferencia de `localStorage`
    const userPref = localStorage.getItem("theme");
    darkMode.value = userPref === "dark" || (!userPref && window.matchMedia("(prefers-color-scheme: dark)").matches);
    updateDarkClass();
});

// Función para alternar el modo oscuro
function toggleDarkMode() {
    darkMode.value = !darkMode.value;
    localStorage.setItem("theme", darkMode.value ? "dark" : "light");
    updateDarkClass();
}

// Aplicar la clase `dark` al elemento raíz
function updateDarkClass() {
    const root = document.documentElement;
    if (darkMode.value) {
        root.classList.add("dark");
    } else {
        root.classList.remove("dark");
    }
}

function toggleDropdown(id) {
    dropdownOpen.value = dropdownOpen.value === id ? null : id;
}
</script>

<template>
    <div>

        <Head :title="$props.title" />

        <nav class="bg-white border-gray-200 dark:bg-gray-900 dark:border-gray-700">
            <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
                <Link href="/" class="flex items-center space-x-3 rtl:space-x-reverse">
                <ApplicationMark class="h-8 w-8" />
                </Link>

                <div class="flex items-center md:order-2 space-x-3 rtl:space-x-reverse">
                    <button @click="userDropdownOpen = !userDropdownOpen" type="button"
                        class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600">
                        <img class="w-8 h-8 rounded-full object-cover" :src="page.props.auth.user.profile_photo_url"
                            :alt="page.props.auth.user.name" />
                    </button>
                    <div v-show="userDropdownOpen"
                        class="absolute right-4 top-16 z-50 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm dark:bg-gray-700 dark:divide-gray-600">
                        <div class="px-4 py-3">
                            <span class="block text-sm text-gray-900 dark:text-white">{{ page.props.auth.user.name
                            }}</span>
                            <span class="block text-sm text-gray-500 truncate dark:text-gray-400">{{
                                page.props.auth.user.email }}</span>
                        </div>
                        <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                            <li>
                                <Link :href="route('profile.show')"
                                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                Profile</Link>
                            </li>
                            <li>
                                <button @click="logout"
                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                    Sign out
                                </button>
                            </li>
                        </ul>
                    </div>

                    <button @click="showingMenu = !showingMenu" type="button"
                        class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 1h15M1 7h15M1 13h15" />
                        </svg>
                    </button>
                </div>

                <div :class="[
                    'w-full md:flex md:w-auto md:order-1',
                    showingMenu ? 'block' : 'hidden md:block'
                ]">
                    <ul
                        class="flex flex-col font-medium p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:flex-row md:space-x-8 md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">

                        <li v-if="userRole === 'admin' || userRole === 'professor'">
                            <Link :href="route('dashboard')" class="nav-link">Dashboard</Link>
                        </li>

                        <template v-if="userRole === 'admin'">
                            <li>
                                <Link :href="route('programs.index')" class="nav-link">Programs</Link>
                            </li>

                            <li class="relative">
                                <button @click="toggleDropdown('professors')" class="nav-link dropdown-toggle">
                                    Professors
                                    <svg class="w-2.5 h-2.5 ml-2 inline-block" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 4 4 4-4" />
                                    </svg>
                                </button>
                                <div v-show="dropdownOpen === 'professors'" class="dropdown-menu">
                                    <Link :href="route('professors.index')" class="dropdown-item">Index</Link>
                                    <Link :href="route('professors.assignSubjectForm')" class="dropdown-item">Assign
                                    Subject</Link>
                                </div>
                            </li>

                            <li class="relative">
                                <button @click="toggleDropdown('students')" class="nav-link dropdown-toggle">
                                    Students
                                    <svg class="w-2.5 h-2.5 ml-2 inline-block" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 4 4 4-4" />
                                    </svg>
                                </button>
                                <div v-show="dropdownOpen === 'students'" class="dropdown-menu">
                                    <Link :href="route('students.index')" class="dropdown-item">Index</Link>
                                    <Link :href="route('students.assignSubjectForm')" class="dropdown-item">Assign
                                    Subject</Link>
                                </div>
                            </li>

                            <li>
                                <Link :href="route('subjects.index')" class="nav-link">Subjects</Link>
                            </li>
                            <li>
                                <Link :href="route('users.index')" class="nav-link">Admin</Link>
                            </li>
                            <li>
                                <Link :href="route('class-groups.index')" class="nav-link">Class Groups</Link>
                            </li>
                            <li>
                                <Link :href="route('academic-periods.index')" class="nav-link">Academic Periods</Link>
                            </li>
                        </template>

                        <template v-if="userRole === 'professor'">
                            <li>
                                <Link :href="route('professor.subjects')" class="nav-link">My Subjects</Link>
                            </li>
                            <li>
                                <Link :href="route('students.index')" class="nav-link">Students</Link>
                            </li>
                            <li>
                                <Link :href="route('profile.show')" class="nav-link">Profile</Link>
                            </li>
                        </template>

                        <template v-if="userRole === 'student'">
                            <li>
                                <Link :href="route('professor.subjects')" class="nav-link">My Subjects</Link>
                            </li>
                            <li>
                                <Link :href="route('professors.index')" class="nav-link">Professors</Link>
                            </li>
                            <li>
                                <Link :href="route('profile.show')" class="nav-link">Profile</Link>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </nav>


        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <slot name="header"></slot>

                <!-- Dark Mode Toggle Button -->
                <button @click="toggleDarkMode"
                    class="text-gray-500 dark:text-gray-400 hover:text-blue-500 focus:outline-none transition duration-300 ease-in-out transform hover:scale-110">
                    <!-- Transición de ícono de sol/luna -->
                    <i :class="[
                        darkMode ? 'fas fa-moon' : 'fas fa-sun',
                        'transition-transform ease-in-out duration-300'
                    ]" class="w-6 h-6"></i>
                </button>
            </div>
        </header>
        <main class="p-4">
            <slot />
        </main>
    </div>
</template>

<style scoped>
body {
    transition: background-color 1s ease, color 1s ease;
}

.nav-link {
    @apply block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent;
}

.dropdown-menu {
    @apply absolute mt-2 z-50 bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600;
}

.dropdown-item {
    @apply block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white;
}
</style>
