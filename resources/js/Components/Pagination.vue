<template>
    <div v-if="links.length > 3" class="mt-4 flex flex-wrap justify-center gap-1">
        <template v-for="(link, key) in links" :key="key">
            <div v-if="link.url === null" class="rounded-lg px-3 py-2 text-sm text-slate-400 dark:text-zinc-500"
                v-html="link.label" />
            <Link v-else :href="link.url" class="rounded-lg px-3 py-2 text-sm transition hover:bg-slate-100 dark:hover:bg-zinc-800"
                :class="{
                    'bg-brand text-white': link.active,
                    'text-slate-700 dark:text-zinc-300': !link.active
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
