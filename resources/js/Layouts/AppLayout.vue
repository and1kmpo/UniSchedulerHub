<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import ApplicationCompactMark from "@/Components/ApplicationCompactMark.vue";

defineProps({
    title: {
        type: String,
        default: "",
    },
});

// Acceso a la página actual de Inertia
const page = usePage();

const darkMode = ref(false);
const showingMenu = ref(false);
const userDropdownOpen = ref(null);
const dropdownOpen = ref(null);

const navItems = computed(() =>
    (page.props.navigation?.main ?? []).map((item) => {
        if (item.children?.length === 1) {
            return item.children[0];
        }

        return item;
    })
);

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

        <nav class="relative z-50 border-b border-border-light bg-surface dark:border-border-dark dark:bg-surface-dark">
            <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
                <Link href="/" class="flex items-center space-x-3 rtl:space-x-reverse">
                    <ApplicationCompactMark class="h-9 w-9 shrink-0" />
                    <span class="hidden leading-tight sm:block">
                        <span class="block text-sm font-bold tracking-tight text-ink dark:text-white">
                            TARRAYA
                        </span>
                        <span class="block font-mono text-[10px] uppercase tracking-wide text-slate-500 dark:text-zinc-400">
                            Academic OS
                        </span>
                    </span>
                </Link>

                <div class="flex items-center md:order-2 space-x-3 rtl:space-x-reverse relative">
                    <button
                        @click="toggleDarkMode"
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 transition hover:bg-brand-50 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:text-zinc-400 dark:hover:bg-brand-500/10 dark:hover:text-brand-300"
                        :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                    >
                        <i :class="[
                            darkMode ? 'fas fa-moon' : 'fas fa-sun',
                            'transition-transform ease-in-out duration-300',
                        ]"></i>
                    </button>

                    <button id="avatar-button" @click="userDropdownOpen = !userDropdownOpen" type="button"
                        class="flex rounded-full border border-border-light bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:border-border-dark dark:bg-surface-dark">
                        <img class="w-8 h-8 rounded-full object-cover" :src="page.props.auth.user.profile_photo_url"
                            :alt="page.props.auth.user.name" />
                    </button>

                    <transition name="fade">
                        <div v-show="userDropdownOpen" id="user-dropdown"
                            class="absolute right-0 top-12 z-50 min-w-[12rem] list-none divide-y divide-slate-100 rounded-lg border border-border-light bg-surface text-base shadow-sm dark:divide-border-dark dark:border-border-dark dark:bg-surface-dark">
                            <div class="px-4 py-3">
                                <span class="block text-sm text-ink dark:text-white">{{ page.props.auth.user.name
                                    }}</span>
                                <span class="block truncate text-sm text-slate-500 dark:text-zinc-400">{{
                                    page.props.auth.user.email }}</span>
                            </div>
                            <ul class="py-2 text-sm text-slate-700 dark:text-zinc-200">
                                <li>
                                    <Link :href="route('profile.show')"
                                        class="block px-4 py-2 hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-white">
                                    Profile
                                    </Link>
                                </li>
                                <li>
                                    <button @click="logout"
                                        class="block w-full px-4 py-2 text-left hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-white">
                                        Sign out
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </transition>

                    <button @click="showingMenu = !showingMenu" type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg p-2 text-sm text-slate-500 transition-transform hover:bg-brand-50 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:text-zinc-400 dark:hover:bg-brand-500/10 lg:hidden">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 1h15M1 7h15M1 13h15" />
                        </svg>
                    </button>

                </div>

                <div class="hidden lg:block w-full lg:w-auto lg:order-1">
                    <ul
                        class="mt-4 flex flex-col rounded-lg border border-border-light bg-slate-50 p-4 font-medium dark:border-border-dark dark:bg-zinc-900 md:mt-0 md:flex-row md:space-x-8 md:border-0 md:bg-transparent md:p-0 md:dark:bg-transparent">
                        <li v-for="item in navItems" :key="item.route ?? item.label" class="relative">
                            <button
                                v-if="item.children"
                                type="button"
                                class="dropdown-toggle nav-link inline-flex items-center gap-2"
                                @click="toggleDropdown(item.label)"
                            >
                                {{ item.label }}
                                <i class="fas fa-chevron-down text-[0.65rem]"></i>
                            </button>

                            <transition name="dropdown-fade">
                                <ul
                                    v-if="item.children && dropdownOpen === item.label"
                                    class="dropdown-menu show"
                                >
                                    <li v-for="child in item.children" :key="child.route">
                                        <Link
                                            :href="route(child.route)"
                                            class="dropdown-item"
                                            @click="dropdownOpen = null"
                                        >
                                            {{ child.label }}
                                        </Link>
                                    </li>
                                </ul>
                            </transition>

                            <Link v-if="!item.children" :href="route(item.route)" class="nav-link">
                                {{ item.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <transition name="fade">
                    <div v-if="showingMenu" @click="showingMenu = false"
                        class="fixed inset-0 bg-black/40 z-40 lg:hidden"></div>
                </transition>

                <transition name="slide">
                    <div v-if="showingMenu"
                        class="fixed right-0 top-0 z-50 h-full w-72 overflow-y-auto border-l border-border-light bg-surface p-6 shadow-sm dark:border-border-dark dark:bg-surface-dark lg:hidden">

                        <button @click="showingMenu = false"
                            class="absolute right-4 top-4 z-50 text-3xl text-slate-500 hover:text-ink focus:outline-none dark:text-zinc-400 dark:hover:text-white">
                            &times;
                        </button>


                        <ul class="space-y-5 mt-10">
                            <li v-for="item in navItems" :key="item.route ?? item.label">
                                <div v-if="item.children" class="space-y-2">
                                    <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-zinc-400">
                                        {{ item.label }}
                                    </p>
                                    <Link
                                        v-for="child in item.children"
                                        :key="child.route"
                                        :href="route(child.route)"
                                        class="mobile-child-link"
                                    >
                                        {{ child.label }}
                                    </Link>
                                </div>

                                <Link v-if="!item.children" :href="route(item.route)" class="nav-link">
                                    {{ item.label }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </transition>
            </div>
        </nav>

        <header class="border-b border-border-light bg-surface dark:border-border-dark dark:bg-surface-dark">
            <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                <slot name="header"></slot>
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
    @apply block rounded-md px-3 py-2 text-ink transition hover:bg-brand-50 hover:text-brand-700 md:border-0 md:p-0 md:hover:bg-transparent md:hover:text-brand-700 dark:text-white dark:hover:bg-brand-500/10 dark:hover:text-brand-300 md:dark:hover:bg-transparent;
}

/* Haciendo que el dropdown flote por encima de otros elementos */
/* Para asegurarse de que el submenú no mueva otros elementos */
.dropdown-menu {
    @apply mt-2 w-44 divide-y divide-slate-100 rounded-lg border border-border-light bg-surface shadow-sm dark:divide-border-dark dark:border-border-dark dark:bg-surface-dark;
    position: absolute;
    top: 100%;
    left: 0;
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
    @apply block px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 dark:text-zinc-200 dark:hover:bg-brand-500/10 dark:hover:text-white;
}

.mobile-child-link {
    @apply block rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-brand-50 hover:text-brand-700 dark:text-zinc-200 dark:hover:bg-brand-500/10 dark:hover:text-white;
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
