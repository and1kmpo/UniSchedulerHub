<script setup>
import FormSection from "@/Components/UI/Forms/FormSection.vue";
import FormGrid from "@/Components/UI/Forms/FormGrid.vue";
import FormActions from "@/Components/UI/Forms/FormActions.vue";

import BaseInput from "@/Components/UI/Base/BaseInput.vue";
import BaseTextarea from "@/Components/UI/Base/BaseTextarea.vue";
import BaseCheckbox from "@/Components/UI/Base/BaseCheckbox.vue";

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
    <form @submit.prevent="$emit('submit')" class="overflow-hidden rounded-lg border border-border-light bg-surface shadow-sm dark:border-border-dark dark:bg-surface-dark">
        <FormSection :title="updating ? 'Update Subject' : 'Create Subject'" :description="updating
            ? 'Update the selected subject information.'
            : 'Create a new university subject.'
            ">
            <FormGrid :cols="2">

                <BaseInput v-model="form.name" label="Subject" placeholder="Enter subject name"
                    :error="form.errors.name" required />

                <BaseInput v-model="form.credits" type="number" label="Credits" placeholder="Enter subject credits"
                    :error="form.errors.credits" required />

                <div class="md:col-span-2">
                    <BaseTextarea v-model="form.description" label="Description" placeholder="Enter subject description"
                        :error="form.errors.description" rows="4" />
                </div>

                <BaseInput v-model="form.knowledge_area" label="Knowledge Area" placeholder="Enter knowledge area"
                    :error="form.errors.knowledge_area" required />

                <BaseCheckbox v-model="form.elective" label="Elective Subject"
                    description="Available as an elective option." :error="form.errors.elective" />

            </FormGrid>
        </FormSection>

        <FormActions>

            <BaseButton type="button" variant="secondary" @click="handleCancel">
                Cancel
            </BaseButton>

            <BaseButton type="submit" variant="primary" :disabled="processing">
                <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2"></i>

                {{ updating ? "Update Subject" : "Create Subject" }}
            </BaseButton>

        </FormActions>
    </form>
</template>


