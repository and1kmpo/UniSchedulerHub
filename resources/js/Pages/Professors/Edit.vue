<script>
export default {
    name: "ProfessorsEdit",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import ProfessorForm from "@/Components/Professors/Form.vue";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    professor: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.professor.professor.name,
    document: props.professor.professor.document,
    phone: props.professor.professor.phone,
    email: props.professor.professor.email,
    address: props.professor.professor.address,
    city: props.professor.professor.city,
});

const handleCancel = () => {
    Inertia.visit(route("professors.index"));
};
</script>

<template>
    <AppLayout title="Edit professor">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit professor
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <ProfessorForm :updating="true" :form="form" @submit="
                                form.put(
                                    route(
                                        'professors.update',
                                        props.professor.id
                                    )
                                )
                                " :handleCancel="handleCancel" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
