<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import { route } from "ziggy-js";
import { useAlert } from "@/Components/Composables/useAlert";

const { toastSuccess, toastError } = useAlert();

const props = defineProps({
    classGroup: Object,
    subjects: Array,
    professors: Array,
    currentPeriodId: Number
});

const form = ref({
    subject_id: props.classGroup?.subject_id || "",
    professor_id: props.classGroup?.professor_id || "",
    capacity: props.classGroup?.capacity || 30,
    modality: props.classGroup?.modality || "Presential",
    shift: props.classGroup?.shift || "Day",
    academic_period_id: props.currentPeriodId,

    // schedules: array de objetos { day, start_time, end_time }
    schedules: props.classGroup?.schedules?.length
        ? props.classGroup.schedules.map(s => ({
            day: s.day,
            start_time: s.start_time,
            end_time: s.end_time
        }))
        : [{ day: '', start_time: '', end_time: '' }]

});

const errors = ref({});

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']

function addSchedule() {
    form.value.schedules.push({ day: '', start_time: '', end_time: '' })
}
function removeSchedule(i) {
    form.value.schedules.splice(i, 1)
}

const submit = async () => {
    try {
        errors.value = {};

        if (props.classGroup) {
            await axios.post(route('class-groups.update', props.classGroup.id), {
                ...form.value,
                _method: 'PUT'
            });
            toastSuccess("Group updated successfully");
        } else {
            await axios.post(route('class-groups.store'), form.value);
            toastSuccess("Group created successfully");
        }

        window.location.href = route('class-groups.index');
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors;
        } else {
            toastError("Something went wrong. Please try again.");
            console.error("Error submitting form:", err);
        }
    }
};

const cancel = () => {
    window.location.href = route('class-groups.index');
};

const selectedSubject = computed(() => props.subjects.find(s => s.id === form.value.subject_id));
const subjectCode = computed(() => selectedSubject.value?.code || "SUBJ");
const subjectName = computed(() => selectedSubject.value?.name || "Subject");
const generatedSemester = computed(() => {
    const now = new Date();
    return `${now.getFullYear()}-${now.getMonth() < 6 ? 'I' : 'II'}`;
});
const generatedName = computed(() =>
    `${subjectName.value} - ${form.value.modality} - ${form.value.shift}`
);
const generatedCodePreview = computed(() =>
    `${subjectCode.value}-${generatedSemester.value}-G?`
);
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Subject -->
            <div>
                <label class="label">Subject</label>
                <select v-model="form.subject_id" class="input">
                    <option value="">Select subject</option>
                    <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
                <p v-if="errors.subject_id" class="error">{{ errors.subject_id[0] }}</p>
            </div>

            <!-- Professor -->
            <div>
                <label class="label">Professor</label>
                <select v-model="form.professor_id" class="input">
                    <option value="">Select professor</option>
                    <option v-for="p in professors" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <p v-if="errors.professor_id" class="error">{{ errors.professor_id[0] }}</p>
            </div>

            <!-- Modality -->
            <div>
                <label class="label">Modality</label>
                <select v-model="form.modality" class="input">
                    <option value="Presential">Presential</option>
                    <option value="Virtual">Virtual</option>
                    <option value="Hybrid">Hybrid</option>
                </select>
                <p v-if="errors.modality" class="error">{{ errors.modality[0] }}</p>
            </div>

            <!-- Shift -->
            <div>
                <label class="label">Shift</label>
                <select v-model="form.shift" class="input">
                    <option value="Day">Day</option>
                    <option value="Night">Night</option>
                    <option value="Intensive">Intensive</option>
                </select>
                <p v-if="errors.shift" class="error">{{ errors.shift[0] }}</p>
            </div>

            <!-- Capacity -->
            <div>
                <label class="label">Capacity</label>
                <input type="number" min="1" v-model="form.capacity" class="input" />
                <p v-if="errors.capacity" class="error">{{ errors.capacity[0] }}</p>
            </div>
        </div>

        <!-- Schedules -->
        <div class="space-y-2">
            <h3 class="font-semibold">Schedules <small class="text-sm text-gray-500">(at least one)</small></h3>
            <div v-for="(sch, i) in form.schedules" :key="i" class="grid grid-cols-4 gap-4 items-end">
                <!-- Día -->
                <div>
                    <select v-model="sch.day" class="input">
                        <option disabled value="">Day</option>
                        <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
                    </select>
                    <p v-if="errors[`schedules.${i}.day`]" class="error">
                        {{ errors[`schedules.${i}.day`][0] }}
                    </p>
                </div>
                <!-- Hora inicio -->
                <div>
                    <input type="time" v-model="sch.start_time" class="input" />
                    <p v-if="errors[`schedules.${i}.start_time`]" class="error">
                        {{ errors[`schedules.${i}.start_time`][0] }}
                    </p>
                </div>
                <!-- Hora fin -->
                <div>
                    <input type="time" v-model="sch.end_time" class="input" />
                    <p v-if="errors[`schedules.${i}.end_time`]" class="error">
                        {{ errors[`schedules.${i}.end_time`][0] }}
                    </p>
                </div>
                <!-- Botón eliminar (solo si hay más de uno) -->
                <button v-if="form.schedules.length > 1" type="button" @click="removeSchedule(i)"
                    class="btn-secondary">−</button>
            </div>
            <!-- Añadir nuevo horario -->
            <button type="button" @click="addSchedule" class="btn-primary">
                + Add Schedule
            </button>
        </div>


        <!-- Vista previa -->
        <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded mt-4 text-sm space-y-1">
            <p><strong>Code:</strong> <span class="font-mono text-indigo-600 dark:text-indigo-400">{{
                generatedCodePreview }}</span></p>
            <p><strong>Semester:</strong> {{ generatedSemester }}</p>
            <p><strong>Group Code:</strong> Will be assigned (e.g., G1, G2...)</p>
            <p><strong>Name:</strong> {{ generatedName }}</p>
        </div>

        <!-- Buttons -->
        <div class="flex justify-between mt-6">
            <button type="button" class="btn-secondary" @click="cancel">Cancel</button>
            <button type="submit" class="btn-primary">
                {{ props.classGroup ? "Update Group" : "Create Group" }}
            </button>
        </div>
    </form>
</template>

<style scoped>
.input {
    @apply w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500;
}

.label {
    @apply block text-sm font-medium text-gray-800 dark:text-gray-100 mb-1;
}

.error {
    @apply text-sm text-red-500 mt-1;
}

.btn-primary {
    @apply bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded shadow transition;
}

.btn-secondary {
    @apply bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-semibold py-2 px-4 rounded shadow transition;
}
</style>
