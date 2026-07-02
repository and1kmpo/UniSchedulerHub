<script setup>
import { nextTick, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import BaseButton from '@/Components/UI/Base/BaseButton.vue';
import BaseInput from '@/Components/UI/Base/BaseInput.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value ^= true;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value.focus();
        form.code = '';
    } else {
        codeInput.value.focus();
        form.recovery_code = '';
    }
};

const submit = () => {
    form.post(route('two-factor.login'));
};
</script>

<template>
    <Head title="Two-factor Confirmation" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm text-slate-600 dark:text-zinc-300">
            <template v-if="! recovery">
                Please confirm access to your account by entering the authentication code provided by your authenticator application.
            </template>

            <template v-else>
                Please confirm access to your account by entering one of your emergency recovery codes.
            </template>
        </div>

        <form @submit.prevent="submit">
            <BaseInput
                v-if="! recovery"
                id="code"
                ref="codeInput"
                v-model="form.code"
                label="Code"
                type="text"
                inputmode="numeric"
                autofocus
                autocomplete="one-time-code"
                :error="form.errors.code"
            />

            <BaseInput
                v-else
                id="recovery_code"
                ref="recoveryCodeInput"
                v-model="form.recovery_code"
                label="Recovery Code"
                type="text"
                autocomplete="one-time-code"
                :error="form.errors.recovery_code"
            />

            <div class="flex items-center justify-end mt-4">
                <button type="button" class="cursor-pointer rounded-lg text-sm text-slate-600 underline transition hover:text-ink focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 dark:text-zinc-300 dark:hover:text-white dark:focus:ring-offset-dark-bg" @click.prevent="toggleRecovery">
                    <template v-if="! recovery">
                        Use a recovery code
                    </template>

                    <template v-else>
                        Use an authentication code
                    </template>
                </button>

                <BaseButton type="submit" class="ms-4" :disabled="form.processing">
                    Log in
                </BaseButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
