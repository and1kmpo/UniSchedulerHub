<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import { useAlert } from "@/Components/Composables/useAlert";

// Acceso a la página actual de Inertia
const page = usePage();

// Props globales
const darkMode = ref(false);
const showingMenu = ref(false);
const userDropdownOpen = ref(null);
const dropdownOpen = ref(null);

const userRole = computed(() => {
    return page.props.auth?.user?.roles?.[0]?.name || null;
});

const logout = () => {
    router.post(route("logout"));
};

function toggleDarkMode() {
    darkMode.value = !darkMode.value;
    localStorage.setItem("theme", darkMode.value ? "dark" : "light");
    updateDarkClass();
}

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

function handleClickOutside(event) {
    const dropdown = document.getElementById("user-dropdown");
    const avatarBtn = document.getElementById("avatar-button");

    if (
        userDropdownOpen.value &&
        dropdown &&
        avatarBtn &&
        !dropdown.contains(event.target) &&
        !avatarBtn.contains(event.target)
    ) {
        userDropdownOpen.value = false;
    }

    if (
        dropdownOpen.value &&
        !event.target.closest(".dropdown-toggle") &&
        !event.target.closest(".dropdown-menu")
    ) {
        dropdownOpen.value = null;
    }
}

onMounted(() => {
    const userPref = localStorage.getItem("theme");
    darkMode.value =
        userPref === "dark" ||
        (!userPref &&
            window.matchMedia("(prefers-color-scheme: dark)").matches);
    updateDarkClass();

    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>


<template>
    <div>

        <Head :title="$props.title" />

        <nav class="bg-white border-gray-200 dark:bg-gray-900 dark:border-gray-700 relative z-50">
            <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
                <Link href="/" class="flex items-center space-x-3 rtl:space-x-reverse">
                <ApplicationMark class="h-8 w-8" />
                </Link>

                <div class="flex items-center md:order-2 space-x-3 rtl:space-x-reverse relative">
                    <button id="avatar-button" @click="userDropdownOpen = !userDropdownOpen" type="button"
                        class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600">
                        <img class="w-8 h-8 rounded-full object-cover" :src="page.props.auth.user.profile_photo_url"
                            :alt="page.props.auth.user.name" />
                    </button>

                    <transition name="fade">
                        <div v-show="userDropdownOpen" id="user-dropdown"
                            class="absolute right-0 top-12 z-50 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm dark:bg-gray-700 dark:divide-gray-600 min-w-[12rem]">
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
                                    Profile
                                    </Link>
                                </li>
                                <li>
                                    <button @click="logout"
                                        class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                        Sign out
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </transition>

                    <button @click="showingMenu = !showingMenu" type="button"
                        class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-transform hover:rotate-90">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 1h15M1 7h15M1 13h15" />
                        </svg>
                    </button>

                </div>

                <div class="hidden lg:block w-full lg:w-auto lg:order-1">
                    <ul
                        class="flex flex-col font-medium p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:flex-row md:space-x-8 md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
                        <li v-if="
                            userRole === 'admin' || userRole === 'professor'
                        ">
                            <Link :href="route('dashboard')" class="nav-link">Dashboard</Link>
                        </li>

                        <template v-if="userRole === 'admin'">
                            <li>
                                <Link :href="route('programs.index')" class="nav-link">Programs</Link>
                            </li>

                            <li class="relative group">
                                <button @click="toggleDropdown('professors')"
                                    class="nav-link dropdown-toggle flex items-center gap-1">
                                    Professors
                                    <svg class="w-2.5 h-2.5 ml-2 inline-block transition-transform duration-300"
                                        :class="{ 'rotate-180': dropdownOpen === 'professors' }"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 4 4 4-4" />
                                    </svg>
                                </button>
                                <transition name="dropdown-fade">
                                    <div v-if="dropdownOpen === 'professors'"
                                        class="absolute left-1/2 transform -translate-x-1/2 mt-2 w-48 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded shadow-lg z-40">
                                        <Link :href="route('professors.index')"
                                            class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Index
                                        </Link>
                                        <Link :href="route('professors.assignSubjectForm')"
                                            class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Assign Subject
                                        </Link>
                                    </div>
                                </transition>

                            </li>

                            <li class="relative group">
                                <button @click="toggleDropdown('students')"
                                    class="nav-link dropdown-toggle flex items-center gap-1">
                                    Students
                                    <svg class="w-2.5 h-2.5 ml-2 inline-block transition-transform duration-300"
                                        :class="{ 'rotate-180': dropdownOpen === 'students' }"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 4 4 4-4" />
                                    </svg>
                                </button>
                                <transition name="dropdown-fade">
                                    <div v-if="dropdownOpen === 'students'"
                                        class="absolute left-1/2 transform -translate-x-1/2 mt-2 w-48 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded shadow-lg z-40">
                                        <Link :href="route('students.index')"
                                            class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Index
                                        </Link>
                                        <Link :href="route('students.assignSubjectForm')"
                                            class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Assign Subject
                                        </Link>
                                    </div>
                                </transition>

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
                                <Link :href="route('student.subjects')" class="nav-link">My Subjects</Link>
                            </li>
                            <li>
                                <Link :href="route('student.subject-enrollment.index')" class="nav-link">Subject
                                Enrollment</Link>
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

                <transition name="fade">
                    <div v-if="showingMenu" @click="showingMenu = false"
                        class="fixed inset-0 bg-black/40 z-40 lg:hidden"></div>
                </transition>

                <transition name="slide">
                    <div v-if="showingMenu"
                        class="lg:hidden fixed top-0 right-0 z-50 w-64 h-full bg-white dark:bg-gray-800 shadow-lg p-6 overflow-y-auto">

                        <button @click="showingMenu = false"
                            class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-none text-3xl z-50">
                            &times;
                        </button>


                        <ul class="space-y-4 mt-10">
                            <template v-if="
                                userRole === 'admin' ||
                                userRole === 'professor'
                            ">
                                <li>
                                    <Link :href="route('dashboard')" class="nav-link">Dashboard</Link>
                                </li>
                            </template>

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
                                    <div :class="{
                                        show: dropdownOpen === 'professors',
                                    }" class="dropdown-menu absolute">
                                        <Link :href="route('professors.index')" class="dropdown-item">Index</Link>
                                        <Link :href="route(
                                            'professors.assignSubjectForm'
                                        )
                                            " class="dropdown-item">Assign Subject</Link>
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
                                    <div :class="{
                                        show: dropdownOpen === 'students',
                                    }" class="dropdown-menu absolute">
                                        <Link :href="route('students.index')" class="dropdown-item">Index</Link>
                                        <Link :href="route(
                                            'students.assignSubjectForm'
                                        )
                                            " class="dropdown-item">Assign Subject</Link>
                                    </div>
                                </li>

                                <li class="relative">
                                    <div :class="{
                                        show: dropdownOpen === 'students',
                                    }">
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
                                    <Link :href="route('academic-periods.index')" class="nav-link">Academic Periods
                                    </Link>
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
                </transition>
            </div>
        </nav>

        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <slot name="header"></slot>
                <button @click="toggleDarkMode"
                    class="text-gray-500 dark:text-gray-400 hover:text-blue-500 focus:outline-none transition duration-300 ease-in-out transform hover:scale-110">
                    <i :class="[
                        darkMode ? 'fas fa-moon' : 'fas fa-sun',
                        'transition-transform ease-in-out duration-300',
                    ]" class="w-6 h-6"></i>
                </button>

            </div>
        </header>

        <main>
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

