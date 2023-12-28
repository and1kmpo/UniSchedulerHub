<script>
export default {
    name: "SubjectsEdit",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import SubjectForm from "@/Components/Subjects/Form.vue";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    subject: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.subject.name,
    description: props.subject.description,
    credits: props.subject.credits,
    knowledge_area: props.subject.knowledge_area,
    elective: props.subject.elective,
});

const handleCancel = () => {
    Inertia.visit(route("subjects.index"));
};
</script>

<template>
    <AppLayout title="Edit subject">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit subject
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                    >
                        <div class="p-6 bg-white border-b border-gray-200">
                            <SubjectForm
                                :updating="true"
                                :form="form"
                                @submit="
                                    form.put(
                                        route('subjects.update', subject.id)
                                    )
                                "
                                :handleCancel="handleCancel"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
