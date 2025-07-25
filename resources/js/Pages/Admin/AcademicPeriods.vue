<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useAlert } from '@/Components/Composables/useAlert'
import AppLayout from "@/Layouts/AppLayout.vue";

const { toastSuccess, toastError, confirm, error } = useAlert()

const page = usePage()

const periods = ref([...page.props.periods?.data ?? []])
const links = page.props.periods?.links ?? []

const form = ref({
    name: '',
    start_date: '',
    end_date: '',
    enrollment_deadline: '',
    unenrollment_deadline: '',
    is_active: false,
})

const editingId = ref(null)

const submit = async () => {
    // Verificar si ya existe un periodo con el mismo nombre
    const existingPeriod = periods.value.find(p => p.name === form.value.name && p.id !== editingId.value);
    if (existingPeriod) {
        console.log(`Error: Ya existe un periodo con el nombre "${form.value.name}"`);
        toastError(`Ya existe un periodo con el nombre "${form.value.name}"`);
        return; // No se envía el formulario
    }

    // Validación de solapamiento de fechas
    const startDate = new Date(form.value.start_date).setHours(0, 0, 0, 0);
    const endDate = new Date(form.value.end_date).setHours(0, 0, 0, 0);

    // Verificar si hay algún periodo con solapamiento
    const overlappingPeriod = periods.value.find(p => {
        const pStart = new Date(p.start_date).setHours(0, 0, 0, 0);
        const pEnd = new Date(p.end_date).setHours(0, 0, 0, 0);

        // Comprobar si las fechas se solapan
        return (startDate < pEnd && endDate > pStart && p.id !== editingId.value);
    });

    if (overlappingPeriod) {
        console.log("Error: El periodo de fechas se solapa con otro periodo existente.");
        // Mostrar un mensaje específico con el nombre del periodo con el que se solapa
        toastError(`El periodo de fechas se solapa con el periodo "${overlappingPeriod.name}", que va de ${overlappingPeriod.start_date} a ${overlappingPeriod.end_date}.`);
        return; // No se envía el formulario
    }

    try {
        // Si no hay solapamientos, se puede proceder con la creación o actualización
        let response;

        if (editingId.value) {
            // Enviar petición para actualizar el periodo
            response = await axios.put(`/academic-periods/${editingId.value}`, form.value);
        } else {
            // Enviar petición para crear el periodo
            response = await axios.post('/academic-periods', form.value);
        }

        // Si la respuesta es exitosa
        if (response.data.success) {
            toastSuccess('Period created/updated successfully');
            resetForm();
            reload();
        } else {
            toastError('Failed to create/update period');
        }
    } catch (error) {
        // Manejo de error cuando el backend devuelve un error
        console.log('Error creating/updating period:', error.response?.data || error);

        // Si el error es por solapamiento de fechas, mostrar el mensaje adecuado
        if (error.response && error.response.data.error) {
            toastError(error.response.data.error);  // Usar el mensaje de error retornado por el servidor
        } else if (error.response && error.response.data.errors) {
            // Si el backend retorna errores de validación (por ejemplo, fechas solapadas)
            const overlapMessage = error.response.data.errors.start_date || 'Failed to create/update period';
            toastError(overlapMessage);
        } else {
            toastError('Failed to create/update period');
        }
    }
};

const toDateInputFormat = (date) => {
    return date ? new Date(date).toISOString().slice(0, 10) : ''
}

const edit = (period) => {
    form.value = {
        name: period.name,
        start_date: toDateInputFormat(period.start_date),
        end_date: toDateInputFormat(period.end_date),
        is_active: period.is_active,
        enrollment_deadline: toDateInputFormat(period.enrollment_deadline),
        unenrollment_deadline: toDateInputFormat(period.unenrollment_deadline),
    }
    editingId.value = period.id
}

const destroy = async (id) => {
    const confirmed = await confirm('Esto eliminará permanentemente el periodo académico.', '¿Estás seguro?')
    if (!confirmed) return

    router.delete(`/academic-periods/${id}`, {
        onSuccess: () => {
            toastSuccess('Periodo eliminado correctamente')
            reload()
        },
        onError: () => {
            toastError('Error al eliminar el periodo')
        },
    })
}

