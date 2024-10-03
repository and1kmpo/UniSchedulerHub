<script setup>
import { Link } from '@inertiajs/inertia-vue3';
</script>

<template>
    <h1>Users</h1>
    <div class="overflow-x-auto shadow-md sm:rounded-lg m-4">
        <table class="w-full text-gray-500 dark:text-gray-400 text-center divide-y divide-gray-200 dark:divide-gray-700">
            <thead
                class="text-xs sm:text-sm md:text-sm text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
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
                <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <th scope="row" class="px-1 sm:px-1 md:px-2 py-2 font-medium text-gray-900 dark:text-white">
                        Julio Rodriguez
                    </th>
                    <td class="px-1 sm:px-1 md:px-2 py-2">
                        julirod@gmail.com
                    </td>
                    <td class="px-1 sm:px-1 md:px-2 py-2">
                        <span
                            class="bg-red-100 text-red-800 font-medium me-1 px-1.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Admin</span>
                    </td>
                    <td class="px-1 sm:px-1 md:px-2 py-2">
                        <a href="#"
                            class="text-xxs sm:text-xs md:text-xs bg-blue-700 hover:bg-blue-400 hover:text-black rounded p-1 px-2.5 text-white">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>