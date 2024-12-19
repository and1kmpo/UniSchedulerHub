<script>
export default {
    name: "UserForm",
};
</script>

<script setup>
import FormSection from "@/Components/FormSection.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

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
        <template #title>{{ updating ? "Update User" : "Create User" }}</template>
        <template #description>
            {{ updating ? "Modify the selected user." : "Create a new user for the system." }}
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Common Fields -->
            <div>
                <InputLabel for="role" value="Role" />
                <select id="role" v-model="form.role" class="form-input rounded-md shadow-sm mt-1 block w-full">
                    <option value="Professor">Professor</option>
                    <option value="Student">Student</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>

            <TextInput id="name" v-model="form.name" label="Name" required />
            <TextInput id="email" type="email" v-model="form.email" label="Email" required />
            <TextInput id="document" v-model="form.document" label="Document" required />

            <!-- Professor Fields -->
            <template v-if="form.role === 'Professor'">
                <TextInput id="phone" v-model="form.phone" label="Phone" />
                <TextInput id="address" v-model="form.address" label="Address" />
                <TextInput id="city" v-model="form.city" label="City" />
            </template>

            <!-- Student Fields -->
            <template v-if="form.role === 'Student'">
                <TextInput id="program_id" v-model="form.studentData.program_id" label="Program ID" />
                <TextInput id="semester" v-model="form.studentData.semester" label="Semester" />
            </template>
        </div>

        <template #actions>
            <PrimaryButton class="bg-indigo-700 hover:bg-indigo-600 rounded p-2 px-4 text-white">
                {{ updating ? "Update" : "Create" }}
            </PrimaryButton>
            <DangerButton @click="handleCancel" class="ml-2">Cancel</DangerButton>
        </template>
    </FormSection>
</template>
