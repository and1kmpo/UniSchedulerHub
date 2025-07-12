<script setup>
import { useForm } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    classGroup: Object,
    subjects: Array,
    professors: Array,
});

const form = useForm({
    code: props.classGroup?.code || "",
    name: props.classGroup?.name || "",
    subject_id: props.classGroup?.subject_id || "",
    professor_id: props.classGroup?.professor_id || "",
    capacity: props.classGroup?.capacity || 30,
});

function submit() {
    if (props.classGroup) {
        form.put(route("class-groups.update", props.classGroup.id));
    } else {
        form.post(route("class-groups.store"));
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded shadow">
        <div>
            <label class="block text-sm font-medium">Code</label>
            <input v-model="form.code" class="input" />
            <div class="text-red-500 text-sm" v-if="form.errors.code">{{ form.errors.code }}</div>
        </div>

        <div>
            <label class="block text-sm font-medium">Name</label>
            <input v-model="form.name" class="input" />
            <div class="text-red-500 text-sm" v-if="form.errors.name">{{ form.errors.name }}</div>
        </div>

        <div>
            <label class="block text-sm font-medium">Subject</label>
            <select v-model="form.subject_id" class="input">
                <option disabled value="">Select subject</option>
                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
            <div class="text-red-500 text-sm" v-if="form.errors.subject_id">{{ form.errors.subject_id }}</div>
        </div>

        <div>
            <label class="block text-sm font-medium">Professor</label>
            <select v-model="form.professor_id" class="input">
                <option disabled value="">Select professor</option>
                <option v-for="p in professors" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <div class="text-red-500 text-sm" v-if="form.errors.professor_id">{{ form.errors.professor_id }}</div>
        </div>

        <div>
            <label class="block text-sm font-medium">Capacity</label>
            <input type="number" min="1" v-model="form.capacity" class="input" />
            <div class="text-red-500 text-sm" v-if="form.errors.capacity">{{ form.errors.capacity }}</div>
        </div>

        <button type="submit" class="btn-indigo">
            {{ props.classGroup ? "Update" : "Create" }}
        </button>
    </form>
</template>
