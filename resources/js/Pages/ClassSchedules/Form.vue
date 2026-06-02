<script setup>
import { computed } from "vue";

import FormActions from "@/Components/UI/Forms/FormActions.vue";
import FormGrid from "@/Components/UI/Forms/FormGrid.vue";
import FormSection from "@/Components/UI/Forms/FormSection.vue";

import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseInput from "@/Components/UI/Base/BaseInput.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },

    classGroup: {
        type: Object,
        required: true,
    },

    classrooms: {
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

    framed: {
        type: Boolean,
        default: true,
    },
});

defineEmits(["submit"]);

const dayOptions = [
    { label: "Monday", value: "monday" },
    { label: "Tuesday", value: "tuesday" },
    { label: "Wednesday", value: "wednesday" },
    { label: "Thursday", value: "thursday" },
    { label: "Friday", value: "friday" },
    { label: "Saturday", value: "saturday" },
];

const statusOptions = [
    { label: "Draft", value: "draft" },
    { label: "Published", value: "published" },
    { label: "Cancelled", value: "cancelled" },
    { label: "Closed", value: "closed" },
];

const classroomOptions = computed(() =>
    props.classrooms.map((classroom) => ({
        label: classroom.building?.name
            ? `${classroom.name} - ${classroom.building.name}`
            : classroom.name,
        value: classroom.id,
    }))
);

const groupLabel = computed(() => {
    const subject = props.classGroup.subject?.name || "Class group";
    const professor = props.classGroup.professor?.name || props.classGroup.professor?.user?.name;

    return professor ? `${subject} with ${professor}` : subject;
});
</script>

<template>
    <form @submit.prevent="$emit('submit')" :class="[
        'overflow-hidden bg-white dark:bg-gray-900',
        framed ? 'rounded-2xl shadow' : 'rounded-lg',
    ]">
        <FormSection :title="updating ? 'Update Schedule' : 'Create Schedule'" :description="updating
            ? 'Adjust this official schedule block.'
            : 'Add an official schedule block before using visual layout adjustments.'
            ">
            <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                    Class group
                </p>

                <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                    {{ groupLabel }}
                </p>
            </div>

            <FormGrid :cols="2">
                <BaseSelect v-model="form.day" label="Day" placeholder="Select day" :options="dayOptions"
                    :error="form.errors.day" required />

                <BaseSelect v-model="form.classroom_id" label="Classroom" placeholder="No classroom"
                    :options="classroomOptions" :error="form.errors.classroom_id" />

                <BaseInput v-model="form.start_time" type="time" label="Start time" :error="form.errors.start_time"
                    required />

                <BaseInput v-model="form.end_time" type="time" label="End time" :error="form.errors.end_time"
                    required />

                <BaseSelect v-model="form.status" label="Status" :options="statusOptions"
                    :error="form.errors.status" required />
            </FormGrid>

            <p v-if="form.errors.schedule" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ form.errors.schedule }}
            </p>
        </FormSection>

        <FormActions>
            <BaseButton type="button" variant="secondary" @click="handleCancel">
                Cancel
            </BaseButton>

            <BaseButton type="submit" variant="primary" :disabled="processing">
                <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2" />

                {{ updating ? "Update Schedule" : "Create Schedule" }}
            </BaseButton>
        </FormActions>
    </form>
</template>
