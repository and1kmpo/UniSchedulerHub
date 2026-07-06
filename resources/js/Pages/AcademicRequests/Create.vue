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

defineProps({
    typeOptions: {
        type: Array,
        default: () => [],
    },
});

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
        title="New Academic Request"
        subtitle="Submit a reviewed academic petition instead of forcing a direct system change"
    >
        <template #actions>
            <BaseButton
                as="a"
                variant="secondary"
                :href="route('academic-requests.index')"
            >
                <i class="fa-solid fa-arrow-left mr-2" />
                Academic Requests
            </BaseButton>
        </template>

        <CrudContainer>
            <SectionCard>
                <form class="space-y-6 p-6" @submit.prevent="submit">
                    <ContextHelp
                        title="Before submitting"
                        description="Use this flow for exceptions that need human review, such as a late withdrawal, group change, enrollment exception or grade review. Immediate enrollment actions still happen from Subject Enrollment."
                        icon="fa-solid fa-circle-info"
                    />

                    <div class="grid gap-5 md:grid-cols-2">
                        <BaseSelect
                            v-model="form.type"
                            label="Request type"
                            required
                            :options="typeOptions"
                            placeholder="Select request type"
                            :error="form.errors.type"
                        />

                        <BaseInput
                            v-model="form.title"
                            label="Title"
                            required
                            placeholder="Short request summary"
                            :error="form.errors.title"
                        />
                    </div>

                    <BaseTextarea
                        v-model="form.description"
                        label="Request details"
                        required
                        :rows="7"
                        placeholder="Explain the academic context, expected outcome and any relevant subject, group or period."
                        :error="form.errors.description"
                    />

                    <div class="flex flex-col-reverse gap-3 border-t border-border-light pt-5 dark:border-border-dark sm:flex-row sm:justify-end">
                        <BaseButton
                            as="a"
                            variant="secondary"
                            :href="route('academic-requests.index')"
                        >
                            Cancel
                        </BaseButton>

                        <BaseButton
                            type="submit"
                            :disabled="form.processing"
                        >
                            <i class="fa-solid fa-paper-plane mr-2" />
                            Submit request
                        </BaseButton>
                    </div>
                </form>
            </SectionCard>
        </CrudContainer>
    </CrudPageLayout>
</template>
