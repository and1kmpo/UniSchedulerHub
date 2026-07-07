<script setup>
defineProps({
    onClick: Function,
});
</script>

<template>
    <button @click="onClick"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-danger transition duration-200 hover:bg-danger/10 hover:text-red-700 dark:text-red-300 dark:hover:bg-danger/15"
        title="Delete">
        <i class="fas fa-trash-alt"></i>
    </button>
</template>
