<script>
export default {
    name: "StudentCreate"
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import StudentForm from "@/Components/Students/Form.vue";
import { Inertia } from "@inertiajs/inertia";

defineProps({
    programs: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    document: "",
    name: "",
    phone: "",
    email: "",
    address: "",
    city: "",
    picture: "",
    semester: "",
    program_id: "",
});

const handleCancel = () => {
    Inertia.visit(route("students.index"));
};
</script>

<template>
    <AppLayout title="Create student">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Create new student
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <StudentForm :form="form" @submit="form.post(route('students.store'))"
                                :handleCancel="handleCancel" :programs="programs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