/* Haciendo que el dropdown flote por encima de otros elementos */
/* Para asegurarse de que el submenú no mueva otros elementos */
.dropdown-menu {
    @apply mt-2 bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600;
    position: absolute;
    top: 100%;
    right: 0;
    z-index: 9999;
    visibility: hidden;
    /* Inicialmente oculto */
    opacity: 0;
    transition: visibility 0.3s, opacity 0.3s ease-in-out;
    /* Agregar transición para la visibilidad */
}

/* Cuando el dropdown está abierto */
.dropdown-menu.show {
    visibility: visible;
    opacity: 1;
}

.dropdown-item {
    @apply block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active {
    transition: transform 0.3s ease-out;
}

.slide-enter-from {
    transform: translateX(100%);
}

.slide-leave-active {
    transition: transform 0.3s ease-in;
}

.slide-leave-to {
    transform: translateX(100%);
}

.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
    transition: all 0.2s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
    opacity: 0;
    transform: translateY(0.5rem);
    /* hacia abajo */
}

@media (min-width: 768px) and (max-width: 1050px) {
    .dropdown-toggle svg {
        margin-left: 0.25rem;
        flex-shrink: 0;
    }

    .dropdown-toggle {
        gap: 0.25rem;
        white-space: nowrap;
    }
}

@media (min-width: 768px) and (max-width: 1024px) {
    .nav-link {
        font-size: 0.875rem;
        /* reduce tamaño fuente */
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
}
</style>
