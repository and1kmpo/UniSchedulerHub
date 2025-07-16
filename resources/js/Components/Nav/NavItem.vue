<!-- resources/js/Components/Navigation/NavItem.vue -->
<script setup>
import NavLink from '@/Components/NavLink.vue'

defineProps({
    label: String,
    href: String,
    active: Boolean,
});
</script>

<template>
    <NavLink :href="href" :active="active">
        {{ label }}
    </NavLink>
</template>
