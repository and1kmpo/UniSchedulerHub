<script setup>
defineProps({
    modelValue: String,
});

defineEmits([
    "update:modelValue",
]);
</script>

<template>

    <div class="relative">

        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />

        <input :value="modelValue" type="text" placeholder="Search students..."
            class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            @input="
                $emit(
                    'update:modelValue',
                    $event.target.value
                )
                " />

    </div>

</template>