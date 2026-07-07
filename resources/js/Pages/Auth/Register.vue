<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import BaseButton from '@/Components/UI/Base/BaseButton.vue';
import BaseCheckbox from '@/Components/UI/Base/BaseCheckbox.vue';
import BaseInput from '@/Components/UI/Base/BaseInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <BaseInput id="name" v-model="form.name" label="Name" type="text" required autofocus
                autocomplete="name" :error="form.errors.name" />

            <BaseInput id="email" v-model="form.email" label="Email" type="email" class="mt-4" required
                autocomplete="username" :error="form.errors.email" />

            <BaseInput id="password" v-model="form.password" label="Password" type="password" class="mt-4" required
                autocomplete="new-password" :error="form.errors.password" />

            <BaseInput id="password_confirmation" v-model="form.password_confirmation" label="Confirm Password"
                type="password" class="mt-4" required autocomplete="new-password"
                :error="form.errors.password_confirmation" />

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <BaseCheckbox id="terms" v-model="form.terms" name="terms" required
                    :error="form.errors.terms">
                    <template #default>
                        I agree to the <a target="_blank" :href="route('terms.show')" class="rounded-lg text-sm text-brand underline transition hover:text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="rounded-lg text-sm text-brand underline transition hover:text-brand-dark focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 dark:focus:ring-offset-dark-bg">Privacy Policy</a>
                    </template>
                </BaseCheckbox>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')" class="rounded-lg text-sm text-slate-600 underline transition hover:text-ink focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 dark:text-zinc-300 dark:hover:text-white dark:focus:ring-offset-dark-bg">
                    Already registered?
                </Link>

                <BaseButton type="submit" class="ms-4" :disabled="form.processing">
                    Register
                </BaseButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
