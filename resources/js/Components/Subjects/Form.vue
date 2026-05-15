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
    <div v-if="form && Object.keys(form).length > 0">
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
                <div class="col-span-6 space-y-5">
                    <div>
                    <InputLabel for="name" value="Subject" />
                    <TextInput id="name" v-model="form.name" type="text" autocomplete="name" class="mt-1 block w-full"
                        placeholder="Enter a subject name" />
                    <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                    <InputLabel for="description" value="Description" />
                    <TextInput id="description" v-model="form.description" type="text" autocomplete="description"
                        class="mt-1 block w-full" placeholder="Enter program description" />
                    <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <div>
                    <InputLabel for="credits" value="Credits" />
                    <TextInput id="credits" v-model="form.credits" type="number" min="1" max="50" autocomplete="credits"
                        class="mt-1 block w-full" placeholder="Enter number credits for this subject" />
                    <InputError :message="form.errors.credits" class="mt-2" />
                    </div>

                    <div>
                    <InputLabel for="knowledge_area" value="Knowledge area" />
                    <TextInput id="knowledge_area" v-model="form.knowledge_area" type="text"
                        autocomplete="knowledge_area" class="mt-1 block w-full"
                        placeholder="Enter knowledge area for this subject" />
                    <InputError :message="form.errors.knowledge_area" class="mt-2" />
                    </div>

                    <div>
                    <InputLabel for="elective" value="Elective" />
                    <select v-model="form.elective" name="elective" id="elective"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option :value="true">Yes</option>
                        <option :value="false">No</option>
                    </select>
                    <InputError :message="form.errors.elective" class="mt-2" />
                    </div>
                </div>
            </template>

            <template #actions>
                <PrimaryButton class="bg-indigo-700 hover:bg-indigo-600 rounded p-2 px-4 text-white">
                    {{ updating ? "Update" : "Create" }}
                </PrimaryButton>

                <DangerButton @click="handleCancel" class="ml-2">Cancel</DangerButton>
            </template>
        </FormSection>
    </div>

    <div v-else class="text-gray-500 text-sm">Loading form...</div>
</template>
