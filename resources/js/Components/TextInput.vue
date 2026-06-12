<script setup>
import { onMounted, ref } from "vue";

defineProps({
    modelValue: String,
});

defineEmits(["update:modelValue"]);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] dark:border-zinc-800 dark:bg-zinc-950 dark:text-gray-100 dark:placeholder:text-gray-500"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        style="margin-top: 0px"
    />
</template>
