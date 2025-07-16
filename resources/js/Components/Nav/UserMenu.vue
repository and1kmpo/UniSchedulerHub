<!-- resources/js/Components/Navigation/UserMenu.vue -->
<script setup>
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import { usePage, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const page = usePage()
const showing = ref(false)

const logout = () => {
    router.post(route('logout'))
}

const managesProfilePhotos = computed(() => page.props.jetstream.managesProfilePhotos)
const hasApiFeatures = computed(() => page.props.jetstream.hasApiFeatures)
const user = computed(() => page.props.auth.user)
</script>

<template>
    <Dropdown v-model:showing="showing" align="right" width="48">
        <template #trigger>
            <button v-if="managesProfilePhotos"
                class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                <img class="h-8 w-8 rounded-full object-cover" :src="user.profile_photo_url" :alt="user.name" />
            </button>

            <span v-else class="inline-flex rounded-md">
                <button type="button" :class="[
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md transition ease-in-out duration-150',
                    showing
                        ? 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-white'
                        : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-200 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-600'
                ]">
                    {{ user.name }}
                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
            </span>
        </template>

        <template #content>
            <!-- Cuenta -->
            <div class="block px-4 py-2 text-xs text-gray-400">Manage Account</div>

            <DropdownLink :href="route('profile.show')">Profile</DropdownLink>
            <DropdownLink v-if="hasApiFeatures" :href="route('api-tokens.index')">API Tokens</DropdownLink>

            <div class="border-t border-gray-200 dark:border-gray-600" />

            <!-- Logout -->
            <form @submit.prevent="logout">
                <DropdownLink as="button">Log Out</DropdownLink>
            </form>
        </template>
    </Dropdown>
</template>
