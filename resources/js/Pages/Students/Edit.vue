<script>
export default {
    name: "StudentsEdit",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";
import StudentForm from "@/Components/Students/Form.vue";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
    programs: {
        type: Object,
        required: true,
    },
});

onMounted(() => {
    console.log("User_ID", props.student.student.name);
});

const form = useForm({
    name: props.student.student.name,
    document: props.student.student.document,
    phone: props.student.student.phone,
    email: props.student.student.email,
    address: props.student.student.address,
    city: props.student.student.city,
    semester: props.student.student.semester,
    program_id: props.student.student.program_id,
});


const handleCancel = () => {
    Inertia.visit(route("students.index"));
};
</script>

<template>
    <AppLayout title="Edit student">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit student
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <StudentForm :updating="true" :form="form" @submit="
                                form.put(
                                    route(
                                        'students.update',
                                        props.student.student.user_id
                                    )
                                )
                                " :handleCancel="handleCancel" :programs="programs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
