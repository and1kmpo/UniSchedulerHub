<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import BaseButton from '@/Components/UI/Base/BaseButton.vue';
import BaseCheckbox from '@/Components/UI/Base/BaseCheckbox.vue';
import BaseInput from '@/Components/UI/Base/BaseInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onSuccess: () => window.location.reload(),
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-600 dark:text-emerald-400">
            {{ status }}
        </div>

        <div
            class="mb-6 border-l-2 border-brand-600 bg-brand-50/70 px-4 py-3 dark:bg-brand-950/20"
        >
            <p class="text-sm font-semibold text-ink dark:text-white">
                Welcome to TARRAYA
            </p>
            <p class="mt-1 font-mono text-xs text-slate-600 dark:text-zinc-400">
                The operational network for your institution.
            </p>
        </div>

        <form @submit.prevent="submit" class="text-slate-900 dark:text-zinc-100">
            <BaseInput id="email" v-model="form.email" label="Email" type="email" required autofocus
                autocomplete="username" :error="form.errors.email" />

            <BaseInput id="password" v-model="form.password" label="Password" type="password" class="mt-4" required
                autocomplete="current-password" :error="form.errors.password" />

            <BaseCheckbox v-model="form.remember" class="mt-4" description="Remember me" />

            <div class="flex items-center justify-end mt-4">
                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="rounded-md font-mono text-xs text-slate-600 underline hover:text-ink focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 dark:text-zinc-400 dark:hover:text-white dark:focus:ring-offset-dark-bg">
                    Forgot your password?
                </Link>

                <BaseButton type="submit" class="ms-4" :disabled="form.processing">
                    Log in
                </BaseButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
