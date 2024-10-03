<script>
export default {
    name: "StudentCreate",
};
</script>

<script setup>
import 'vue-multiselect/dist/vue-multiselect.css';
import { ref, inject } from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import Multiselect from 'vue-multiselect';
import { defineProps } from "vue";

defineProps({
    subjects: {
        type: Array,
        required: true,
    },
});

const value = ref(null);
const options = ref([]);

// Obtener el objeto $page
const $page = inject('$page');

// Utiliza pluck para obtener un array asociativo de id y name
options.value = $page.props.subjects.pluck('name', 'id').all();

// Mostrar en la consola el objeto subjects
console.log($page.props.subjects);
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
                <div>
                    {{ "programs" + programs }}
                </div>
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
