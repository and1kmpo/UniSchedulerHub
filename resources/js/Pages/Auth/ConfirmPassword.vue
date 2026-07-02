<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import BaseButton from '@/Components/UI/Base/BaseButton.vue';
import BaseInput from '@/Components/UI/Base/BaseInput.vue';

const form = useForm({
    password: '',
});

const passwordInput = ref(null);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();

            passwordInput.value.focus();
        },
    });
};
</script>

<template>
    <Head title="Secure Area" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm text-slate-600 dark:text-zinc-300">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <form @submit.prevent="submit">
            <BaseInput
                id="password"
                ref="passwordInput"
                v-model="form.password"
                label="Password"
                type="password"
                required
                autocomplete="current-password"
                autofocus
                :error="form.errors.password"
            />

            <div class="flex justify-end mt-4">
                <BaseButton type="submit" class="ms-4" :disabled="form.processing">
                    Confirm
                </BaseButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
