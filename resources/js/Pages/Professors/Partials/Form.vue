<script setup>
import FormSection from "@/Components/UI/Forms/FormSection.vue";
import FormGrid from "@/Components/UI/Forms/FormGrid.vue";
import FormActions from "@/Components/UI/Forms/FormActions.vue";

import BaseInput from "@/Components/UI/Base/BaseInput.vue";
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
        <FormSection :title="updating ? 'Update Professor' : 'Create Professor'" :description="updating
            ? 'Update professor profile and contact information.'
            : 'Create a professor profile connected to a user account.'
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

            </FormGrid>
        </FormSection>

        <FormActions>

            <BaseButton type="button" variant="secondary" @click="handleCancel">
                Cancel
            </BaseButton>

            <BaseButton type="submit" variant="primary" :disabled="processing">
                <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2"></i>

                {{ updating ? "Update Professor" : "Create Professor" }}
            </BaseButton>

        </FormActions>
    </form>
</template>


