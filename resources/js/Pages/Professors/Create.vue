<script>
export default {
    name: "ProfessorsCreate",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import ProfessorForm from "@/Components/Professors/Form.vue";
import { Inertia } from "@inertiajs/inertia";

const form = useForm({
    document: "",
    first_name: "",
    last_name: "",
    phone: "",
    email: "",
    address: "",
    city: "",
    picture: "",
});

const handleCancel = () => {
    Inertia.visit(route("professors.index"));
};
</script>

<template>
    <AppLayout title="Create professor">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Create new professor
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                    >
                        <div class="p-6 bg-white border-b border-gray-200">
                            <ProfessorForm
                                :form="form"
                                @submit="form.post(route('professors.store'))"
                                :handleCancel="handleCancel"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
