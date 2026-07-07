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
                class="flex rounded-full border-2 border-transparent text-sm transition focus:border-brand focus:outline-none">
                <img class="h-8 w-8 rounded-full object-cover" :src="user.profile_photo_url" :alt="user.name" />
            </button>

            <span v-else class="inline-flex rounded-md">
                <button type="button" :class="[
                    'inline-flex items-center rounded-lg border px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out',
                    showing
                        ? 'border-brand bg-brand/10 text-brand dark:border-brand dark:bg-brand/15 dark:text-brand'
                        : 'border-border-light bg-surface text-slate-600 hover:bg-slate-100 hover:text-ink dark:border-border-dark dark:bg-surface-dark dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white'
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
            <div class="block px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-zinc-500">Manage Account</div>

            <DropdownLink :href="route('profile.show')">Profile</DropdownLink>
            <DropdownLink v-if="hasApiFeatures" :href="route('api-tokens.index')">API Tokens</DropdownLink>

            <div class="border-t border-border-light dark:border-border-dark" />

            <!-- Logout -->
            <form @submit.prevent="logout">
                <DropdownLink as="button">Log Out</DropdownLink>
            </form>
        </template>
    </Dropdown>
</template>
