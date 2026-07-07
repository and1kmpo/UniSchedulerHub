<script setup>
import ApiTokenManager from '@/Pages/API/Partials/ApiTokenManager.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CrudContainer from '@/Layouts/CrudContainerLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';

defineProps({
    tokens: Array,
    availablePermissions: Array,
    defaultPermissions: Array,
});
</script>

<template>
    <AppLayout title="API Tokens">
        <div class="min-h-screen bg-slate-50 dark:bg-dark-bg">
            <CrudContainer>
                <PageHeader
                    title="API Tokens"
                    subtitle="Manage personal access tokens for TARRAYA integrations and academic API workflows."
                    class="py-8"
                />

                <ApiTokenManager
                    :tokens="tokens"
                    :available-permissions="availablePermissions"
                    :default-permissions="defaultPermissions"
                />
            </CrudContainer>
        </div>
    </AppLayout>
</template>
