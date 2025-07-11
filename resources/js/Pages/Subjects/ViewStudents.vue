<template>
  <AppLayout>
    <template #header>
      <h1 class="font-semibold text-xl text-gray-800 leading-tight">
        My Subjects
      </h1>
    </template>
    <div class="max-w-6xl mx-auto p-6">
      <h1 class="text-2xl font-bold mb-4">
        {{ subject.name }} Students
      </h1>

      <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 text-center">
            <tr>
              <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
              <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
              <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">E-mail</th>
              <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Phone number</th>
              <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>

            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 text-center">
            <tr v-for="(student, index) in students.data" :key="student.id">
              <td class="px-6 py-4 whitespace-nowrap">{{ index + 1 + (students.current_page - 1) * students.per_page }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">{{ student.user.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ student.document }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ student.user.email }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ student.phone }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ student.program?.name ?? 'N/A' }}
              </td>

            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex justify-between items-center">
        <button :disabled="!students.prev_page_url" @click="changePage(students.current_page - 1)"
          class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
          Prev
        </button>

        <p>Page {{ students.current_page }} of {{ students.last_page }}</p>

        <button :disabled="!students.next_page_url" @click="changePage(students.current_page + 1)"
          class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
          Next
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";


const props = defineProps({
  subject: Object,
  students: Object
});

const changePage = (page) => {
  router.get(`/subjects/${props.subject.id}/students`, { page }, { preserveScroll: true, preserveState: true });
};

console.log('Subject:', props.subject);
console.log('Students:', props.students);
</script>
