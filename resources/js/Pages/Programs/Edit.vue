<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm, router } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import FormSection from "@/Components/FormSection.vue";

const props = defineProps({
    program: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.program.name ?? "",
    description: props.program.description ?? "",
});

const updateProgram = () => {
    router.post(route("programs.update", props.program.id), {
        _method: "put",
        name: form.name,
        description: form.description,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route("programs.index"));
        },
    });
};

const handleCancel = () => {
    router.visit(route("programs.index"));
};
</script>


<template>
    <AppLayout title="Edit Program">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Program</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <FormSection @submit.prevent="updateProgram">
                        <template #title>Edit Program</template>

                        <template #description>
                            Modify the program details below and click <strong>Save</strong> to update the record.
                        </template>

                        <template #form>
                            <!-- Program Name -->
                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="name" value="Program Name" />
                                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full"
                                    autocomplete="off" placeholder="e.g. Computer Engineering" />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <!-- Description -->
                            <div class="col-span-6 sm:col-span-4 mt-4">
                                <InputLabel for="description" value="Description" />
                                <textarea id="description" v-model="form.description" rows="4"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    placeholder="Add a short description about this academic program..."></textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>
                        </template>

                        <template #actions>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ form.processing ? "Updating..." : "Update" }}

                            </PrimaryButton>
                            <button type="button"
                                class="ml-4 text-sm text-gray-600 hover:text-gray-900 underline transition"
                                @click="handleCancel">
                                Cancel
                            </button>
                        </template>
                    </FormSection>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
