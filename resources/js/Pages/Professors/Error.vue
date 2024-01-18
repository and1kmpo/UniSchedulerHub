<template>
    <div>
        <h1>Error</h1>
        <p>{{ error }}</p>
    </div>
</template>

<script setup>
const { error } = defineProps(['error']);
</script>