<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { useAlert } from "@/Components/Composables/useAlert";
import { route } from "ziggy-js";

const { confirm, success, error } = useAlert();

defineProps({
    subjects: {
        type: Object,
        required: true,
    },
});

const deleteSubject = async (id, name) => {

    const confirmed = await confirm(
        `This will permanently delete "${name}"`,
        "Delete Subject"
    );

    if (!confirmed) return;

    const url = route("subjects.destroy", { subject: id });

    router.delete(url, {

        preserveScroll: true,

        onSuccess: (page) => {

            if (page.props.flash.success) {
                success(page.props.flash.success);
            }
        },

        onError: (errors) => {

            console.error(errors);

            error(
                errors.message ||
                "Failed to delete subject"
            );
        },
    });
};
</script>

<template>
    <AppLayout title="Subjects">
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="font-semibold text-2xl text-gray-800">
                    Subjects
                </h1>

                <Link :href="route('subjects.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                    Create Subject
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow rounded-xl overflow-hidden">

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-center">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Subject</th>
                                    <th class="px-4 py-3">Description</th>
                                    <th class="px-4 py-3">Credits</th>
                                    <th class="px-4 py-3">Knowledge Area</th>
                                    <th class="px-4 py-3">Elective</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="(subject, i) in subjects.data" :key="subject.id"
                                    class="border-b hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        {{
                                            (subjects.current_page - 1) *
                                            subjects.per_page +
                                            i +
                                            1
                                        }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        {{ subject.name }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ subject.description }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ subject.credits }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ subject.knowledge_area }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="subject.elective
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-700'
                                            ">
                                            {{ subject.elective ? "YES" : "NO" }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-3">
                                            <Link :href="route('subjects.edit', subject.id)"
                                                class="text-indigo-600 hover:text-indigo-800">
                                                <i class="fas fa-edit"></i>
                                            </Link>

                                            <button @click="deleteSubject(subject.id, subject.name)"
                                                class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="subjects.data.length === 0">
                                    <td colspan="7" class="py-6 text-gray-500">
                                        No subjects found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-center gap-4 p-6">
                        <Link v-if="subjects.prev_page_url" :href="subjects.prev_page_url"
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                            Prev
                        </Link>

                        <span class="text-sm text-gray-600">
                            Page {{ subjects.current_page }}
                            of {{ subjects.last_page }}
                        </span>

                        <Link v-if="subjects.next_page_url" :href="subjects.next_page_url"
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                            Next
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
