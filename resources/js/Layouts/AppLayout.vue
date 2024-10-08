<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const showingProfessorsStudentsDropdown = ref(false);
const toggleProfessorsMenu = ref(false);
const toggleStudentsMenu = ref(false);

const switchToTeam = (team) => {
    router.put(
        route("current-team.update"),
        {
            team_id: team.id,
        },
        {
            preserveState: false,
        }
    );
};

const logout = () => {
    router.post(route("logout"));
};

</script>




<template>
    <div>

        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex" v-if="$page.props.user.permissions.includes(
                                'read programs'
                            )
                                ">
                                <NavLink :href="route('programs.index')" :active="route().current('programs.*')">
                                    Programs
                                </NavLink>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                                v-if="$page.props.user.permissions.includes('read professors')">
                                <!-- Professors Dropdown -->
                                <Dropdown v-model:showing="showingProfessorsStudentsDropdown" class="mt-[18px]">
                                    <template #trigger>
                                        <span :class="{
                                            'bg-indigo-600 text-white': route().current('professors.index') || route().current('professors.assign-subject'),
                                        }"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 hover:text-gray-700 hover:bg-indigo-400 focus:outline-none focus:bg-gray-100 focus:text-gray-700 transition ease-in-out duration-150">
                                            Professors
                                            <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </template>
                                    <template #content>
                                        <div class="w-48">
                                            <DropdownLink :href="route('professors.index')">
                                                Index
                                            </DropdownLink>
                                            <DropdownLink :href="route('professors.assignSubjectForm')">
                                                Assign Subject
                                            </DropdownLink>


                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                                v-if="$page.props.user.permissions.includes('read students')">
                                <!-- Professors Dropdown -->
                                <Dropdown v-model:showing="showingProfessorsStudentsDropdown" class="mt-[18px]">
                                    <template #trigger>
                                        <span :class="{
                                            'bg-indigo-600 text-white': route().current('students.index') || route().current('students.assign-subject'),
                                        }"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 hover:text-gray-700 hover:bg-indigo-400 focus:outline-none focus:bg-gray-100 focus:text-gray-700 transition ease-in-out duration-150">
                                            Students
                                            <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </template>
                                    <template #content>
                                        <div class="w-48">
                                            <!-- Ajusta el ancho según sea necesario -->
                                            <DropdownLink :href="route('students.index')">
                                                Index
                                            </DropdownLink>
                                            <DropdownLink :href="route('students.assignSubjectForm')">
                                                Assign Subject
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>


                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex" v-if="$page.props.user.permissions.includes(
                                'read subjects'
                            )
                                ">
                                <NavLink :href="route('subjects.index')" :active="route().current('subjects.*')">
                                    Subjects
                                </NavLink>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex" v-if="$page.props.user.permissions.includes(
                                'view admin'
                            )
                                ">
                                <NavLink :href="route('admin.index')" :active="route().current('admin.*')">
                                    Admin
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{
                                                    $page.props.auth.user
                                                        .current_team.name
                                                }}

                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Manage Team
                                            </div>

                                            <!-- Team Settings -->
                                            <DropdownLink :href="route(
                                                        'teams.show',
                                                        $page.props.auth.user
                                                            .current_team
                                                    )
                                                    ">
                                                Team Settings
                                            </DropdownLink>

                                            <DropdownLink v-if="$page.props.jetstream
                                                .canCreateTeams
                                                " :href="route('teams.create')">
                                                Create New Team
                                            </DropdownLink>

                                            <!-- Team Switcher -->
                                            <template v-if="$page.props.auth.user
                                                .all_teams.length > 1
                                                ">
                                                <div class="border-t border-gray-200" />

                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Switch Teams
                                                </div>

                                                <template v-for="team in $page.props
                                                            .auth.user.all_teams" :key="team.id">
                                                    <form @submit.prevent="
                                                        switchToTeam(team)
                                                        ">
                                                        <DropdownLink as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id ==
                                                                    $page
                                                                        .props
                                                                        .auth
                                                                        .user
                                                                        .current_team_id
                                                                    " class="me-2 h-5 w-5 text-green-400"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.5"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>

                                                                <div>
                                                                    {{
                                                                        team.name
                                                                    }}
                                                                </div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream
                                            .managesProfilePhotos
                                            "
                                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.auth.user
                                                .profile_photo_url
                                                " :alt="$page.props.auth.user.name
        " />
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream
                                            .hasApiFeatures
                                            " :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                                @click="
                                    showingNavigationDropdown =
                                    !showingNavigationDropdown
                                    ">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex':
                                            !showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex':
                                            showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }" class="sm:hidden flex flex-col"> <!-- Contenedor flex vertical -->
                    <div class="pt-2 pb-3 space-y-1">
                        <!-- Dashboard -->
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <div class="pt-2 pb-3 space-y-1" v-if="$page.props.user.permissions.includes('read programs')">
                        <!-- Programs -->
                        <ResponsiveNavLink :href="route('programs.index')" :active="route().current('programs')">
                            Programs
                        </ResponsiveNavLink>
                    </div>

                    <div class="pt-2 pb-3 space-y-1 ml-4" v-if="$page.props.user.permissions.includes('read professors')">
                        <!-- Professors -->
                        <div @click="toggleProfessorsMenu = !toggleProfessorsMenu" class="cursor-pointer">
                            <a>
                                Professors <i class="fa-solid fa-caret-down"></i>
                            </a>
                            <div v-show="toggleProfessorsMenu">
                                <!-- Professors Index -->
                                <ResponsiveNavLink :href="route('professors.index')"
                                    :active="route().current('professors.index')">
                                    Index
                                </ResponsiveNavLink>
                                <!-- Assign Subjects to Professors -->
                                <ResponsiveNavLink :href="route('professors.assignSubjectForm')"
                                    :active="route().current('professors.assignSubjectForm')">
                                    Assign Subjects
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 pb-3 space-y-1 ml-4" v-if="$page.props.user.permissions.includes('read students')">
                        <!-- Students -->
                        <div @click="toggleStudentsMenu = !toggleStudentsMenu" class="cursor-pointer">
                            <a>
                                Students <i class="fa-solid fa-caret-down"></i>
                            </a>
                            <div v-show="toggleStudentsMenu">
                                <!-- Students Index -->
                                <ResponsiveNavLink :href="route('students.index')"
                                    :active="route().current('students.index')">
                                    Index
                                </ResponsiveNavLink>
                                <!-- Assign Subjects to Students -->
                                <ResponsiveNavLink :href="route('students.assignSubjectForm')"
                                    :active="route().current('students.assignSubjectForm')">
                                    Assign Subjects
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 pb-3 space-y-1" v-if="$page.props.user.permissions.includes('read subjects')">
                        <!-- Subjects -->
                        <ResponsiveNavLink :href="route('subjects.index')" :active="route().current('subjects')">
                            Subjects
                        </ResponsiveNavLink>
                    </div>

                    <div class="pt-2 pb-3 space-y-1" v-if="$page.props.user.permissions.includes('view admin')">
                        <!-- Admin -->
                        <ResponsiveNavLink :href="route('admin.index')" :active="route().current('admin')">
                            Admin
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <!-- User Avatar -->
                                <img class="h-10 w-10 rounded-full object-cover"
                                    :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" />
                            </div>
                            <div>
                                <!-- User Name -->
                                <div class="font-medium text-base text-gray-800">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <!-- User Email -->
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1 flex flex-col"> <!-- Contenedor flex vertical -->
                            <!-- Profile -->
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Profile
                            </ResponsiveNavLink>

                            <!-- API Tokens -->
                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')"
                                :active="route().current('api-tokens.index')">
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Log Out -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200"></div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)"
                                    :active="route().current('teams.show')">
                                    Team Settings
                                </ResponsiveNavLink>

                                <!-- Create New Team -->
                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')"
                                    :active="route().current('teams.create')">
                                    Create New Team
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200"></div>
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Switch Teams
                                    </div>
                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                                        class="me-2 h-5 w-5 text-green-400"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>

            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
