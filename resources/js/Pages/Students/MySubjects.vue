<template>
    <AppLayout>
        <template #header>
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                My Subjects
            </h1>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <button @click="handleOpenSummary"
                class="mb-4 inline-flex items-center gap-2 text-sm text-green-700 dark:text-green-400 hover:underline focus:outline-none"
                aria-label="View summary of all grades">
                <i class="fas fa-list-alt"></i> View All Grades
            </button>

            <div v-if="subjects.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="subject in subjects" :key="subject.id"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:shadow-md transition duration-200">
                    <h2 class="text-lg font-bold text-blue-700 dark:text-blue-400 flex items-center gap-2">
                        <i class="fas fa-book text-blue-500"></i> {{ subject.name }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 mt-1">
                        <span class="font-medium text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-user text-gray-400 mr-1"></i> Professor:
                        </span>
                        {{ subject.professor_name ?? 'Not assigned' }}
                    </p>

                    <button @click="viewGrades(subject)"
                        class="mt-4 inline-flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        <i class="fas fa-eye"></i> View Grades
                    </button>
                </div>
            </div>

            <div v-else class="text-center text-gray-500 dark:text-gray-400 mt-12">
                <i class="fas fa-info-circle text-3xl mb-3"></i>
                <p>No subjects assigned yet. Please check with your program coordinator.</p>
            </div>
        </div>

        <!-- Modal: Subject Grades -->
        <transition name="fade">
            <div v-if="isModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity"
                role="dialog" aria-label="Subject grades modal">
                <div
                    class="bg-white dark:bg-gray-900 dark:text-gray-200 rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative animate-fade-in">
                    <button @click="closeModal"
                        class="absolute top-2 right-2 text-gray-600 dark:text-gray-300 hover:text-red-500"
                        aria-label="Close grades modal">
                        <i class="fas fa-times text-lg"></i>
                    </button>

                    <h2 class="text-lg font-bold mb-4 text-blue-700 dark:text-blue-300">
                        Grades for {{ selectedSubject?.name }}
                    </h2>

                    <ul class="space-y-2 text-sm">
                        <li><strong>Partial 1:</strong> {{ selectedGrade?.partial_1 ?? 'Not yet registered' }}</li>
                        <li><strong>Partial 2:</strong> {{ selectedGrade?.partial_2 ?? 'Not yet registered' }}</li>
                        <li><strong>Partial 3:</strong> {{ selectedGrade?.partial_3 ?? 'Not yet registered' }}</li>
                        <li><strong>Activities:</strong> {{ selectedGrade?.activities ?? 'Not yet registered' }}</li>
                        <li><strong>Final grade:</strong> {{ selectedGrade?.final_grade ?? '—' }}</li>
                        <li>
                            <strong>Status:</strong>
                            <span class="ml-1" :class="{
                                'text-green-600': selectedGrade?.state?.code === 'passed',
                                'text-red-600': selectedGrade?.state?.code === 'failed',
                                'text-yellow-500': selectedGrade?.state?.code === 'failed_attendance',
                                'text-gray-500': !selectedGrade?.state
                            }">
                                <i v-if="selectedGrade?.state?.code === 'passed'" class="fas fa-check-circle"></i>
                                <i v-else-if="selectedGrade?.state?.code === 'failed_attendance'"
                                    class="fas fa-exclamation-triangle"></i>
                                <i v-else-if="selectedGrade?.state?.code === 'failed'" class="fas fa-times-circle"></i>
                                <i v-else class="fas fa-clock"></i>
                                {{ selectedGrade?.state?.label ?? 'Pending' }}
                            </span>
                        </li>

                    </ul>
                </div>
            </div>
        </transition>

        <!-- Modal: Grades Summary -->
        <transition name="fade">
            <div v-if="showSummary"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity"
                role="dialog" aria-label="Grades summary modal">
                <div
                    class="bg-white dark:bg-gray-900 dark:text-gray-200 rounded-lg shadow-lg w-full max-w-6xl mx-4 p-6 relative animate-fade-in overflow-x-auto">
                    <button @click="showSummary = false"
                        class="absolute top-2 right-2 text-gray-600 dark:text-gray-300 hover:text-red-500"
                        aria-label="Close summary modal">
                        <i class="fas fa-times"></i>
                    </button>

                    <h2 class="text-lg font-bold mb-4 text-green-700 dark:text-green-400">
                        Summary of Grades
                    </h2>

                    <table class="min-w-full text-sm text-center border border-gray-200 dark:border-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="p-2">Subject</th>
                                <th class="p-2">P1</th>
                                <th class="p-2">P2</th>
                                <th class="p-2">P3</th>
                                <th class="p-2">Activities</th>
                                <th class="p-2">Final</th>
                                <th class="p-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="g in allGrades" :key="g.subject.id"
                                class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <td class="p-2">{{ g.subject.name }}</td>
                                <td class="p-2">
                                    <span v-if="g.partial_1">{{ g.partial_1 }}</span>
                                    <i v-else class="fas fa-clock text-gray-400"></i>
                                </td>
                                <td class="p-2">
                                    <span v-if="g.partial_2">{{ g.partial_2 }}</span>
                                    <i v-else class="fas fa-clock text-gray-400"></i>
                                </td>
                                <td class="p-2">
                                    <span v-if="g.partial_3">{{ g.partial_3 }}</span>
                                    <i v-else class="fas fa-clock text-gray-400"></i>
                                </td>
                                <td class="p-2">
                                    <span v-if="g.activities">{{ g.activities }}</span>
                                    <i v-else class="fas fa-clock text-gray-400"></i>
                                </td>
                                <td class="p-2">
                                    <span v-if="g.final_grade">{{ g.final_grade }}</span>
                                    <i v-else class="fas fa-clock text-gray-400"></i>
                                </td>
                                <td class="p-2">
                                    <span :class="{
                                        'text-green-600': g.state?.code === 'passed',
                                        'text-red-600': g.state?.code === 'failed',
                                        'text-yellow-500': g.state?.code === 'failed_attendance',
                                        'text-gray-500': !g.state
                                    }">
                                        <i v-if="g.state?.code === 'passed'" class="fas fa-check-circle"></i>
                                        <i v-else-if="g.state?.code === 'failed_attendance'"
                                            class="fas fa-exclamation-triangle"></i>
                                        <i v-else-if="g.state?.code === 'failed'" class="fas fa-times-circle"></i>
                                        <i v-else class="fas fa-clock"></i>
                                        {{ g.state?.label ?? 'Pending' }}
                                    </span>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({ subjects: Array });

const selectedGrade = ref(null);
const selectedSubject = ref(null);
const isModalOpen = ref(false);
const showSummary = ref(false);
const allGrades = ref([]);

const viewGrades = async (subject) => {
    isModalOpen.value = false;
    setTimeout(async () => {
        selectedSubject.value = subject;
        try {
            const response = await axios.get(route('student.subject.grades.json', subject.id));
            selectedGrade.value = response.data.grade;
        } catch (error) {
            console.error('Error loading grades', error);
            alert('Could not load grades.');
        } finally {
            isModalOpen.value = true;
        }
    }, 100);
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedGrade.value = null;
    selectedSubject.value = null;
};

const loadAllGrades = async () => {
    try {
        const response = await axios.get(route('student.grades.summary'));
        allGrades.value = response.data.grades;
    } catch (e) {
        console.error('Error loading summary', e);
        alert('Could not load summary.');
    }
};

const handleOpenSummary = async () => {
    await loadAllGrades();
    showSummary.value = true;
};
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: scale(0.97);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
