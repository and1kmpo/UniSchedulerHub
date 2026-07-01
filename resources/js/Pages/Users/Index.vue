<script setup>
import { computed, reactive, ref, watch } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import { useAlert } from "@/Components/Composables/useAlert";
import { formatDate } from "@/Components/Composables/useDateTimeFormatter";

import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import CrudContainer from "@/Layouts/CrudContainerLayout.vue";

import TableToolbar from "@/Components/UI/Table/TableToolbar.vue";
import TableSearch from "@/Components/UI/Table/TableSearch.vue";
import DataTable from "@/Components/UI/Table/DataTable.vue";
import TableActionButton from "@/Components/UI/Table/TableActionButton.vue";
import TablePagination from "@/Components/UI/Table/TablePagination.vue";

import EmptyState from "@/Components/UI/Feedback/EmptyState.vue";
import StatCard from "@/Components/UI/Feedback/StatCard.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseInput from "@/Components/UI/Base/BaseInput.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import StatusBadge from "@/Components/UI/Badges/StatusBadge.vue";

const { confirm, success, error } = useAlert();

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    roles: {
        type: Array,
        default: () => [],
    },
    identityRoleOptions: {
        type: Array,
        default: () => [],
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
    metrics: {
        type: Object,
        default: () => ({
            users: 0,
            active: 0,
            inactive: 0,
            roles: 0,
            academicProfiles: 0,
        }),
    },
});

const columns = [
    { key: "name", label: "User", sortable: true },
    { key: "email", label: "Email", sortable: true },
    { key: "roles", label: "Roles" },
    { key: "profile", label: "Academic profile" },
    { key: "status", label: "Status", sortable: true },
    { key: "created_at", label: "Created", sortable: true },
];

const filterForm = reactive({
    search: props.filters.search || "",
    role: props.filters.role || "",
    status: props.filters.status || "",
});

const isModalOpen = ref(false);
const processing = ref(false);
const formErrors = ref({});

const form = reactive({
    id: null,
    name: "",
    email: "",
    role: "",
});

const roleOptions = computed(() => props.roles);
const identityRoleOptions = computed(() => props.identityRoleOptions);
const isEditingAcademicAccount = computed(() => ["student", "professor"].includes(form.role));

const rows = computed(() =>
    props.users.data.map((user) => {
        const roles = user.roles?.map((role) => role.name) ?? [];
        const profile = user.student
            ? `Student · Semester ${user.student.semester ?? "N/A"}`
            : user.professor
                ? "Professor"
                : "Operational user";

        return {
            ...user,
            roles,
            primary_role: roles[0] ?? "No role",
            profile,
            status_label: user.status === "1" ? "Active" : "Inactive",
            status_variant: user.status === "1" ? "success" : "gray",
        };
    })
);

