<script setup>
import NavItem from './Nav/NavItem.vue';
import { usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
const userRole = user?.roles?.[0]?.name ?? 'guest';
</script>

<template>
    <nav class="space-y-1">
        <!-- Dashboard (común) -->
        <NavItem
            v-if="['admin', 'academic_coordinator', 'professor'].includes(userRole)"
            label="Dashboard"
            routeName="dashboard"
            :active="route().current('dashboard')"
        />

        <!-- ADMIN -->
        <template v-if="userRole === 'admin'">
            <NavItem label="Programs" routeName="programs.index" :active="route().current('programs.*')" />

            <NavItem label="Professors" routeName="professors.index" :active="route().current('professors.*')" />

            <NavItem label="Students" routeName="students.index" :active="route().current('students.*')" />

            <NavItem label="Subjects" routeName="subjects.index" :active="route().current('subjects.*')" />

            <NavItem label="Class Groups" routeName="class-groups.index" :active="route().current('class-groups.*')" />

            <NavItem label="Admin Panel" routeName="users.index" :active="route().current('users.*')" />
        </template>

        <!-- PROFESSOR -->
        <template v-else-if="userRole === 'professor'">
            <NavItem label="My Subjects" routeName="professor.subjects" />
            <NavItem label="My Schedule" routeName="professor.schedule" />
            <NavItem label="Group Enrollments" routeName="admin.group-enrollments.index" />
        </template>

        <!-- STUDENT -->
        <template v-else-if="userRole === 'student'">
            <NavItem label="My Subjects" routeName="student.subjects" />
            <NavItem label="My Schedule" routeName="student.schedule" />
            <NavItem label="Subject Enrollment" routeName="student.subject-enrollment.index" />
        </template>
    </nav>
</template>
