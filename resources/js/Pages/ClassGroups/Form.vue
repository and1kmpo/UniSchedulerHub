<script setup>
import { useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    classGroup: Object,
    subjects: Array,
    professors: Array,
});

const form = useForm({
    subject_id: props.classGroup?.subject_id || "",
    professor_id: props.classGroup?.professor_id || "",
    capacity: props.classGroup?.capacity || 30,
    modality: props.classGroup?.modality || "Presential",
    shift: props.classGroup?.shift || "Day",
});

const submit = () => {
    props.classGroup
        ? form.put(route("class-groups.update", props.classGroup.id))
        : form.post(route("class-groups.store"));
};

// === Computed: Auto-generados ===
const selectedSubject = computed(() =>
    props.subjects.find(s => s.id === form.subject_id)
);

const subjectCode = computed(() =>
    selectedSubject.value?.code || "SUBJ"
);

const subjectName = computed(() =>
    selectedSubject.value?.name || "Subject"
);

const generatedSemester = computed(() => {
    const now = new Date();
    const period = now.getMonth() < 6 ? "I" : "II";
    return `${now.getFullYear()}-${period}`;
});

const generatedName = computed(() => {
    return `${subjectName.value} - ${form.modality} - ${form.shift}`;
});

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
                <p v-if="form.errors.subject_id" class="error">{{ form.errors.subject_id }}</p>
            </div>

            <!-- Professor -->
            <div>
                <label class="label">Professor</label>
                <select v-model="form.professor_id" class="input">
                    <option value="">Select professor</option>
                    <option v-for="p in professors" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <p v-if="form.errors.professor_id" class="error">{{ form.errors.professor_id }}</p>
            </div>

            <!-- Modality -->
            <div>
                <label class="label">Modality</label>
                <select v-model="form.modality" class="input">
                    <option value="Presential">Presential</option>
                    <option value="Virtual">Virtual</option>
                    <option value="Hybrid">Hybrid</option>
                </select>
                <p v-if="form.errors.modality" class="error">{{ form.errors.modality }}</p>
            </div>

            <!-- Shift -->
            <div>
                <label class="label">Shift</label>
                <select v-model="form.shift" class="input">
                    <option value="Day">Day</option>
                    <option value="Night">Night</option>
                    <option value="Intensive">Intensive</option>
                </select>
                <p v-if="form.errors.shift" class="error">{{ form.errors.shift }}</p>
            </div>

            <!-- Capacity -->
            <div>
                <label class="label">Capacity</label>
                <input type="number" min="1" v-model="form.capacity" class="input" />
                <p v-if="form.errors.capacity" class="error">{{ form.errors.capacity }}</p>
            </div>
        </div>

        <!-- Vista previa auto-generada -->
        <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded mt-4 text-sm space-y-1">
            <p><strong>Code:</strong> <span class="font-mono text-indigo-600 dark:text-indigo-400">{{
                    generatedCodePreview }}</span></p>
            <p><strong>Semester:</strong> {{ generatedSemester }}</p>
            <p><strong>Group Code:</strong> Will be assigned (e.g., G1, G2...)</p>
            <p><strong>Name:</strong> {{ generatedName }}</p>
        </div>

        <!-- Submit button -->
        <div class="flex justify-end mt-6">
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
</style>
