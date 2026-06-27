<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50 dark:bg-dark-bg">
        <div class="px-4">
            <slot name="logo" />
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-5 bg-surface overflow-hidden border border-border-light shadow-sm sm:rounded-lg dark:border-border-dark dark:bg-surface-dark"
        >
            <slot />
        </div>
    </div>
</template>
