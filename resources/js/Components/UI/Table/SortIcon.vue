<script setup>
defineProps({
    active: {
        type: Boolean,
        default: false,
    },

    direction: {
        type: String,
        default: "asc",
    },
});
</script>

<template>
    <div class="ml-2 flex items-center">

        <i v-if="active && direction === 'asc'" class="fa-solid fa-arrow-up text-xs" />

        <i v-else-if="active && direction === 'desc'" class="fa-solid fa-arrow-down text-xs" />

        <i v-else class="fa-solid fa-sort text-xs opacity-40" />

    </div>
</template>