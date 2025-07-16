<!-- resources/js/Components/Navigation/DropdownMenu.vue -->
<script setup>
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'

defineProps({
    label: String,
    items: Array, // Ej: [{ label: 'Index', href: '/professors' }]
    active: Boolean,
});
</script>

<template>
    <Dropdown class="mt-[18px]">
        <template #trigger>
            <span :class="{
                'bg-indigo-600 text-white': active,
            }" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 hover:text-gray-700 hover:bg-indigo-400 transition ease-in-out duration-150">
                {{ label }}
                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </span>
        </template>

        <template #content>
            <div class="w-48">
                <DropdownLink v-for="item in items" :key="item.href" :href="item.href">
                    {{ item.label }}
                </DropdownLink>
            </div>
        </template>
    </Dropdown>
</template>
