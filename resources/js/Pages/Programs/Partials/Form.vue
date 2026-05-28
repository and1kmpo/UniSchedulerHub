<script setup>
import FormSection from "@/Components/UI/Forms/FormSection.vue";
import FormGrid from "@/Components/UI/Forms/FormGrid.vue";
import FormActions from "@/Components/UI/Forms/FormActions.vue";

import BaseInput from "@/Components/UI/Base/BaseInput.vue";
import BaseTextarea from "@/Components/UI/Base/BaseTextarea.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";

defineProps({
    form: {
        type: Object,
        required: true,
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
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="overflow-hidden rounded-2xl bg-white shadow dark:bg-gray-900">
        <FormSection :title="updating ? 'Update Program' : 'Create Program'" :description="updating
            ? 'Update the selected academic program information.'
            : 'Create a new academic program.'
            ">
            <FormGrid :cols="2">

                <BaseInput v-model="form.name" label="Program Name" placeholder="Enter program name"
                    :error="form.errors.name" required />

                <div class="md:col-span-2">
                    <BaseTextarea v-model="form.description" label="Description"
                        placeholder="Enter a description for this program" :error="form.errors.description" rows="5"
                        required />
                </div>

            </FormGrid>
        </FormSection>

        <FormActions>

            <BaseButton type="button" variant="secondary" @click="handleCancel">
                Cancel
            </BaseButton>

            <BaseButton type="submit" variant="primary" :disabled="processing">
                <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2"></i>

                {{ updating ? "Update Program" : "Create Program" }}
            </BaseButton>

        </FormActions>

    </form>
</template>
