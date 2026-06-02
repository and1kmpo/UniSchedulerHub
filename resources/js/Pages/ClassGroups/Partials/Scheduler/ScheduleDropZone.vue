<script setup>
import { ref } from "vue";

import { useDragScheduler } from "./useDragScheduler";

const props = defineProps({
    day: String,
    hour: String,
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "drop",
]);

const hovering = ref(false);

const {
    draggedSchedule,
} = useDragScheduler();

const onDrop = () => {
    if (props.disabled) {
        return;
    }

    hovering.value = false;

    if (!draggedSchedule.value) {
        return;
    }

    emit(
        "drop",
        {
            schedule: draggedSchedule.value,
            day: props.day,
            hour: props.hour,
        }
    );
};
</script>

<template>

    <div @dragover.prevent="!disabled && (hovering = true)" @dragleave="hovering = false" @drop="onDrop" class="
            absolute
            inset-0
            transition
        " :class="[
            hovering
                ? 'bg-indigo-200/50 dark:bg-indigo-500/20'
                : ''
        ]" />

</template>
