<script setup>
import { ref, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    show: Boolean,
    schedule: Object,
    classGroupId: Number,
    mode: {
        type: String,
        default: "create", // or "edit"
    },
    onClose: Function,
    onSubmit: Function,
    initial: Object, // 👈 Para valores sugeridos (ej: desde el slot seleccionado en FullCalendar)
});

// Formulario reactivo
const form = ref({
    day: props.schedule?.day ?? props.initial?.day ?? "",
    start_time: props.schedule?.start_time ?? props.initial?.start_time ?? "",
    end_time: props.schedule?.end_time ?? props.initial?.end_time ?? "",
    classroom: props.schedule?.classroom ?? "",
});

// Reactividad: actualiza si cambia el horario seleccionado o inicial
watch(
    () => [props.schedule, props.initial],
    () => {
        form.value = {
            day: props.schedule?.day ?? props.initial?.day ?? "",
            start_time: props.schedule?.start_time ?? props.initial?.start_time ?? "",
            end_time: props.schedule?.end_time ?? props.initial?.end_time ?? "",
            classroom: props.schedule?.classroom ?? "",
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
                    <TextInput v-model="form.start_time" type="time" class="mt-1 block w-full" required />
                    <InputError :message="form.errors?.start_time" />
                </div>

                <!-- Hora fin -->
                <div class="mb-4">
                    <InputLabel value="End Time" />
                    <TextInput v-model="form.end_time" type="time" class="mt-1 block w-full" required />
                    <InputError :message="form.errors?.end_time" />
                </div>

                <!-- Aula -->
                <div class="mb-4">
                    <InputLabel value="Classroom" />
                    <TextInput v-model="form.classroom" class="mt-1 block w-full" />
                    <InputError :message="form.errors?.classroom" />
                </div>

                <!-- Botones -->
                <div class="mt-6 flex justify-end gap-2">
                    <PrimaryButton type="submit">
                        {{ mode === "edit" ? "Update" : "Create" }}
                    </PrimaryButton>
                    <PrimaryButton v-if="mode === 'edit'" class="bg-red-600 hover:bg-red-700 text-white ml-auto"
                        type="button" @click="$emit('delete')">
                        Delete
                    </PrimaryButton>

                    <button type="button" @click="onClose"
                        class="text-sm text-gray-500 dark:text-gray-300 hover:underline">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
