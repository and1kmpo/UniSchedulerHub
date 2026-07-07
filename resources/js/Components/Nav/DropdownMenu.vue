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
                'border-brand bg-brand text-white': active,
                'border-transparent text-slate-600 hover:border-border-light hover:bg-slate-100 hover:text-ink dark:text-zinc-300 dark:hover:border-border-dark dark:hover:bg-zinc-900 dark:hover:text-white': !active,
            }" class="inline-flex items-center rounded-lg border px-3 py-2 text-sm font-medium leading-4 transition duration-150">
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
