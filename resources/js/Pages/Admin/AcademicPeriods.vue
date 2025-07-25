<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/Components/Composables/useAlert'

const { toastSuccess, toastError, confirm, error } = useAlert()

const page = usePage()

const periods = ref([...page.props.periods?.data ?? []])
const links = page.props.periods?.links ?? []

const form = ref({
    name: '',
    start_date: '',
    end_date: '',
    is_active: false,
})

const editingId = ref(null)

const submit = () => {
    if (editingId.value) {
        router.post(`/academic-periods/${editingId.value}`, {
            ...form.value,
            _method: 'put',
        }, {
            onSuccess: () => {
                toastSuccess('Period updated successfully')
                resetForm()
                reload()
            },
            onError: () => {
                toastError('Failed to update period')
            },
        })
    } else {
        router.post('/academic-periods', form.value, {
            onSuccess: () => {
                toastSuccess('Period created successfully')
                resetForm()
                reload()
            },
            onError: () => {
                toastError('Failed to create period')
            },
        })
    }
}


const edit = (period) => {
    form.value = {
        name: period.name,
        start_date: period.start_date,
        end_date: period.end_date,
        is_active: period.is_active,
    }
    editingId.value = period.id
}

const destroy = async (id) => {
    const confirmed = await confirm('This will permanently delete the academic period.', 'Are you sure?')
    if (!confirmed) return

    router.delete(`/academic-periods/${id}`, {
        onSuccess: () => {
            toastSuccess('Period deleted successfully')
            reload()
        },
        onError: () => {
            toastError('Failed to delete period')
        },
    })
}

const activate = (id) => {
    const url = `/academic-periods/${id}/activate`

    router.post(url, { _method: 'patch' }, {
        preserveScroll: true,
        onSuccess: () => {
            toastSuccess('Period activated successfully')

            // ✅ Reactividad inmediata
            periods.value = periods.value.map(p => ({
                ...p,
                is_active: p.id === id
            }))

            // 🔁 Sincroniza con backend después de un pequeño delay
            setTimeout(reload, 500)
        },
        onError: () => {
            error('Could not activate the period', 'Activation Error')
        }
    })
}

const resetForm = () => {
    form.value = {
        name: '',
        start_date: '',
        end_date: '',
        is_active: false,
    }
    editingId.value = null
}

const reload = () => {
    router.reload({
        only: ['periods'],
        preserveState: true,
        onSuccess: () => {
            // 🔄 Reasignamos el array reactivo
            periods.value = [...page.props.periods?.data ?? []]
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
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
</script>

<template>
    <div class="max-w-5xl mx-auto py-10 space-y-10">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Academic Period Management</h1>
        <div v-if="page.props.flash?.success"
            class="bg-green-100 text-green-800 border border-green-300 px-4 py-3 rounded">
            {{ page.props.flash.success }}
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
                {{ editingId ? 'Edit Period' : 'Create New Period' }}
            </h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input v-model="form.name" type="text" required placeholder="e.g. 2025-I"
                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                    <input v-model="form.start_date" type="date" required
                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                    <input v-model="form.end_date" type="date" required
                        class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-gray-900 dark:text-white" />
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="form.is_active" type="checkbox" id="is_active"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Mark as active</label>
                </div>
                <div class="flex gap-4">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded">
                        {{ editingId ? 'Update' : 'Save' }}
                    </button>
                    <button type="button" @click="resetForm"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded">
                        Clear Form
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Periods List</h2>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Start</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            End</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Status</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="period in periods" :key="period.id">
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ period.name }}</td>
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ formatDate(period.start_date) }}</td>
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ formatDate(period.end_date) }}</td>
                        <td class="px-4 py-2">
                            <span v-if="period.is_active" class="text-green-600 font-semibold">Active</span>
                            <span v-else class="text-gray-500">Inactive</span>
                        </td>
                        <td class="px-4 py-2 space-x-2 text-right">
                            <button @click="edit(period)"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm px-3 py-1 rounded">
                                Edit
                            </button>
                            <button v-if="!period.is_active" @click="activate(period.id)"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-3 py-1 rounded">
                                Activate
                            </button>
                            <button @click="destroy(period.id)"
                                class="bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1 rounded">
                                Delete
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
</template>
