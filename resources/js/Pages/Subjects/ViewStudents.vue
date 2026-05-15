<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  subject: {
    type: Object,
    required: true,
  },

  students: {
    type: Object,
    required: true,
  },
});

const changePage = (page) => {
  router.get(
    route("subjects.students.view", props.subject.id),
    { page },
    {
      preserveScroll: true,
      preserveState: true,
    }
  );
};
</script>

<template>
  <AppLayout :title="`${subject.name} Students`">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ subject.name }}
          </h1>

          <p class="text-sm text-gray-500 mt-1">
            Students enrolled in this subject
          </p>
        </div>
      </div>
    </template>

    <div class="py-10">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-center">
              <thead class="bg-gray-100 border-b">
                <tr>
                  <th class="px-6 py-4 font-semibold text-gray-700">#</th>
                  <th class="px-6 py-4 font-semibold text-gray-700">Name</th>
                  <th class="px-6 py-4 font-semibold text-gray-700">Document</th>
                  <th class="px-6 py-4 font-semibold text-gray-700">Email</th>
                  <th class="px-6 py-4 font-semibold text-gray-700">Phone</th>
                  <th class="px-6 py-4 font-semibold text-gray-700">Program</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="(student, index) in students.data" :key="student.id"
                  class="border-b hover:bg-gray-50 transition">
                  <td class="px-6 py-4">
                    {{
                      (students.current_page - 1) *
                      students.per_page +
                      index +
                      1
                    }}
                  </td>

                  <td class="px-6 py-4 font-medium text-gray-800">
                    {{ student.user.name }}
                  </td>

                  <td class="px-6 py-4">
                    {{ student.document }}
                  </td>

                  <td class="px-6 py-4">
                    {{ student.user.email }}
                  </td>

                  <td class="px-6 py-4">
                    {{ student.phone }}
                  </td>

                  <td class="px-6 py-4">
                    {{ student.program?.name ?? "N/A" }}
                  </td>
                </tr>

                <tr v-if="students.data.length === 0">
                  <td colspan="6" class="px-6 py-10 text-gray-500">
                    No students enrolled.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t">
            <button :disabled="!students.prev_page_url" @click="changePage(students.current_page - 1)"
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition disabled:opacity-50">
              <i class="fa-solid fa-chevron-left"></i>
            </button>

            <span class="text-sm text-gray-600">
              Page {{ students.current_page }}
              of {{ students.last_page }}
            </span>

            <button :disabled="!students.next_page_url" @click="changePage(students.current_page + 1)"
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition disabled:opacity-50">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>