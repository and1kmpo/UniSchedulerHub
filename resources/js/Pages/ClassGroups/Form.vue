<script setup>
import { computed } from "vue";

import FormSection from "@/Components/UI/Forms/FormSection.vue";
import FormGrid from "@/Components/UI/Forms/FormGrid.vue";
import FormActions from "@/Components/UI/Forms/FormActions.vue";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseInput from "@/Components/UI/Base/BaseInput.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },

    subjects: {
        type: Array,
        default: () => [],
    },

    professors: {
        type: Array,
        default: () => [],
    },

    updating: {
        type: Boolean,
        default: false,
    },

    processing: {
        type: Boolean,
        default: false,
    },

    handleCancel: {
        type: Function,
        required: true,
    },
});

defineEmits(["submit"]);

const subjectOptions = computed(() =>
    props.subjects.map((subject) => ({
        label: `${subject.code} - ${subject.name}`,
        value: subject.id,
    }))
);

const professorOptions = computed(() =>
    props.professors.map((professor) => ({
        label: professor.name,
        value: professor.id,
    }))
);

const modalityOptions = [
    { label: "In-person", value: "In-person" },
    { label: "Virtual", value: "Virtual" },
    { label: "Hybrid", value: "Hybrid" },
];

const shiftOptions = [
    { label: "Day", value: "Day" },
    { label: "Night", value: "Night" },
    { label: "Intensive", value: "Intensive" },
];

const statusOptions = [
    { label: "Draft", value: "draft" },
    { label: "Published", value: "published" },
    { label: "Cancelled", value: "cancelled" },
    { label: "Closed", value: "closed" },
];

const dayOptions = [
    { label: "Monday", value: "monday" },
    { label: "Tuesday", value: "tuesday" },
    { label: "Wednesday", value: "wednesday" },
    { label: "Thursday", value: "thursday" },
    { label: "Friday", value: "friday" },
    { label: "Saturday", value: "saturday" },
];

const selectedSubject = computed(() =>
    props.subjects.find((subject) => Number(subject.id) === Number(props.form.subject_id))
);

const generatedSemester = computed(() => {
    const now = new Date();

    return `${now.getFullYear()}-${now.getMonth() < 6 ? "I" : "II"}`;
});

const generatedCodePreview = computed(() => {
    const subjectCode = selectedSubject.value?.code || "SUBJ";

    return `${subjectCode}-${generatedSemester.value}-G?`;
});

const addSchedule = () => {
    props.form.schedules.push({
        day: "monday",
        start_time: "08:00",
        end_time: "10:00",
    });
};

const removeSchedule = (index) => {
    props.form.schedules.splice(index, 1);
};
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="overflow-hidden rounded-2xl bg-white shadow dark:bg-gray-900">
        <FormSection :title="updating ? 'Update Class Group' : 'Create Class Group'" :description="updating
            ? 'Update academic group information.'
            : 'Create a group with its initial schedule block.'
            ">
            <FormGrid :cols="2">
                <BaseSelect v-model="form.subject_id" label="Subject" placeholder="Select subject"
                    :options="subjectOptions" :error="form.errors.subject_id" required />

                <BaseSelect v-model="form.professor_id" label="Professor" placeholder="Select professor"
                    :options="professorOptions" :error="form.errors.professor_id" required />

                <BaseSelect v-model="form.modality" label="Modality" :options="modalityOptions"
                    :error="form.errors.modality" required />

                <BaseSelect v-model="form.shift" label="Shift" :options="shiftOptions" :error="form.errors.shift"
                    required />

                <BaseInput v-model="form.capacity" type="number" label="Capacity" placeholder="30"
                    :error="form.errors.capacity" required />

                <BaseSelect v-model="form.status" label="Status" :options="statusOptions" :error="form.errors.status"
                    required />
            </FormGrid>

            <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                            Code preview
                        </p>

                        <p class="mt-1 font-mono font-semibold text-indigo-700 dark:text-indigo-300">
                            {{ generatedCodePreview }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                            Semester
                        </p>

                        <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                            {{ generatedSemester }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                            Group code
                        </p>

                        <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                            Assigned automatically
                        </p>
                    </div>
                </div>
            </div>
        </FormSection>

        <FormSection v-if="!updating" title="Initial Schedule"
            description="Add the first schedule blocks for this group. More detailed scheduling can be handled later from the scheduler.">
            <div class="space-y-4">
                <div v-for="(schedule, index) in form.schedules" :key="index"
                    class="grid gap-4 rounded-xl border border-gray-200 p-4 dark:border-gray-700 md:grid-cols-[1fr_1fr_1fr_auto]">
                    <BaseSelect v-model="schedule.day" label="Day" :options="dayOptions"
                        :error="form.errors[`schedules.${index}.day`]" required />

                    <BaseInput v-model="schedule.start_time" type="time" label="Start time"
                        :error="form.errors[`schedules.${index}.start_time`]" required />

                    <BaseInput v-model="schedule.end_time" type="time" label="End time"
                        :error="form.errors[`schedules.${index}.end_time`]" required />

                    <div class="flex items-end">
                        <BaseButton type="button" variant="danger" :disabled="form.schedules.length === 1"
                            @click="removeSchedule(index)">
                            <i class="fa-solid fa-trash" />
                        </BaseButton>
                    </div>
                </div>

                <p v-if="form.errors.schedules" class="text-sm text-red-500">
                    {{ form.errors.schedules }}
                </p>

                <BaseButton type="button" variant="secondary" @click="addSchedule">
                    <i class="fa-solid fa-plus mr-2" />
                    Add Schedule Block
                </BaseButton>
            </div>
        </FormSection>

        <FormActions>
            <BaseButton type="button" variant="secondary" @click="handleCancel">
                Cancel
            </BaseButton>

            <BaseButton type="submit" variant="primary" :disabled="processing">
                <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2" />

                {{ updating ? "Update Group" : "Create Group" }}
            </BaseButton>
        </FormActions>
    </form>
</template>
