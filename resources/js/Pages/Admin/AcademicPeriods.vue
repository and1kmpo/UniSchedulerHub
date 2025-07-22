<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()
const periods = page.props.periods?.data ?? []
const pagination = page.props.periods?.meta ?? {}
const links = page.props.periods?.links ?? []

const form = ref({
    name: '',
    start_date: '',
    end_date: '',
    is_active: false,
})

const submit = () => {
    router.post('/academic-periods', form.value, {
        onSuccess: () => {
            form.value = {
                name: '',
                start_date: '',
                end_date: '',
                is_active: false,
            }
            reload()
        }
    })
}

const activate = (id) => {
    router.patch(`/academic-periods/${id}/activate`, {}, {
        onSuccess: reload
    })
}

const destroy = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar este período académico?')) {
        router.delete(`/academic-periods/${id}`, {
            onSuccess: reload
        })
    }
}

const reload = () => {
    router.reload({
        only: ['periods'],
        preserveState: true,
    })
}

const goToPage = (url) => {
    if (url) {
        router.visit(url, {
            only: ['periods'],
            preserveState: true,
        })
    }
}

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
</script>

<template>
    <div class="max-w-5xl mx-auto space-y-10 py-10">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Gestión de Períodos Académicos</h1>

        <!-- Formulario -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Crear Período Académico</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <input v-model="form.name" type="text" placeholder="Ej: 2025-I"
                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Inicio</label>
                    <input v-model="form.start_date" type="date"
                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fin</label>
                    <input v-model="form.end_date" type="date"
                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-white" />
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="form.is_active" type="checkbox" id="is_active"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Marcar como activo</label>
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded">
                    Guardar
                </button>
            </form>
        </div>

        <!-- Tabla -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Listado de períodos</h2>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Nombre</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Inicio</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Fin</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Estado</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="period in periods" :key="period.id">
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ period.name }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ formatDate(period.start_date) }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ formatDate(period.end_date) }}</td>
                        <td class="px-4 py-2">
                            <span v-if="period.is_active" class="text-green-600 font-bold">Activo</span>
                            <span v-else class="text-gray-500">Inactivo</span>
                        </td>
                        <td class="px-4 py-2 space-x-2 text-right">
                            <button v-if="!period.is_active" @click="activate(period.id)"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-3 py-1 rounded">
                                Activar
                            </button>
                            <button @click="destroy(period.id)"
                                class="bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1 rounded">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Paginación -->
            <div class="mt-4 flex justify-end space-x-2">
                <button v-for="link in links" :key="link.label" v-html="link.label" :disabled="!link.url"
                    @click="goToPage(link.url)" :class="[
                        'px-3 py-1 rounded',
                        link.active
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600',
                        !link.url && 'opacity-50 cursor-not-allowed'
                    ]" />
            </div>
        </div>
    </div>
</template>
