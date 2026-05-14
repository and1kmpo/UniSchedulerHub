<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                My Subjects
            </h1>
        </template>
        <div class="max-w-7xl mx-auto py-10">

            <div v-if="props.groups.length === 0" class="text-center text-gray-500 py-10">
                You have no assigned groups in the active academic period.
            </div>

            <table v-else class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
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
                    <tr v-for="group in groups" :key="group.id">
                        <td class="px-4 py-2">
                            {{ group.subject.name }} <br>
                            <span class="text-xs text-gray-500">{{ group.code }}</span>
                        </td>

                        <td class="px-4 py-2">
                            {{ group.subject.knowledge_area }}
                        </td>

                        <td class="px-4 py-2">
                            {{ group.subject.credits }}
                        </td>

                        <td class="px-4 py-2">
                            {{ group.subject_enrollments_count }}
                        </td>

                        <td class="px-4 py-2 space-y-1">
                            <button @click="openModal(group)" class="text-blue-600 hover:underline block">
                                🔍 More details
                            </button>

                            <a :href="`/groups/${group.id}/grades`" class="text-blue-600 hover:underline block">
                                📋 Record grades
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Modal de Estudiantes -->
            <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xl relative">
                    <h2 class="text-xl font-bold mb-4">
                        {{ selectedGroup?.subject?.name }} – {{ selectedGroup.name }}
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
                            <tr v-for="(enrollment, index) in selectedGroup.subject_enrollments.slice(0, 5)"
                                :key="enrollment.id" class="border-t">
                                <td class="px-3 py-2">{{ index + 1 }}</td>
                                <td class="px-3 py-2">{{ enrollment.student.user.name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ enrollment.student.document ?? '-' }}</td>
                                <td class="px-3 py-2">{{ enrollment.student.user?.email ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-2 text-sm text-gray-500">
                        Showing {{ Math.min(selectedGroup.subject_enrollments.length, 5) }} of
                        {{ selectedGroup.subject_enrollments.length }} students.
                    </div>
                    <div class="mt-4 text-center" v-if="selectedGroup.subject_enrollments.length > 5">
                        <a :href="`/class-groups/${selectedGroup.id}/students`" class="text-blue-600 hover:underline">
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

const props = defineProps({
    groups: {
        type: Array,
        default: () => []
    }
});

onMounted(() => {
    console.log('Groups recibidos:', props.groups);
});

const showModal = ref(false);
const selectedGroup = ref(null);

function openModal(group) {
    selectedGroup.value = group;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    selectedGroup.value = null;
}
</script>
