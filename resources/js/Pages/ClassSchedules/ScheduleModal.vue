<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch, ref } from 'vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { route } from 'ziggy-js'
import axios from 'axios'
import { useAlert } from '@/Components/Composables/useAlert'

const { custom } = useAlert()

const props = defineProps({
    show: Boolean,
    schedule: Object,
    classGroupId: Number,
    classrooms: { type: Array, default: () => [] },
    mode: { type: String, default: 'create' },
    onClose: Function,
    initial: Object
})

const emit = defineEmits(['saved', 'deleted'])

const days = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"]

// Formulario Inertia
const form = useForm({
    day: props.schedule?.day ?? props.initial?.day ?? '',
    start_time: props.schedule?.start_time ?? props.initial?.start_time ?? '',
    end_time: props.schedule?.end_time ?? props.initial?.end_time ?? '',
    classroom_id: props.schedule?.classroom_id ?? null,
})

watch(() => [props.schedule, props.initial], () => {
    // limpiamos errores cuando cambia el modal / datos iniciales
    form.clearErrors()
    form.day = props.schedule?.day ?? props.initial?.day ?? ''
    form.start_time = props.schedule?.start_time ?? props.initial?.start_time ?? ''
    form.end_time = props.schedule?.end_time ?? props.initial?.end_time ?? ''
    form.classroom_id = props.schedule?.classroom_id ?? null
}, { immediate: true })

// Muestra SweetAlert con el mensaje (puede venir en varias formas desde el backend)
function showBackendError(payload) {
    // payload puede ser: error.response.data.message | error.response.data.error | error.response.data.errors
    let msg = null

    // si viene un objeto de errores (Laravel validation), priorizamos campos comunes
    if (payload?.errors) {
        // preferimos schedule, luego start_time, luego end_time
        msg = payload.errors.schedule?.[0] || payload.errors.start_time?.[0] || payload.errors.end_time?.[0]
    }

    // otros formatos
    msg = msg || payload?.message || payload?.error || payload

    // fallback
    msg = msg || 'Server returned an error.'

    custom({
        icon: 'error',
        title: 'Error',
        html: msg,
        confirmButtonText: 'OK'
    })
}

function submitCreate() {
    axios.post(route('class-schedules.store', props.classGroupId), form.data())
        .then(() => {
            emit('saved')
        })
        .catch(errorResponse => {
            if (errorResponse.response?.status === 422) {
                const errors = errorResponse.response.data.errors
                const msg = errors?.start_time?.[0] || errors?.schedule?.[0] || errors?.end_time?.[0]

                if (msg) {
                    custom({
                        icon: 'error',
                        title: 'Conflict',
                        html: msg,
                        confirmButtonText: 'OK'
                    })
                } else {
                    custom({
                        icon: 'error',
                        title: 'Validation Error',
                        html: JSON.stringify(errors),
                        confirmButtonText: 'OK'
                    })
                }
            } else {
                error('An unexpected error occurred', 'Error')
            }
        })
}

// EDIT (axios). Mantenemos axios por petición explícita del usuario; sincronizamos errores a form
async function submitEdit() {
    try {
        await axios.post(route('class-schedules.update', [props.classGroupId, props.schedule.id]), {
            ...form.data(),
            start_time: form.start_time?.slice(0, 5),
            end_time: form.end_time?.slice(0, 5),
            _method: 'PUT'
        })
        emit('saved')
    } catch (error) {
        // error.response.data puede ser:
        // { error: "mensaje" } OR { message: "mensaje" } OR { errors: { start_time: [...]} }
        const resData = error.response?.data

        if (resData?.errors) {
            // sincronizamos errores al form (InputError los mostrará)
            form.setError ? form.setError(resData.errors) : (form.errors = resData.errors)
        }

        // mostramos SweetAlert con la razón (prioridades)
        const msg = resData?.message || resData?.error || resData || error.message
        showBackendError({ message: Array.isArray(msg) ? msg.join('<br>') : msg })
    }
}

async function remove() {
    try {
        await axios.post(route('class-schedules.destroy', [props.classGroupId, props.schedule.id]), {
            _method: 'DELETE'
        })
        emit('deleted')
    } catch (error) {
        const resData = error.response?.data
        if (resData?.errors) {
            form.setError ? form.setError(resData.errors) : (form.errors = resData.errors)
        }
        showBackendError(resData || error.message)
    }
}

function submit() {
    props.mode === 'create' ? submitCreate() : submitEdit()
}
</script>

<template>
    <Modal :show="show" @close="onClose">
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4 capitalize">
                {{ mode === "edit" ? "Edit Schedule" : "Add Schedule" }}
            </h2>

            <form @submit.prevent="submit">
                <!-- Día -->
                <div class="mb-4">
                    <InputLabel value="Day" />
                    <select v-model="form.day"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-800 dark:text-white">
                        <option value="" disabled>Select day</option>
                        <option v-for="day in days" :key="day" :value="day" class="capitalize">
                            {{ day }}
                        </option>
                    </select>
                    <InputError :message="form.errors?.day" />
                </div>

                <!-- Hora inicio -->
                <div class="mb-4">
                    <InputLabel value="Start Time" />
                    <TextInput v-model="form.start_time" type="time" step="60" class="mt-1 block w-full" required />
                    <InputError :message="form.errors?.start_time" />
                </div>

                <!-- Hora fin -->
                <div class="mb-4">
                    <InputLabel value="End Time" />
                    <TextInput v-model="form.end_time" type="time" step="60" class="mt-1 block w-full" required />
                    <InputError :message="form.errors?.end_time" />
                </div>

                <!-- Aula -->
                <div class="mb-4">
                    <InputLabel value="Classroom" />
                    <select v-model="form.classroom_id"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-800 dark:text-white">
                        <option value="">— None —</option>
                        <option v-for="room in classrooms" :key="room.id" :value="room.id">
                            {{ room.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors?.classroom_id || form.errors?.classroom" />
                </div>

                <!-- Botones -->
                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-between sm:items-center gap-3">
                    <div v-if="mode === 'edit'">
                        <button type="button" @click="remove"
                            class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-semibold rounded-md text-sm border border-red-300 shadow-sm">
                            <i class="fa-solid fa-trash mr-2"></i> Delete schedule
                        </button>
                    </div>

                    <div class="flex justify-end gap-2">
                        <SecondaryButton @click="onClose"
                            class="text-sm text-gray-500 dark:text-gray-300 hover:underline">
                            Cancel
                        </SecondaryButton>

                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ mode === 'edit' ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>
