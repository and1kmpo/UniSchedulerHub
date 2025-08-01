<!-- resources/js/Pages/Programs/Create.vue -->
<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    description: "",
});

const submit = () => {
    form.post(route("programs.store"), {
        onSuccess: () => {
            router.visit(route("programs.index"));
        },
    });
};
</script>

<template>
    <AppLayout title="Create Program">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Program</h2>
        </template>

        <div class="py-10 max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="bg-white p-6 rounded-lg shadow space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input v-model="form.name" type="text" class="mt-1 block w-full rounded border-gray-300" />
                    <span class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea v-model="form.description" class="mt-1 block w-full rounded border-gray-300"></textarea>
                    <span class="text-sm text-red-600" v-if="form.errors.description">{{ form.errors.description
                        }}</span>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="$inertia.visit(route('programs.index'))"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded">Create</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
