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
        class="rounded-md border-border-light shadow-sm focus:border-brand-600 focus:ring-brand-600 dark:border-border-dark dark:bg-surface-dark dark:text-zinc-100 dark:placeholder:text-zinc-500"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        style="margin-top: 0px"
    />
</template>
