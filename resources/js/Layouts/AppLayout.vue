<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
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
const sidebarOpen = ref(false);
const userDropdownOpen = ref(false);

const iconMap = {
    Insights: "fa-solid fa-chart-line",
    Core: "fa-solid fa-layer-group",
    Sync: "fa-solid fa-calendar-check",
    Rooms: "fa-solid fa-door-open",
    Admin: "fa-solid fa-shield-halved",
    Teaching: "fa-solid fa-chalkboard-user",
    "Student Flow": "fa-solid fa-route",
    Account: "fa-solid fa-user-gear",
    Dashboard: "fa-solid fa-gauge-high",
    Reports: "fa-solid fa-file-lines",
    "Audit Logs": "fa-solid fa-clock-rotate-left",
    Programs: "fa-solid fa-diagram-project",
    Subjects: "fa-solid fa-book-open",
    Professors: "fa-solid fa-chalkboard-user",
    Students: "fa-solid fa-user-graduate",
    "Class Groups": "fa-solid fa-users-rectangle",
    "Enrollment Management": "fa-solid fa-user-plus",
    "Academic Periods": "fa-solid fa-calendar-days",
    Buildings: "fa-solid fa-building",
    Classrooms: "fa-solid fa-door-closed",
    "Identity & Access": "fa-solid fa-users-gear",
    "My Subjects": "fa-solid fa-book",
    "My Schedule": "fa-solid fa-calendar-week",
    "Group Enrollments": "fa-solid fa-list-check",
    "Subject Enrollment": "fa-solid fa-clipboard-list",
    Profile: "fa-solid fa-id-card",
};

const navGroups = computed(() => page.props.navigation?.main ?? []);

const isRouteActive = (routeName) => {
    if (!routeName) return false;

    try {
        return route().current(routeName);
    } catch {
        return false;
    }
};

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
    <div class="min-h-screen bg-slate-50 text-ink dark:bg-dark-bg dark:text-zinc-100">
        <Head :title="$props.title" />

        <transition name="fade">
            <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" />
        </transition>

        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-border-light bg-surface text-ink transition-transform duration-200 dark:border-border-dark dark:bg-dark-bg dark:text-white lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <div class="flex h-20 items-center gap-3 border-b border-border-light px-5 dark:border-border-dark">
                <ApplicationCompactMark class="h-11 w-11 shrink-0" />
                <div class="min-w-0">
                    <Link href="/" class="block text-lg font-bold leading-none tracking-tight text-ink dark:text-white" @click="sidebarOpen = false">
                        TARRAYA
                    </Link>
                    <p class="mt-2 font-mono text-[9px] uppercase leading-3 tracking-wider text-slate-500">
                        The next-generation academic operating system
                    </p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <div v-for="group in navGroups" :key="group.label" class="mb-5">
                    <div class="mb-2 flex items-center gap-2 px-3">
                        <i :class="[iconMap[group.label] || 'fa-solid fa-circle-nodes', 'w-4 text-xs text-slate-500']" />
                        <p class="font-mono text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                            {{ group.label }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <Link
                            v-for="child in group.children || [group]"
                            :key="child.route ?? child.label"
                            :href="child.route ? route(child.route) : '#'"
                            :class="[
                                'group flex items-center gap-3 rounded-lg border-l-2 px-3 py-2.5 text-sm font-medium transition',
                                isRouteActive(child.route)
                                    ? 'border-brand-600 bg-brand-600/10 text-brand-700 dark:text-white'
                                    : 'border-transparent text-slate-500 hover:border-brand-600/60 hover:bg-brand-600/10 hover:text-brand-700 dark:text-slate-400 dark:hover:text-white',
                            ]"
                            @click="sidebarOpen = false"
                        >
                            <i :class="[iconMap[child.label] || iconMap[group.label] || 'fa-regular fa-circle', 'w-4 text-sm']" />
                            <span>{{ child.label }}</span>
                        </Link>
                    </div>
                </div>
            </nav>
        </aside>

        <div class="lg:pl-64">
            <header class="sticky top-0 z-30 border-b border-border-light bg-surface/95 backdrop-blur dark:border-border-dark dark:bg-dark-bg/95">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-border-light text-slate-500 transition hover:bg-brand-50 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:border-border-dark dark:text-zinc-400 dark:hover:bg-brand-500/10 lg:hidden"
                        @click="sidebarOpen = true"
                    >
                        <i class="fa-solid fa-bars" />
                        <span class="sr-only">Open navigation</span>
                    </button>

                    <div class="hidden min-w-0 flex-1 md:block">
                        <label class="relative block max-w-md">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                <i class="fa-solid fa-magnifying-glass text-xs" />
                            </span>
                            <input
                                type="search"
                                placeholder="Search anything..."
                                class="w-full rounded-lg border border-border-light bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-ink outline-none transition placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-100 dark:placeholder:text-zinc-500"
                            />
                        </label>
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <button
                            type="button"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-border-light text-slate-500 transition hover:bg-brand-50 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:border-border-dark dark:text-zinc-400 dark:hover:bg-brand-500/10"
                            aria-label="Notifications"
                        >
                            <i class="fa-regular fa-bell" />
                            <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-accent ring-2 ring-surface dark:ring-dark-bg" />
                        </button>

                        <button
                            @click="toggleDarkMode"
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-border-light text-slate-500 transition hover:bg-brand-50 hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:border-border-dark dark:text-zinc-400 dark:hover:bg-brand-500/10"
                            :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                        >
                            <i :class="darkMode ? 'fas fa-moon' : 'fas fa-sun'" />
                        </button>

                        <button
                            type="button"
                            class="hidden h-10 items-center gap-2 rounded-lg border border-border-light bg-surface px-3 font-mono text-xs font-medium text-slate-600 transition hover:border-brand-600 hover:text-brand-600 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-300 dark:hover:border-brand-500 dark:hover:text-brand-300 sm:inline-flex"
                        >
                            <i class="fa-solid fa-calendar-days text-brand-600 dark:text-brand-300" />
                            This Semester
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400" />
                        </button>

                        <div class="relative">
                            <button
                                id="avatar-button"
                                @click="userDropdownOpen = !userDropdownOpen"
                                type="button"
                                class="flex rounded-full border border-border-light bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:border-border-dark dark:bg-surface-dark"
                            >
                                <img class="h-9 w-9 rounded-full object-cover" :src="page.props.auth.user.profile_photo_url" :alt="page.props.auth.user.name" />
                            </button>

                            <transition name="fade">
                                <div v-show="userDropdownOpen" id="user-dropdown"
                                    class="absolute right-0 top-12 z-50 min-w-[13rem] list-none divide-y divide-slate-100 rounded-lg border border-border-light bg-surface text-base dark:divide-border-dark dark:border-border-dark dark:bg-surface-dark">
                                    <div class="px-4 py-3">
                                        <span class="block text-sm font-medium text-ink dark:text-white">{{ page.props.auth.user.name }}</span>
                                        <span class="block truncate text-sm text-slate-500 dark:text-zinc-400">{{ page.props.auth.user.email }}</span>
                                    </div>
                                    <ul class="py-2 text-sm text-slate-700 dark:text-zinc-200">
                                        <li>
                                            <Link :href="route('profile.show')" class="block px-4 py-2 hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-white">
                                                Profile
                                            </Link>
                                        </li>
                                        <li>
                                            <button @click="logout" class="block w-full px-4 py-2 text-left hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-white">
                                                Sign out
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </header>

            <section v-if="$slots.header" class="border-b border-border-light bg-surface dark:border-border-dark dark:bg-surface-dark">
                <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </section>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
