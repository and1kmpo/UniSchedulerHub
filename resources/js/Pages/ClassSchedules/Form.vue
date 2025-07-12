<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    classGroup: Object,
    schedule: Object,
});

const form = useForm({
    day: props.schedule?.day || '',
    start_time: props.schedule?.start_time || '',
    end_time: props.schedule?.end_time || '',
    classroom: props.schedule?.classroom || '',
});

function submit() {
    if (props.schedule) {
        form.put(route('class-schedules.update', [props.classGroup.id, props.schedule.id]));
    } else {
        form.post(route('class-schedules.store', props.classGroup.id));
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded shadow">

        <div>
            <label class="block text-sm font-medium">Day</label>
            <select v-model="form.day" class="input">
                <option value="">Select day</option>
                <option value="monday">Monday</option>
                <option value="tuesday">Tuesday</option>
                <option value="wednesday">Wednesday</option>
                <option value="thursday">Thursday</option>
                <option value="friday">Friday</option>
                <option value="saturday">Saturday</option>
            </select>
            <div class="text-red-500 text-sm" v-if="form.errors.day">{{ form.errors.day }}</div>
        </div>

        <div>
            <label class="block text-sm font-medium">Start Time</label>
            <input type="time" v-model="form.start_time" class="input" />
            <div class="text-red-500 text-sm" v-if="form.errors.start_time">{{ form.errors.start_time }}</div>
        </div>

        <div>
            <label class="block text-sm font-medium">End Time</label>
            <input type="time" v-model="form.end_time" class="input" />
            <div class="text-red-500 text-sm" v-if="form.errors.end_time">{{ form.errors.end_time }}</div>
        </div>

        <div>
            <label class="block text-sm font-medium">Classroom</label>
            <input v-model="form.classroom" class="input" />
        </div>

        <button type="submit" class="btn-indigo">
            {{ props.schedule ? 'Update' : 'Create' }}
        </button>
    </form>
</template>
