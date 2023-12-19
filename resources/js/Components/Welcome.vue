<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
</script>

<template>
    <div>
        <button v-if="$page.props.user.permissions.includes('create roles')">
            Manage roles
        </button>
        <button
            v-if="$page.props.user.permissions.includes('create professors')"
        >
            Manage professors
        </button>
    </div>
</template>

<style>
button {
    background-color: #6e76f2;
    color: rgb(0, 0, 0);
    padding: 20px;
    margin: 5px;
}
</style>
