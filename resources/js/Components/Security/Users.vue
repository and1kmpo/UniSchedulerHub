<template>
    <h1 class="text-lg font-semibold text-ink dark:text-white">Users</h1>
    <div class="m-4 overflow-x-auto rounded-lg border border-border-light bg-surface shadow-sm dark:border-border-dark dark:bg-surface-dark">
        <table class="w-full divide-y divide-border-light text-center text-slate-500 dark:divide-border-dark dark:text-zinc-400">
            <thead
                class="bg-slate-50 text-xs uppercase text-slate-700 dark:bg-zinc-950 dark:text-zinc-400 sm:text-sm md:text-sm">
                <tr>
                    <th scope="col" class="px-1 sm:px-1 md:px-2 py-2">
                        User
                    </th>
                    <th scope="col" class="px-1 sm:px-1 md:px-2 py-2">
                        Email
                    </th>
                    <th scope="col" class="px-1 sm:px-1 md:px-2 py-2">
                        Rol
                    </th>
                    <th scope="col" class="px-1 sm:px-1 md:px-2 py-2">
                        Update role
                    </th>
                </tr>
            </thead>
            <tbody class="text-xs sm:text-sm md:text-sm">
                <tr class="bg-surface hover:bg-slate-50 dark:bg-surface-dark dark:hover:bg-zinc-900">
                    <th scope="row" class="px-1 py-2 font-medium text-ink dark:text-white sm:px-1 md:px-2">
                        Julio Rodriguez
                    </th>
                    <td class="px-1 sm:px-1 md:px-2 py-2">
                        julirod@gmail.com
                    </td>
                    <td class="px-1 sm:px-1 md:px-2 py-2">
                        <span
                            class="me-1 rounded-full bg-danger/10 px-1.5 py-0.5 font-medium text-danger dark:text-red-300">Admin</span>
                    </td>
                    <td class="px-1 sm:px-1 md:px-2 py-2">
                        <a href="#"
                            class="text-xxs rounded-lg bg-brand p-1 px-2.5 text-white transition hover:bg-brand-dark sm:text-xs md:text-xs">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
