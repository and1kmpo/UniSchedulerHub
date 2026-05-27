<script setup>
import { computed } from "vue";

import FormSection from "@/Components/UI/Forms/FormSection.vue";
import FormGrid from "@/Components/UI/Forms/FormGrid.vue";
import FormActions from "@/Components/UI/Forms/FormActions.vue";

import BaseInput from "@/Components/UI/Base/BaseInput.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },

    programs: {
        type: Array,
        required: true,
    },

    curricula: {
        type: Array,
        default: () => [],
    },

    academicStatuses: {
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

const programOptions = computed(() =>
    props.programs.map((program) => ({
        label: program.name,
        value: program.id,
    }))
);

const curriculumOptions = computed(() =>
    props.curricula
        .filter((curriculum) => !props.form.program_id || Number(curriculum.program_id) === Number(props.form.program_id))
        .map((curriculum) => ({
            label: `${curriculum.name} (${curriculum.code})`,
            value: curriculum.id,
        }))
);
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="overflow-hidden rounded-2xl bg-white shadow dark:bg-gray-900">
        <FormSection :title="updating ? 'Update Student' : 'Create Student'" :description="updating
            ? 'Update student profile, program and academic status.'
            : 'Create a student profile connected to a user account.'
            ">
            <FormGrid :cols="2">

                <BaseInput v-model="form.name" label="Full Name" placeholder="Enter full name" :error="form.errors.name"
                    required />

                <BaseInput v-model="form.document" label="Document" placeholder="Enter document number"
                    :error="form.errors.document" required />

                <BaseInput v-model="form.email" type="email" label="Email" placeholder="Enter email address"
                    :error="form.errors.email" required />

                <BaseInput v-model="form.phone" label="Phone" placeholder="Enter phone number"
                    :error="form.errors.phone" required />

                <BaseInput v-if="!updating" v-model="form.password" type="password" label="Password"
                    placeholder="Enter temporary password" :error="form.errors.password" required />

                <BaseInput v-else v-model="form.password" type="password" label="New Password"
                    placeholder="Leave blank to keep current password" :error="form.errors.password" />

                <BaseInput v-model="form.city" label="City" placeholder="Enter city" :error="form.errors.city"
                    required />

                <div class="md:col-span-2">
                    <BaseInput v-model="form.address" label="Address" placeholder="Enter address"
                        :error="form.errors.address" required />
                </div>

                <BaseSelect v-model="form.semester" label="Semester" placeholder="Select semester"
                    :error="form.errors.semester" :options="Array.from({ length: 10 }, (_, index) => ({
                        label: String(index + 1),
                        value: index + 1,
                    }))" required />

                <BaseSelect v-model="form.program_id" label="Program" placeholder="Select program"
                    :error="form.errors.program_id" :options="programOptions" required />

                <BaseSelect v-model="form.curriculum_id" label="Curriculum" placeholder="Select curriculum"
                    :error="form.errors.curriculum_id" :options="curriculumOptions" />

                <BaseSelect v-model="form.academic_status" label="Academic Status" placeholder="Select status"
                    :error="form.errors.academic_status" :options="academicStatuses" />

            </FormGrid>
        </FormSection>

        <FormActions>

            <BaseButton type="button" variant="secondary" @click="handleCancel">
                Cancel
            </BaseButton>

            <BaseButton type="submit" variant="primary" :disabled="processing">
                <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2"></i>

                {{ updating ? "Update Student" : "Create Student" }}
            </BaseButton>

        </FormActions>
    </form>
</template>
