<script setup>
import { useForm } from '@inertiajs/vue3'
import { useAlert } from '@/Components/Composables/useAlert'
import AppLayout from '@/Layouts/AppLayout.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import axios from 'axios'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    suggestedCode: String
})

const { toastSuccess, toastError, confirm } = useAlert()

// useForm mantiene form.errors y helpers
const form = useForm({
    name: '',
    code: props.suggestedCode || ''
})

const loading = ref(false)

const submit = async () => {
    loading.value = true

    try {
        const payload = {
            name: form.name,
            code: form.code || null
        }

        const res = await axios.post(route('buildings.store'), payload)

        // backend responde 201 con { status:'success', ... }
        if (res.data?.status === 'success') {
            toastSuccess(res.data.message || 'Building created successfully')
            form.reset('name')
            // actualizar código sugerido para la próxima creación
            form.code = res.data.suggestedCode || props.suggestedCode || ''
        } else {
            // fallback genérico
            toastSuccess('Building created')
            form.reset('name')
        }
    } catch (err) {
        const r = err.response

        // 1) Existe un trashed -> pedir confirm para restaurar
        if (r?.status === 409 && r.data?.status === 'trashed_exists') {
            const b = r.data.building
            const ok = await confirm(
                `A building with code "${b.code}" exists but is deleted. Do you want to restore it?`,
                'Restore building'
            )
            if (ok) {
                try {
                    const restoreRes = await axios.post(route('buildings.restore', b.id), {
                        name: form.name || b.name
                    })
                    toastSuccess(restoreRes.data.message || 'Building restored successfully')
                    // ir al index o actualizar la UI
                    router.visit(route('buildings.index'))
                } catch (e2) {
                    toastError(e2.response?.data?.message || 'Could not restore the building')
                }
            } else {
                // usuario canceló: actualizar código sugerido para intentar de nuevo
                // pedimos al backend un nuevo suggestedCode (podrías fetchear /create o usar props)
                form.code = props.suggestedCode || ''
            }

            loading.value = false
            return
        }

        // 2) Validación 422 -> mostrar errores en el formulario (y primer toast)
        if (r?.status === 422 && r.data?.errors) {
            // Inertia useForm se llena normalmente con form.post, aquí asignamos manualmente
            form.errors = r.data.errors
            const first = Object.values(r.data.errors)[0]
            toastError(Array.isArray(first) ? first[0] : first)
            loading.value = false
            return
        }

        // 3) Otro error inesperado
        toastError(r?.data?.message || 'An unexpected error occurred.')
        console.error('Create building error:', err)
    } finally {
        loading.value = false
    }
}

const cancel = () => {
    router.visit(route('buildings.index'))
}
</script>



<template>
    <AppLayout title="New Building">
        <template #header>
            <h1 class="text-2xl font-bold">Create Building</h1>
        </template>

        <div class="max-w-xl mx-auto mt-6">
            <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Building Name
                    </label>
                    <input id="name" v-model="form.name" type="text" class="input w-full"
                        placeholder="Enter building name" required />
                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <!-- Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Building Code <span class="text-gray-400 text-xs">(optional)</span>
                    </label>
                    <input id="code" v-model="form.code" type="text" class="input w-full"
                        placeholder="Enter a custom code or leave blank" />
                    <p class="text-xs text-gray-500 mt-1">
                        If left blank, a code will be generated automatically.
                        Suggested: <span class="font-semibold">{{ suggestedCode }}</span>
                    </p>
                    <div v-if="form.errors.code" class="text-red-600 text-sm mt-1">{{ form.errors.code }}</div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end pt-4">
                    <button type="submit" class="btn-primary mr-2">Create</button>
                    <SecondaryButton @click="cancel">Cancel</SecondaryButton>
                </div>
            </form>
        </div>

    </AppLayout>
</template>

<style scoped>
.input {
    @apply w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}

.btn-primary {
    @apply bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded transition;
}
</style>
