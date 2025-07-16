<script setup>
import NavItem from './Nav/NavItem.vue';
import { usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
const userRole = user?.roles?.[0]?.name ?? 'guest';
</script>

<template>
    <nav class="space-y-1">
        <!-- Dashboard (común) -->
        <NavItem label="Dashboard" routeName="dashboard" :active="route().current('dashboard')" />

        <!-- ADMIN -->
        <template v-if="userRole === 'admin'">
            <NavItem label="Programs" routeName="programs.index" :active="route().current('programs.*')" />

            <NavItem label="Professors" dropdown :dropdownItems="[
                { label: 'Index', href: 'professors.index' },
                { label: 'Assign Subjects', href: 'professors.assignSubjectForm' },
            ]" />

            <NavItem label="Students" dropdown :dropdownItems="[
                { label: 'Index', href: 'students.index' },
                { label: 'Assign Subjects', href: 'students.assignSubjectForm' },
            ]" />

            <NavItem label="Subjects" routeName="subjects.index" :active="route().current('subjects.*')" />

            <NavItem label="Admin Panel" routeName="users.index" :active="route().current('users.*')" />
        </template>

        <!-- PROFESSOR -->
        <template v-else-if="userRole === 'professor'">
            <NavItem label="My Subjects" routeName="professor.subjects" />
            <NavItem label="Students" routeName="students.index" />
        </template>

        <!-- STUDENT -->
        <template v-else-if="userRole === 'student'">
            <NavItem label="My Subjects" routeName="professor.subjects" />
            <NavItem label="Professors" routeName="professors.index" />
        </template>
    </nav>
</template>
