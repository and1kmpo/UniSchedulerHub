<script setup>
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import CrudContainer from "@/Layouts/CrudContainerLayout.vue";
import CrudPageLayout from "@/Layouts/CrudPageLayout.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseInput from "@/Components/UI/Base/BaseInput.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import BaseTextarea from "@/Components/UI/Base/BaseTextarea.vue";
import ContextHelp from "@/Components/UI/Feedback/ContextHelp.vue";
import SectionCard from "@/Components/UI/Layout/SectionCard.vue";
import { useTranslations } from "@/Components/Composables/useTranslations";

defineProps({
    typeOptions: {
        type: Array,
        default: () => [],
    },
});

const { t } = useTranslations();

const form = useForm({
    type: "",
    title: "",
    description: "",
});

function submit() {
    form.post(route("academic-requests.store"));
}
</script>

<template>
    <CrudPageLayout
        :title="t('academic_requests.create_title')"
        :subtitle="t('academic_requests.create_subtitle')"
    >
        <template #actions>
            <BaseButton
                as="a"
                variant="secondary"
                :href="route('academic-requests.index')"
            >
                <i class="fa-solid fa-arrow-left mr-2" />
                {{ t("academic_requests.back") }}
            </BaseButton>
        </template>

        <CrudContainer>
            <SectionCard>
                <form class="space-y-6 p-6" @submit.prevent="submit">
                    <ContextHelp
                        :title="t('academic_requests.before_title')"
                        :description="t('academic_requests.before_description')"
                        icon="fa-solid fa-circle-info"
                    />

                    <div class="grid gap-5 md:grid-cols-2">
                        <BaseSelect
                            v-model="form.type"
                            :label="t('academic_requests.request_type')"
                            required
                            :options="typeOptions"
                            :placeholder="t('academic_requests.select_type')"
                            :error="form.errors.type"
                        />

                        <BaseInput
                            v-model="form.title"
                            :label="t('academic_requests.form_title')"
                            required
                            :placeholder="t('academic_requests.title_placeholder')"
                            :error="form.errors.title"
                        />
                    </div>

                    <BaseTextarea
                        v-model="form.description"
                        :label="t('academic_requests.details')"
                        required
                        :rows="7"
                        :placeholder="t('academic_requests.details_placeholder')"
                        :error="form.errors.description"
                    />

                    <div class="flex flex-col-reverse gap-3 border-t border-border-light pt-5 dark:border-border-dark sm:flex-row sm:justify-end">
                        <BaseButton
                            as="a"
                            variant="secondary"
                            :href="route('academic-requests.index')"
                        >
                            {{ t("academic_requests.cancel") }}
                        </BaseButton>

                        <BaseButton
                            type="submit"
                            :disabled="form.processing"
                        >
                            <i class="fa-solid fa-paper-plane mr-2" />
                            {{ t("academic_requests.submit") }}
                        </BaseButton>
                    </div>
                </form>
            </SectionCard>
        </CrudContainer>
    </CrudPageLayout>
</template>
