<script setup>
defineProps({
    message: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <p v-if="message" class="text-sm text-danger">
        {{ message }}
    </p>
</template>
