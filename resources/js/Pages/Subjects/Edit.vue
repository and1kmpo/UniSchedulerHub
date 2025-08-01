<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import SubjectForm from "@/Components/Subjects/Form.vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/vue3";
import { watch } from "vue";
import axios from 'axios';
import { router } from '@inertiajs/vue3'; // Si quieres redirigir tras guardar
import { useAlert } from '@/Components/Composables/useAlert'; // Opcional si ya lo usas
const { success, error } = useAlert(); // Si tienes alertas definidas

const props = defineProps({
    subject: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: "",
    description: "",
    credits: "",
    knowledge_area: "",
    elective: "",
});
const submitUpdate = async () => {
    try {
        await axios.post(`/subjects/${props.subject.id}`, {
            ...form.data(),
            _method: 'PUT',
        });

        success('Subject updated successfully.');
        router.visit(route('subjects.index')); // Redirige tras guardar
    } catch (err) {
        if (err.response?.status === 422) {
            form.setErrors(err.response.data.errors); // Para mostrar errores de validación
        } else {
            error('Failed to update subject.');
        }
    }
};
watch(
    () => props.subject,
    (subject) => {
        if (subject) {
            form.name = subject.name ?? "";
            form.description = subject.description ?? "";
            form.credits = subject.credits ?? "";
            form.knowledge_area = subject.knowledge_area ?? "";
            form.elective = subject.elective ?? "";
        }
    },
    { immediate: true }
);

const handleCancel = () => {
    router.visit(route("subjects.index")); // ✔️ Correcto para Vue 3
};

</script>

<template>
    <AppLayout title="Edit Subject">
        <h1 class="text-2xl font-bold mb-4">Edit Subject</h1>

        <SubjectForm :updating="true" :form="form" :handleCancel="handleCancel" @submit="submitUpdate" />

    </AppLayout>
</template>
