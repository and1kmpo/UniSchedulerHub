<script>
export default {
    name: "ProgramsEdit",
};
</script>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import CategoryForm from "@/Components/Programs/Form.vue";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    program: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.program.name,
    description: props.program.description,
});

const handleCancel = () => {
    Inertia.visit(route("programs.index"));
};
</script>

<template>
    <AppLayout title="Edit program">
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit program
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                    >
                        <div class="p-6 bg-white border-b border-gray-200">
                            <CategoryForm
                                :updating="true"
                                :form="form"
                                @submit="
                                    form.put(
                                        route('programs.update', program.id)
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
