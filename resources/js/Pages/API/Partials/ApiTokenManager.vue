<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import ActionSection from '@/Components/ActionSection.vue';
import BaseButton from '@/Components/UI/Base/BaseButton.vue';
import BaseInput from '@/Components/UI/Base/BaseInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DialogModal from '@/Components/DialogModal.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SectionBorder from '@/Components/SectionBorder.vue';

const props = defineProps({
    tokens: Array,
    availablePermissions: Array,
    defaultPermissions: Array,
});

const createApiTokenForm = useForm({
    name: '',
    permissions: props.defaultPermissions,
});

const updateApiTokenForm = useForm({
    permissions: [],
});

const deleteApiTokenForm = useForm({});

const displayingToken = ref(false);
const managingPermissionsFor = ref(null);
const apiTokenBeingDeleted = ref(null);

const createApiToken = () => {
    createApiTokenForm.post(route('api-tokens.store'), {
        preserveScroll: true,
        onSuccess: () => {
            displayingToken.value = true;
            createApiTokenForm.reset();
        },
    });
};

const manageApiTokenPermissions = (token) => {
    updateApiTokenForm.permissions = token.abilities;
    managingPermissionsFor.value = token;
};

const updateApiToken = () => {
    updateApiTokenForm.put(route('api-tokens.update', managingPermissionsFor.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => (managingPermissionsFor.value = null),
    });
};

const confirmApiTokenDeletion = (token) => {
    apiTokenBeingDeleted.value = token;
};

const deleteApiToken = () => {
    deleteApiTokenForm.delete(route('api-tokens.destroy', apiTokenBeingDeleted.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => (apiTokenBeingDeleted.value = null),
    });
};
</script>

<template>
    <div>
        <!-- Generate API Token -->
        <FormSection @submitted="createApiToken">
            <template #title>
                Create API Token
            </template>

            <template #description>
                Create scoped personal tokens for integrations that need access to the TARRAYA academic API.
            </template>

            <template #form>
                <!-- Token Name -->
                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="name" value="Name" />
                    <BaseInput
                        id="name"
                        v-model="createApiTokenForm.name"
                        type="text"
                        class="mt-1 block w-full"
                        autofocus
                    />
                    <InputError :message="createApiTokenForm.errors.name" class="mt-2" />
                </div>

                <!-- Token Permissions -->
                <div v-if="availablePermissions.length > 0" class="col-span-6">
                    <InputLabel for="permissions" value="Permissions" />

                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="permission in availablePermissions" :key="permission">
                            <label class="flex min-h-11 items-center rounded-lg border border-border-light bg-surface px-3 py-2 dark:border-border-dark dark:bg-surface-dark">
                                <Checkbox v-model:checked="createApiTokenForm.permissions" :value="permission" />
                                <span class="ms-2 font-mono text-xs text-slate-600 dark:text-zinc-300">{{ permission }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </template>

            <template #actions>
                <ActionMessage :on="createApiTokenForm.recentlySuccessful" class="me-3">
                    Created.
                </ActionMessage>

                <BaseButton :disabled="createApiTokenForm.processing">
                    <i class="fa-solid fa-key mr-2" />
                    Create
                </BaseButton>
            </template>
        </FormSection>

        <div v-if="tokens.length > 0">
            <SectionBorder />

            <!-- Manage API Tokens -->
            <div class="mt-10 sm:mt-0">
                <ActionSection>
                    <template #title>
                        Manage API Tokens
                    </template>

                    <template #description>
                        Revoke unused tokens and keep integration access aligned with operational needs.
                    </template>

                    <!-- API Token List -->
                    <template #content>
                        <div class="space-y-6">
                            <div v-for="token in tokens" :key="token.id" class="flex flex-col gap-3 rounded-lg border border-border-light bg-surface p-4 dark:border-border-dark dark:bg-surface-dark sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="break-all font-medium text-ink dark:text-white">
                                        {{ token.name }}
                                    </div>
                                    <div v-if="token.last_used_ago" class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                                        Last used {{ token.last_used_ago }}
                                    </div>
                                    <div v-else class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                                        Not used yet
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <BaseButton
                                        v-if="availablePermissions.length > 0"
                                        variant="secondary"
                                        size="sm"
                                        @click="manageApiTokenPermissions(token)"
                                    >
                                        <i class="fa-solid fa-sliders mr-2" />
                                        Permissions
                                    </BaseButton>

                                    <BaseButton variant="danger" size="sm" @click="confirmApiTokenDeletion(token)">
                                        <i class="fa-solid fa-trash mr-2" />
                                        Delete
                                    </BaseButton>
                                </div>
                            </div>
                        </div>
                    </template>
                </ActionSection>
            </div>
        </div>

        <!-- Token Value Modal -->
        <DialogModal :show="displayingToken" @close="displayingToken = false">
            <template #title>
                API Token
            </template>

            <template #content>
                <div>
                    Please copy your new API token. For your security, it won't be shown again.
                </div>

                <div v-if="$page.props.jetstream.flash.token" class="mt-4 break-all rounded-lg border border-border-light bg-slate-50 px-4 py-3 font-mono text-sm text-slate-700 dark:border-border-dark dark:bg-dark-bg dark:text-zinc-200">
                    {{ $page.props.jetstream.flash.token }}
                </div>
            </template>

            <template #footer>
                <BaseButton variant="secondary" @click="displayingToken = false">
                    Close
                </BaseButton>
            </template>
        </DialogModal>

        <!-- API Token Permissions Modal -->
        <DialogModal :show="managingPermissionsFor != null" @close="managingPermissionsFor = null">
            <template #title>
                API Token Permissions
            </template>

            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="permission in availablePermissions" :key="permission">
                        <label class="flex min-h-11 items-center rounded-lg border border-border-light bg-surface px-3 py-2 dark:border-border-dark dark:bg-surface-dark">
                            <Checkbox v-model:checked="updateApiTokenForm.permissions" :value="permission" />
                            <span class="ms-2 font-mono text-xs text-slate-600 dark:text-zinc-300">{{ permission }}</span>
                        </label>
                    </div>
                </div>
            </template>

            <template #footer>
                <BaseButton variant="secondary" @click="managingPermissionsFor = null">
                    Cancel
                </BaseButton>

                <BaseButton
                    class="ms-3"
                    :disabled="updateApiTokenForm.processing"
                    @click="updateApiToken"
                >
                    Save
                </BaseButton>
            </template>
        </DialogModal>

        <!-- Delete Token Confirmation Modal -->
        <ConfirmationModal :show="apiTokenBeingDeleted != null" @close="apiTokenBeingDeleted = null">
            <template #title>
                Delete API Token
            </template>

            <template #content>
                Are you sure you would like to delete this API token?
            </template>

            <template #footer>
                <BaseButton variant="secondary" @click="apiTokenBeingDeleted = null">
                    Cancel
                </BaseButton>

                <BaseButton
                    variant="danger"
                    class="ms-3"
                    :disabled="deleteApiTokenForm.processing"
                    @click="deleteApiToken"
                >
                    Delete
                </BaseButton>
            </template>
        </ConfirmationModal>
    </div>
</template>
