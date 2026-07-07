<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import BaseButton from '@/Components/UI/Base/BaseButton.vue';
import BaseInput from '@/Components/UI/Base/BaseInput.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Password" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <BaseInput id="email" v-model="form.email" label="Email" type="email" required autofocus
                autocomplete="username" :error="form.errors.email" />

            <BaseInput id="password" v-model="form.password" label="Password" type="password" class="mt-4" required
                autocomplete="new-password" :error="form.errors.password" />

            <BaseInput id="password_confirmation" v-model="form.password_confirmation" label="Confirm Password"
                type="password" class="mt-4" required autocomplete="new-password"
                :error="form.errors.password_confirmation" />

            <div class="flex items-center justify-end mt-4">
                <BaseButton type="submit" :disabled="form.processing">
                    Reset Password
                </BaseButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
