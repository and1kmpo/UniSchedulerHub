<script setup>
import { ref, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    show: Boolean,
    schedule: Object,
    classGroupId: Number,
    classrooms: Array,
    mode: {
        type: String,
        default: "create",
    },
    onClose: Function,
    onSubmit: Function,
    initial: Object,
});

console.log(props);

// Formulario reactivo
const form = ref({
    day: props.schedule?.day ?? props.initial?.day ?? "",
    start_time: props.schedule?.start_time ?? props.initial?.start_time ?? "",
    end_time: props.schedule?.end_time ?? props.initial?.end_time ?? "",
    classroom_id: props.schedule?.classroom_id ?? null,
});

// Reactividad: actualiza si cambia el horario seleccionado o inicial
watch(
    () => [props.schedule, props.initial],
    () => {
        form.value = {
            day: props.schedule?.day ?? props.initial?.day ?? "",
            start_time: props.schedule?.start_time ?? props.initial?.start_time ?? "",
            end_time: props.schedule?.end_time ?? props.initial?.end_time ?? "",
            classroom_id: props.schedule?.classroom_id ?? null,
        };
    },
    { immediate: true }
);

const days = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"];
</script>

<template>
    <Modal :show="show" @close="onClose">
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4 capitalize">
                {{ mode === "edit" ? "Edit Schedule" : "Add Schedule" }}
            </h2>

            <form @submit.prevent="onSubmit(form)">
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
                        <option value="" disabled>Select classroom</option>
                        <option v-for="room in classrooms" :key="room.id" :value="room.id">
                            {{ room.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors?.classroom" />
                </div>

                <!-- Botones -->
                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-between sm:items-center gap-3">
                    <!-- Izquierda: Botón de eliminar -->
                    <div v-if="mode === 'edit'">
                        <button type="button" @click="$emit('delete')"
                            class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-semibold rounded-md text-sm border border-red-300 shadow-sm">
                            <i class="fa-solid fa-trash mr-2"></i> Delete schedule
                        </button>
                    </div>

                    <!-- Derecha: Confirmar o cancelar -->
                    <div class="flex justify-end gap-2">
                        <SecondaryButton @click="onClose"
                            class="text-sm text-gray-500 dark:text-gray-300 hover:underline">
                            Cancel
                        </SecondaryButton>

                        <PrimaryButton type="submit">
                            {{ mode === "edit" ? "Update" : "Create" }}
                        </PrimaryButton>
                    </div>
                </div>

            </form>
        </div>
    </Modal>
</template>
