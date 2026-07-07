<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    href: String,
    as: String,
})
</script>

<template>
    <div>
        <button v-if="as == 'button'" type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5
                   text-slate-700 dark:text-zinc-200
                   hover:bg-slate-100 dark:hover:bg-zinc-800
                   focus:outline-none focus:bg-slate-100 dark:focus:bg-zinc-800
                   transition duration-150 ease-in-out">
            <slot />
        </button>

        <a v-else-if="as == 'a'" :href="href" class="block px-4 py-2 text-sm leading-5
                   text-slate-700 dark:text-zinc-200
                   hover:bg-slate-100 dark:hover:bg-zinc-800
                   focus:outline-none focus:bg-slate-100 dark:focus:bg-zinc-800
                   transition duration-150 ease-in-out">
            <slot />
        </a>

        <Link v-else :href="href" class="block px-4 py-2 text-sm leading-5
                   text-slate-700 dark:text-zinc-200
                   hover:bg-slate-100 dark:hover:bg-zinc-800
                   focus:outline-none focus:bg-slate-100 dark:focus:bg-zinc-800
                   transition duration-150 ease-in-out">
        <slot />
        </Link>
    </div>
</template>
