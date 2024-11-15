<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>

    <Head title="Welcome" />

    <div
        class="relative min-h-screen flex flex-col justify-center items-center bg-gray-100 dark:bg-gray-900 selection:bg-red-500 selection:text-white">

        <!-- Bloque de logo y nombre de la plataforma -->
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-lg flex flex-col items-center text-center">
            <div class="flex items-center mb-4">
                <!-- Logo más grande y centrado -->
                <div
                    class="w-20 h-20 bg-blue-500 text-white font-bold rounded-full flex items-center justify-center shadow-md text-4xl">
                    Uni
                </div>
                <!-- Nombre de la plataforma alineado a la derecha -->
                <span class="text-5xl font-bold text-gray-800 tracking-tight">
                    Scheduler<span class="text-blue-500">Hub</span>
                </span>
            </div>

            <!-- Descripción centrada debajo del logo -->
            <p class="text-center text-sm text-gray-600 mt-4 max-w-sm">
                Organize and manage your university courses effortlessly.
            </p>

            <!-- Botones de login y register centrados dentro del contenedor -->
            <div class="mt-6 flex space-x-4">
                <Link v-if="!$page.props.auth.user" :href="route('login')"
                    class="bg-indigo-600 text-white px-6 py-3 rounded-full hover:bg-indigo-500 transition">
                Log in
                </Link>
                <Link v-if="canRegister" :href="route('register')"
                    class="bg-gray-600 text-white px-6 py-3 rounded-full hover:bg-gray-500 transition">
                Register
                </Link>
            </div>
        </div>
    </div>
</template>

<style>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}

@media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
}
</style>
