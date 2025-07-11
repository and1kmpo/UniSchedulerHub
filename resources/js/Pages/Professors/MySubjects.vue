<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                My Subjects
            </h1>
        </template>
        <div class="max-w-7xl mx-auto py-10">

            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                <thead class="bg-gray-100 text-center text-sm font-semibold text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Subject</th>
                        <th class="px-4 py-3">Knowledge area</th>
                        <th class="px-4 py-3">Credits</th>
                        <th class="px-4 py-3">Students</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-800 divide-y divide-gray-200 text-center">
                    <tr v-for="subject in subjects" :key="subject.id">
                        <td class="px-4 py-2">{{ subject.name }}</td>
                        <td class="px-4 py-2">{{ subject.knowledge_area || '—' }}</td>
                        <td class="px-4 py-2">{{ subject.credits }}</td>
                        <td class="px-4 py-2">{{ subject.students?.length || 0 }}</td>
                        <td class="px-4 py-2">
                            <button @click="openModal(subject)"
                                class="text-blue-600 hover:underline hover:text-blue-800 flex items-center">
                                🔍 More details
                            </button>
                            <a :href="`/subjects/${subject.id}/grades`" class="text-blue-600 hover:underline">📋
                                Record grades</a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Modal de Estudiantes -->
            <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xl relative">
                    <h2 class="text-xl font-bold mb-4">
                        {{ selectedSubject?.name }} Students
                    </h2>

                    <table class="w-full text-sm border border-gray-300 text-center">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 border">#</th>
                                <th class="px-3 py-2 border">Name</th>
                                <th class="px-3 py-2 border">Document</th>
                                <th class="px-3 py-2 border">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, index) in selectedSubject.students.slice(0, 5)" :key="student.id"
                                class="border-t">
                                <td class="px-3 py-2">{{ index + 1 }}</td>
                                <td class="px-3 py-2">{{ student.user?.name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ student.document }}</td>
                                <td class="px-3 py-2">{{ student.user?.email ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-2 text-sm text-gray-500">
                        Showing {{ Math.min(selectedSubject.students.length, 5) }} of
                        {{ selectedSubject.students.length }} students.
                    </div>
                    <div class="mt-4 text-center" v-if="selectedSubject.students.length > 5">
                        <a :href="`/subjects/${selectedSubject.id}/students`" class="text-blue-600 hover:underline">
                            👥 Show all students
                        </a>
                    </div>



                    <button class="absolute top-2 right-2 text-gray-500 hover:text-black" @click="closeModal">
                        ✖
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";

// Primero define los props
const props = defineProps({
    subjects: {
        type: Array,
        required: true
    }
});

// Luego puedes usar props.subjects
onMounted(() => {
    console.log('Subjects recibidos al montar:', props.subjects);
});

const showModal = ref(false);
const selectedSubject = ref(null);

function openModal(subject) {
    selectedSubject.value = subject;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    selectedSubject.value = null;
}
</script>