const activate = (id) => {
    const url = `/academic-periods/${id}/activate`

    router.post(url, { _method: 'patch' }, {
        preserveScroll: true,
        onSuccess: () => {
            toastSuccess('Periodo activado correctamente')

            // ✅ Reactividad inmediata
            periods.value = periods.value.map(p => ({
                ...p,
                is_active: p.id === id
            }))

            // 🔁 Sincroniza con backend después de un pequeño delay
            setTimeout(reload, 500)
        },
        onError: () => {
            error('No se pudo activar el periodo', 'Error de activación')
        }
    })
}

const resetForm = () => {
    form.value = {
        name: '',
        start_date: '',
        end_date: '',
        enrollment_deadline: '',
        unenrollment_deadline: '',
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
        timeZone: 'UTC'
    })
}

// Computed property para validar si la fecha final es mayor que la de inicio
const isStartDateBeforeEnd = computed(() => {
    if (!form.value.start_date || !form.value.end_date) return true
    return new Date(form.value.start_date) < new Date(form.value.end_date)
})
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Academic Period Management
            </h1>
        </template>
        <div class="max-w-5xl mx-auto py-10 space-y-10">
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
                        <p v-if="!isStartDateBeforeEnd" class="text-red-600 text-xs mt-1">The end date must be after the
                            start
                            date.</p>
                    </div>
                    <div>
                        <label for="unenrollment_deadline"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Enrollment Deadline
                        </label>
                        <input type="date" id="unenrollment_deadline" v-model="form.enrollment_deadline"
                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-gray-900 dark:text-white" />

                    </div>
                    <div>
                        <label for="unenrollment_deadline"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Unenrollment Deadline
                        </label>
                        <input type="date" id="unenrollment_deadline" v-model="form.unenrollment_deadline"
                            class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-gray-900 dark:text-white" />

                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model="form.is_active" type="checkbox" id="is_active"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Mark as active</label>
                    </div>
                    <div class="flex gap-4">
                        <button type="submit" :disabled="!isStartDateBeforeEnd"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded disabled:opacity-50">
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
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 text-center">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Name</th>
                            <th
                                class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Start</th>
                            <th
                                class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                End</th>
                            <th
                                class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Enrollment Deadline</th>
                            <th
                                class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Unenrollment Deadline</th>
                            <th
                                class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Status</th>
                            <th
                                class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="period in periods" :key="period.id">
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ period.name }}</td>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ formatDate(period.start_date) }}
                            </td>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ formatDate(period.end_date) }}
                            </td>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{
                                formatDate(period.enrollment_deadline)
                                }}
                            </td>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{
                                formatDate(period.unenrollment_deadline)
                                }}
                            </td>
                            <td class="px-4 py-2">
                                <!-- Toggle Switch -->
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" :checked="period.is_active"
                                        :disabled="period.is_active" @change="() => activate(period.id)" />
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 
              peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 
              peer-checked:after:translate-x-full 
              rtl:peer-checked:after:-translate-x-full 
              peer-checked:after:border-white 
              after:content-[''] after:absolute after:top-0.5 after:start-[2px] 
              after:bg-white after:border-gray-300 after:border after:rounded-full 
              after:h-5 after:w-5 after:transition-transform after:duration-300 
              dark:border-gray-600 peer-checked:bg-indigo-600">
                                    </div>
                                    <span class="ms-2 text-sm font-semibold"
                                        :class="period.is_active ? 'text-green-600' : 'text-gray-500'">
                                        {{ period.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </label>
                            </td>
                            <td class="px-4 py-2 flex gap-4 justify-center text-center">
                                <button @click="edit(period)"
                                    class="dark:text-indigo-400 text-indigo-800 transition ease-in-out hover:-translate-y-1 hover:scale-110 duration-300">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="destroy(period.id)"
                                    class="text-red-600 dark:text-red-400 hover:text-red-800 transition ease-in-out hover:-translate-y-1 hover:scale-110 duration-300">
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
