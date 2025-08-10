<template>
    <AppLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Buildings</h1>
                <Link :href="route('buildings.create')"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                <i class="fa-solid fa-plus"></i>
                New Building
                </Link>
            </div>
        </template>

        <div class="mt-6 px-4 sm:px-6 lg:px-8">
            <div v-if="buildings.length === 0"
                class="rounded-lg shadow ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900 p-6 text-center text-gray-500 dark:text-gray-300">
                No buildings have been registered yet.
            </div>
            <div v-else
                class="rounded-lg shadow ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-900 overflow-x-auto mb-8">
                <table class="min-w-full text-sm text-center">
                    <thead>
                        <tr
                            class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 uppercase text-xs tracking-wider">
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="building in buildings.data" :key="building.id"
                            class="even:bg-gray-50 dark:even:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">
                                {{ building.name }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-300">
                                {{ building.code }}
                            </td>
                            <td class="px-4 py-3">
                                <EditButton :href="route('buildings.edit', { building: building.id })" class="mr-2" />
                                <DeleteButton :onClick="() => deleteBuilding(building.id)" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="buildings.links" />
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { useAlert } from '@/Components/Composables/useAlert'
import EditButton from '@/Components/EditButton.vue'
import DeleteButton from '@/Components/DeleteButton.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    buildings: Object
})

console.log(props.buildings);

const { toastSuccess, toastError, confirm } = useAlert()


const deleteBuilding = async (id) => {
    const confirmed = await confirm('Are you sure you want to delete this building?', 'Confirm Deletion')

    if (!confirmed) return

    try {
        await axios.post(route('buildings.destroy', id), {
            _method: 'DELETE'
        })
        toastSuccess('Building deleted successfully')
        location.reload()
    } catch (error) {
        console.error(error)
        toastError('Could not delete the building')
    }
}
</script>
