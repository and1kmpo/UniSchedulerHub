<script setup>

import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/Components/Composables/UseAlert'
import dayjs from 'dayjs'
import 'dayjs/locale/en'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'

const { success, error, confirm, toastSuccess, toastError } = useAlert()
const page = usePage()
const periods = computed(() => usePage().props.periods?.data ?? [])
const pagination = page.props.periods?.meta ?? {}
const links = page.props.periods?.links ?? []
const editingId = ref(null)

const form = ref({
    name: '',
    start_date: '',
    end_date: '',
    is_active: false,
})

const submit = async () => {
    if (editingId.value) {
        try {
            const response = await axios.put(`/academic-periods/${editingId.value}`, form.value, {
                headers: {
                    'X-Inertia': false // ❗️Esto evita que Laravel espere una respuesta Inertia
                }
            })

            const updated = response.data?.period
            if (updated) {
                const index = periods.value.findIndex(p => p.id === updated.id)
                if (index !== -1) {
                    periods.value[index] = updated
                }
                toastSuccess('Period successfully updated.')
            } else {
                toastError('Updated but no period data returned.')
            }

            resetForm()
        } catch (error) {
            toastError('The period could not be updated.')
            console.error(error)
        }

    } else {
        // Si no estás editando, puedes seguir usando Inertia o también cambiar a axios
        try {
            const response = await axios.post('/academic-periods', form.value, {
                headers: {
                    'X-Inertia': false
                }
            })

            const created = response.data?.period
            if (created) {
                periods.value.unshift(created)
                toastSuccess('Period successfully created.')
            } else {
                toastError('Created but no period data returned.')
            }

            resetForm()
        } catch (error) {
            toastError('The period could not be created.')
            console.error(error)
        }
    }
}


const edit = (period) => {
    editingId.value = period.id
    form.value = {
        name: period.name,
        start_date: period.start_date,
        end_date: period.end_date,
        is_active: period.is_active,
    }
}

const resetForm = () => {
    editingId.value = null
    form.value = {
        name: '',
        start_date: '',
        end_date: '',
        is_active: false,
    }
}

const activate = async (id) => {
    const confirmed = await confirm('Do you want to activate this academic period? The others will be deactivated.')
    if (!confirmed) return

    router.patch(`/academic-periods/${id}/activate`, {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toastSuccess('Period successfully activated')
        },
        onError: (errors) => {
            toastError(errors?.message || 'Error activating the period')
        }
    })
}


const destroy = async (id) => {
    const confirmed = await confirm('Are you sure you want to delete this period? This action cannot be undone.')
    if (!confirmed) return

    router.delete(`/academic-periods/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            const index = periods.value.findIndex(p => p.id === id)
            if (index !== -1) periods.value.splice(index, 1)
            toastSuccess('Period deleted.')
        },
        onError: ({ response }) => {
            toastError(response?.data?.error || 'The period could not be deleted.')
        }
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
    return dayjs(dateStr).format('D / MMM / YYYY');
}

</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Academic Periods
            </h1>
        </template>
        <div class="max-w-5xl mx-auto">
            <!-- Formulario -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
                <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">
                    {{ editingId ? 'Edit Academic Period' : 'Create Academic Period' }}
                </h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                        <input v-model="form.name" type="text" placeholder="E.g. 2025-I"
                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                        <input v-model="form.start_date" type="date"
                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                        <input v-model="form.end_date" type="date"
                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-white" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model="form.is_active" type="checkbox" id="is_active"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Mark as active</label>
                    </div>
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded">
                        Save
                    </button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
                <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">List of Periods</h2>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-center">
                        <tr>
                            <th class="px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Name</th>
                            <th class="px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Start Date</th>
                            <th class="px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                End Date</th>
                            <th class="px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Status</th>
                            <th
                                class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700 text-center">
                        <tr v-for="period in periods" :key="period.id">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ period.name }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ formatDate(period.start_date) }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ formatDate(period.end_date) }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" :checked="period.is_active"
                                        @change="activate(period.id)" />
                                    <div
                                        class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600">
                                    </div>
                                </label>
                            </td>

                            <td class="px-6 py-2 space-x-2 text-right">
                                <button @click="edit(period)"
                                    class=" text-indigo-600 hover:text-indigo-800 dark:text-blue-400 dark:hover:text-indigo-200 text-sm rounded">
                                    <i class="fa-solid fa-pen-to-square"></i>

                                </button>
                                <button @click="destroy(period.id)" :disabled="period.is_active"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 text-sm rounded disabled:cursor-not-allowed disabled:opacity-50">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
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
    </AppLayout>
</template>
