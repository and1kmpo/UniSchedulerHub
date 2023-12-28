<script>
export default {
    name: "programForm",
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
        <template #title
            >{{ updating ? "Update program" : "Create new program" }}
        </template>

        <template #description
            >{{
                updating
                    ? "Update the selected program"
                    : "Create new program from scratch"
            }}
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-6">
                <InputLabel for="name" value="Name" />
                <TextInput
                    for="name"
                    v-model="form.name"
                    type="text"
                    autocomplete="name"
                    class="mt-1 block w-full"
                    placeholder="Enter a program name"
                />
                <InputError :message="$page.props.errors.name" class="mt-2" />
                <InputLabel for="name" value="Description" />
                <TextInput
                    for="description"
                    v-model="form.description"
                    type="text"
                    autocomplete="description"
                    class="mt-1 block w-full"
                    placeholder="Enter program description"
                />
                <InputError
                    :message="$page.props.errors.description"
                    class="mt-2"
                />
            </div>
        </template>
        <template #actions>
            <PrimaryButton
                class="bg-indigo-700 hover:bg-indigo-600 rounded p-2 px-4 text-white"
                >{{ updating ? "Update" : "Create" }}</PrimaryButton
            >

            <DangerButton @click="handleCancel" class="ml-2"
                >Cancel</DangerButton
            >
        </template>
    </FormSection>
</template>
