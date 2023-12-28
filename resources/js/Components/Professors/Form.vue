<script>
export default {
    name: "ProfessorForm",
};
</script>

<script setup>
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
    form: {
        type: Object,
        required: true,
    },
    updating: {
        type: Boolean,
        required: false,
        default: false,
    },
    handleCancel: Function,
});

defineEmits(["submit"]);
</script>

<template>
    <FormSection @submitted="$emit('submit')">
        <template #title>{{
            updating ? "Update professor" : "Create new professor"
        }}</template>

        <template #description>
            {{
                updating
                    ? "Update the selected professor"
                    : "Create new professor from scratch"
            }}
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-6 space-y-2">
                <InputLabel for="first_name" value="Professor" />
                <TextInput
                    for="first_name"
                    v-model="form.first_name"
                    type="text"
                    autocomplete="first_name"
                    class="block w-full"
                    placeholder="Enter the professor's first name"
                />
                <InputError
                    :message="$page.props.errors.first_name"
                    class="mt-2"
                />

                <InputLabel for="last_name" value="First name" />
                <TextInput
                    for="last_name"
                    v-model="form.last_name"
                    type="text"
                    autocomplete="last_name"
                    class="mt-1 block w-full"
                    placeholder="Enter the professor's first last name"
                />
                <InputError
                    :message="$page.props.errors.last_name"
                    class="mt-2"
                />

                <InputLabel for="document" value="Document" />
                <TextInput
                    for="document"
                    v-model="form.document"
                    type="number"
                    autocomplete="document"
                    class="mt-1 block w-full"
                    placeholder="Enter the professor's document number"
                />
                <InputError
                    :message="$page.props.errors.document"
                    class="mt-2"
                />

                <InputLabel for="phone" value="Phone" />
                <TextInput
                    for="phone"
                    v-model="form.phone"
                    type="number"
                    autocomplete="phone"
                    class="mt-1 block w-full"
                    placeholder="Enter the professor's phone number"
                />
                <InputError :message="$page.props.errors.phone" class="mt-2" />

                <InputLabel for="email" value="E-mail" />
                <TextInput
                    for="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    class="mt-1 block w-full"
                    placeholder="Enter the  professor's email address"
                />
                <InputError :message="$page.props.errors.email" class="mt-2" />

                <InputLabel for="address" value="Address" />
                <TextInput
                    for="address"
                    v-model="form.address"
                    type="text"
                    autocomplete="address"
                    class="mt-1 block w-full"
                    placeholder="Enter the  professor's address"
                />
                <InputError
                    :message="$page.props.errors.address"
                    class="mt-2"
                />

                <InputLabel for="city" value="City" />
                <TextInput
                    for="city"
                    v-model="form.city"
                    type="text"
                    autocomplete="city"
                    class="mt-1 block w-full"
                    placeholder="Enter the  professor's city"
                />
                <InputError :message="$page.props.errors.city" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <PrimaryButton
                class="bg-indigo-700 hover:bg-indigo-600 rounded p-2 px-4 text-white"
            >
                {{ updating ? "Update" : "Create" }}
            </PrimaryButton>

            <DangerButton @click="handleCancel" class="ml-2"
                >Cancel</DangerButton
            >
        </template>
    </FormSection>
</template>
