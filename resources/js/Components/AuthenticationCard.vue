<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50 dark:bg-[#09090B]">
        <div class="px-4">
            <slot name="logo" />
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-5 bg-white overflow-hidden border border-slate-200 shadow-sm sm:rounded-lg dark:border-zinc-800 dark:bg-zinc-950"
        >
            <slot />
        </div>
    </div>
</template>
