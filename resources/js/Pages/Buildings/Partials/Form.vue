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
    <form @submit.prevent="$emit('submit')" class="overflow-hidden rounded-lg border border-border-light bg-surface shadow-sm dark:border-border-dark dark:bg-surface-dark">
        <FormSection :title="updating ? 'Update Building' : 'Create Building'" :description="updating
            ? 'Update infrastructure building information.'
            : 'Create a new infrastructure building.'
            ">
            <FormGrid :cols="2">

                <BaseInput v-model="form.name" label="Building Name" placeholder="Engineering Building"
                    :error="form.errors.name" required />

                <BaseInput v-model="form.code" label="Code" placeholder="ENG-B" :error="form.errors.code" required />

                <div class="md:col-span-2">
                    <BaseTextarea v-model="form.description" label="Description" rows="4"
                        placeholder="Building description..." :error="form.errors.description" />
                </div>

            </FormGrid>
        </FormSection>

        <FormActions>

            <BaseButton type="button" variant="secondary" @click="handleCancel">
                Cancel
            </BaseButton>

            <BaseButton type="submit" variant="primary" :disabled="processing">
                <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2"></i>

                {{ updating ? "Update Building" : "Create Building" }}
            </BaseButton>

        </FormActions>
    </form>
</template>