watch(
    () => ({ ...filterForm }),
    () => {
        router.get(
            route("users.index"),
            {
                search: filterForm.search,
                role: filterForm.role,
                status: filterForm.status,
                sort: props.filters?.sort,
                direction: props.filters?.direction,
                page: 1,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    },
    { deep: true }
);

const resetForm = () => {
    Object.assign(form, {
        id: null,
        name: "",
        email: "",
        role: "",
    });
    formErrors.value = {};
};

const openCreateModal = () => {
    resetForm();
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    resetForm();

    Object.assign(form, {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.primary_role === "No role" ? "" : user.primary_role,
    });

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    resetForm();
};

const clearFilters = () => {
    filterForm.search = "";
    filterForm.role = "";
    filterForm.status = "";
};

const saveUser = async () => {
    processing.value = true;
    formErrors.value = {};

    try {
        if (form.id) {
            await axios.put(route("users.update", form.id), form);
            success("User updated successfully");
        } else {
            await axios.post(route("users.store"), form);
            success("User created successfully");
        }

        closeModal();
        router.reload({ only: ["users"] });
    } catch (exception) {
        if (exception.response?.status === 422) {
            formErrors.value = exception.response.data.errors ?? {};
            error(exception.response.data.message ?? "Please review the highlighted fields");
        } else {
            error("The user could not be saved");
        }
    } finally {
        processing.value = false;
    }
};

const toggleStatus = async (user) => {
    const action = user.status === "1" ? "deactivate" : "activate";
    const confirmed = await confirm(
        `This will ${action} "${user.name}" access.`,
        `${action.charAt(0).toUpperCase()}${action.slice(1)} user`
    );

    if (!confirmed) return;

    try {
        await axios.patch(route(`users.${action}`, user.id));
        success(`User ${action}d successfully`);
        router.reload({ only: ["users"] });
    } catch {
        error("The user status could not be updated");
    }
};

const deleteUser = async (user) => {
    const confirmed = await confirm(
        `This will permanently delete "${user.name}". Prefer deactivation when the account has academic history.`,
        "Delete User"
    );

    if (!confirmed) return;

    try {
        await axios.delete(route("users.destroy", user.id));
        success("User deleted successfully");
        router.reload({ only: ["users"] });
    } catch (exception) {
        error(exception.response?.data?.message ?? "The user could not be deleted");
    }
};

const roleLabel = (role) => role.replaceAll("_", " ").replace(/\b\w/g, (letter) => letter.toUpperCase());
const firstError = (field) => formErrors.value[field]?.[0] ?? "";
</script>

<template>
    <CrudPageLayout title="Identity & Access" subtitle="Manage login accounts, roles and institutional access">
        <template #actions>
            <BaseButton variant="primary" @click="openCreateModal">
                <i class="fa-solid fa-plus mr-2"></i>
                Create Access Account
            </BaseButton>
        </template>

        <CrudContainer>
            <div class="grid gap-3 border-b border-border-light bg-surface p-4 dark:border-border-dark dark:bg-surface-dark sm:grid-cols-2 xl:grid-cols-4">
                <StatCard title="Accounts" :value="metrics.users" icon="fa-solid fa-users-gear" />
                <StatCard title="Active Access" :value="metrics.active" icon="fa-solid fa-user-check" />
                <StatCard title="Blocked Access" :value="metrics.inactive" icon="fa-solid fa-user-lock" />
                <StatCard title="Academic Profiles" :value="metrics.academicProfiles" icon="fa-solid fa-id-card-clip" />
            </div>

            <div class="border-b border-border-light bg-brand/5 px-4 py-4 dark:border-border-dark dark:bg-brand/10 sm:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-ink dark:text-white">
                            Identity & Access is for technical account governance.
                        </p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-zinc-400">
                            Create admins and academic coordinators here. Create students and professors from their academic modules so their institutional profiles stay complete.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <StatusBadge label="Admin creates coordinators" variant="brand" />
                        <StatusBadge label="Academic profiles stay in Core" variant="gray" />
                    </div>
                </div>
            </div>

            <TableToolbar>
                <template #search>
                    <div class="w-full lg:max-w-sm">
                        <TableSearch v-model="filterForm.search" placeholder="Search users..." />
                    </div>
                </template>

                <template #filters>
                    <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2 lg:max-w-xl">
                        <BaseSelect v-model="filterForm.role" placeholder="Role" :options="roleOptions" />
                        <BaseSelect v-model="filterForm.status" placeholder="Status" :options="statusOptions" />
                    </div>
                </template>

                <template #actions>
                    <BaseButton variant="secondary" @click="clearFilters">
                        <i class="fa-solid fa-rotate-left mr-2"></i>
                        Reset
                    </BaseButton>
                </template>
            </TableToolbar>

            <DataTable v-if="rows.length" :columns="columns" :rows="rows" :filters="filters" sortable>
                <template #cell-name="{ row }">
                    <div>
                        <p class="font-semibold text-ink dark:text-white">{{ row.name }}</p>
                        <p class="font-mono text-xs text-slate-500 dark:text-zinc-400">ID {{ row.id }}</p>
                    </div>
                </template>

                <template #cell-roles="{ value }">
                    <div class="flex flex-wrap gap-2">
                        <StatusBadge
                            v-for="role in value"
                            :key="role"
                            :label="roleLabel(role)"
                            :variant="role === 'admin' ? 'danger' : role === 'academic_coordinator' ? 'warning' : 'gray'"
                        />
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <StatusBadge :label="row.status_label" :variant="row.status_variant" />
                </template>

                <template #cell-created_at="{ value }">
                    <StatusBadge :label="formatDate(value)" variant="gray" />
                </template>

                <template #actions="{ row }">
                    <div class="flex items-center justify-center gap-2">
                        <TableActionButton icon="fa-solid fa-pen" label="Edit user" color="brand" @click="openEditModal(row)" />
                        <TableActionButton
                            :icon="row.status === '1' ? 'fa-solid fa-user-slash' : 'fa-solid fa-user-check'"
                            :label="row.status === '1' ? 'Deactivate user' : 'Activate user'"
                            color="sky"
                            @click="toggleStatus(row)"
                        />
                        <TableActionButton icon="fa-solid fa-trash" label="Delete user" color="red" @click="deleteUser(row)" />
                    </div>
                </template>
            </DataTable>

            <EmptyState
                v-else
                title="No users found"
                description="Create the first login account or adjust the current filters."
                icon="fa-solid fa-users-gear"
            >
                <BaseButton variant="primary" @click="openCreateModal">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Create Access Account
                </BaseButton>
            </EmptyState>

            <TablePagination v-if="rows.length" :data="users" />
        </CrudContainer>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg border border-border-light bg-surface shadow-sm dark:border-border-dark dark:bg-surface-dark">
                <div class="flex items-start justify-between border-b border-border-light p-5 dark:border-border-dark">
                    <div>
                        <h2 class="text-lg font-semibold text-ink dark:text-white">
                            {{ form.id ? "Edit User" : "Create User" }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-zinc-400">
                            Manage account access. Academic profile data is maintained from Students and Professors.
                        </p>
                    </div>
                    <button class="text-slate-400 hover:text-ink dark:hover:text-white" @click="closeModal">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form class="space-y-5 p-5" @submit.prevent="saveUser">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <BaseInput v-model="form.name" label="Name" required :error="firstError('name')" />
                        <BaseInput v-model="form.email" label="Email" type="email" required :error="firstError('email')" />
                        <BaseSelect
                            v-model="form.role"
                            label="Role"
                            required
                            placeholder="Select a role"
                            :options="form.id && isEditingAcademicAccount ? roleOptions : identityRoleOptions"
                            :disabled="form.id && isEditingAcademicAccount"
                            :error="firstError('role')"
                        />
                    </div>

                    <div v-if="form.id && isEditingAcademicAccount" class="rounded-lg border border-warning/30 bg-warning/10 p-4 text-sm text-amber-800 dark:bg-warning/15 dark:text-amber-200">
                        This account is connected to an academic profile. Edit student or professor details from the corresponding Core module; use this screen only for access status and account identity.
                    </div>

                    <div v-else-if="form.role" class="rounded-lg border border-brand/20 bg-brand/10 p-4 text-sm text-brand dark:bg-brand/15 dark:text-brand-200">
                        This role is operational. Students and professors must be created from their academic modules to keep curriculum, document and institutional profile data consistent.
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-border-light pt-5 dark:border-border-dark sm:flex-row sm:justify-end">
                        <BaseButton variant="secondary" @click="closeModal">
                            Cancel
                        </BaseButton>
                        <BaseButton type="submit" variant="primary" :disabled="processing">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>
                            {{ processing ? "Saving..." : "Save User" }}
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </CrudPageLayout>
</template>


