<template>
    <div v-if="links.length > 3" class="mt-4 flex flex-wrap justify-center space-x-1">
        <template v-for="(link, key) in links" :key="key">
            <div v-if="link.url === null" class="px-3 py-2 text-sm text-gray-400 dark:text-gray-500"
                v-html="link.label" />
            <Link v-else :href="link.url" class="px-3 py-2 text-sm rounded hover:bg-gray-200 dark:hover:bg-gray-700"
                :class="{
                    'bg-blue-500 text-white': link.active,
                    'text-gray-700 dark:text-gray-300': !link.active
                }" v-html="link.label" />
        </template>
    </div>
</template>

<script setup>
defineProps({
    links: Array
})
import { Link } from '@inertiajs/vue3'
</script>
