<script>
export default {
    name: "SubjectForm",
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
            updating ? "Update subject" : "Create new subject"
        }}</template>

        <template #description>
            {{
                updating
                    ? "Update the selected subject"
                    : "Create new subject from scratch"
            }}
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-6">
                <InputLabel for="name" value="Subject" />
                <TextInput
                    for="name"
                    v-model="form.name"
                    type="text"
                    autocomplete="name"
                    class="mt-1 block w-full"
                    placeholder="Enter a subject name"
                />
                <InputError :message="$page.props.errors.name" class="mt-2" />

                <InputLabel for="description" value="Description" />
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

                <InputLabel for="credits" value="Credits" />
                <TextInput
                    for="credits"
                    v-model="form.credits"
                    type="number"
                    autocomplete="credits"
                    class="mt-1 block w-full"
                    placeholder="Enter number credits for this subject"
                />
                <InputError
                    :message="$page.props.errors.credits"
                    class="mt-2"
                />

                <InputLabel for="knowledge_area" value="Knowledge area" />
                <TextInput
                    for="knowledge_area"
                    v-model="form.knowledge_area"
                    type="text"
                    autocomplete="knowledge_area"
                    class="mt-1 block w-full"
                    placeholder="Enter knowledge area for this subject"
                />
                <InputError
                    :message="$page.props.errors.knowledge_area"
                    class="mt-2"
                />

                <InputLabel for="elective" value="Elective" />
                <select
                    v-model="form.elective"
                    name="elective"
                    id="elective"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded"
                >
                    <option value="" disabled selected>Select an option</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>

                <InputError
                    :message="$page.props.errors.elective"
                    class="mt-2"
                />
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
