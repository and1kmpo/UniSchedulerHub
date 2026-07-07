<script setup>
import debounce from "lodash.debounce";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },

    placeholder: {
        type: String,
        default: "Search...",
    },

    delay: {
        type: Number,
        default: 400,
    },
});

const emit = defineEmits([
    "update:modelValue",
]);

const updateValue = debounce((value) => {
    emit("update:modelValue", value);
}, props.delay);
</script>

<template>
    <div class="relative">

        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-zinc-500" />

        <input :value="modelValue" type="text" :placeholder="placeholder" @input="updateValue($event.target.value)"
            class="w-full rounded-lg border border-border-light bg-surface py-2.5 pl-10 pr-4 text-sm text-ink transition placeholder:text-slate-400 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-100 dark:placeholder:text-zinc-500" />

    </div>
</template>
