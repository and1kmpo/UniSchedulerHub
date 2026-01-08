<script setup>
defineProps({
    onClick: Function,
});
</script>

<template>
    <button @click="onClick"
        class="text-red-600 dark:text-red-400 hover:text-red-800 transition ease-in-out hover:-translate-y-1 hover:scale-110 duration-300"
        title="Delete">
        <i class="fas fa-trash-alt"></i>
    </button>
</template>
