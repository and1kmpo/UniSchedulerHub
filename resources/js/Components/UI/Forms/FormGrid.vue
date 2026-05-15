<script setup>
defineProps({
    cols: {
        type: Number,
        default: 2,
    },
});
</script>

<template>
    <div class="grid grid-cols-1 gap-6" :class="{
        'md:grid-cols-2': cols === 2,
        'md:grid-cols-3': cols === 3,
        'md:grid-cols-4': cols === 4,
    }">
        <slot />
    </div>
</template>