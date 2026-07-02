<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import BaseButton from '@/Components/UI/Base/BaseButton.vue';
import BaseInput from '@/Components/UI/Base/BaseInput.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm text-slate-600 dark:text-zinc-300">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-success">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <BaseInput
                id="email"
                v-model="form.email"
                label="Email"
                type="email"
                required
                autofocus
                autocomplete="username"
                :error="form.errors.email"
            />

            <div class="flex items-center justify-end mt-4">
                <BaseButton type="submit" :disabled="form.processing">
                    Email Password Reset Link
                </BaseButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
