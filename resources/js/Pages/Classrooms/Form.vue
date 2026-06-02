<script setup>
import { computed, ref, watch } from "vue";
import axios from "axios";
import { route } from "ziggy-js";

import FormSection from "@/Components/UI/Forms/FormSection.vue";
import FormGrid from "@/Components/UI/Forms/FormGrid.vue";
import FormActions from "@/Components/UI/Forms/FormActions.vue";
import BaseButton from "@/Components/UI/Base/BaseButton.vue";
import BaseInput from "@/Components/UI/Base/BaseInput.vue";
import BaseSelect from "@/Components/UI/Base/BaseSelect.vue";
import BaseTextarea from "@/Components/UI/Base/BaseTextarea.vue";

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },

    buildings: {
        type: Array,
        default: () => [],
    },

    updating: {
        type: Boolean,
        default: false,
    },

    processing: {
        type: Boolean,
        default: false,
    },

    handleCancel: {
        type: Function,
        required: true,
    },
});

defineEmits(["submit"]);

const previewName = ref("");

const buildingOptions = computed(() =>
    props.buildings.map((building) => ({
        label: `${building.code} - ${building.name}`,
        value: building.id,
    }))
);

const statusOptions = [
    { label: "Active", value: "active" },
    { label: "Inactive", value: "inactive" },
];

watch(
    () => [props.form.building_id, props.form.floor],
    async ([buildingId, floor]) => {
        if (props.updating || !buildingId || floor === "") {
            previewName.value = "";

            return;
        }

        try {
            const response = await axios.get(route("classrooms.preview"), {
                params: {
                    building_id: buildingId,
                    floor,
                },
            });

            previewName.value = response.data.name;
        } catch {
            previewName.value = "";
        }
    },
    {
        immediate: true,
    }
);
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="overflow-hidden rounded-2xl bg-white shadow dark:bg-gray-900">
        <FormSection :title="updating ? 'Update Classroom' : 'Create Classroom'" :description="updating
            ? 'Update classroom capacity, status and metadata.'
            : 'Create a classroom using the building and floor naming convention.'
            ">
            <FormGrid :cols="2">
                <BaseSelect v-model="form.building_id" label="Building" placeholder="Select building"
                    :options="buildingOptions" :error="form.errors.building_id" required />

                <BaseInput v-model="form.floor" type="number" label="Floor" placeholder="2"
                    :error="form.errors.floor" required />

                <BaseInput v-model="form.capacity" type="number" label="Capacity" placeholder="35"
                    :error="form.errors.capacity" required />

                <BaseSelect v-model="form.status" label="Status" :options="statusOptions" :error="form.errors.status"
                    required />

                <div class="md:col-span-2">
                    <BaseTextarea v-model="form.description" label="Description" rows="4"
                        placeholder="Room equipment, accessibility notes, or scheduling constraints..."
                        :error="form.errors.description" />
                </div>
            </FormGrid>

            <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                    Classroom name
                </p>

                <p class="mt-1 font-mono text-lg font-semibold text-indigo-700 dark:text-indigo-300">
                    {{ updating ? form.name : previewName || "Select building and floor" }}
                </p>
            </div>
        </FormSection>

        <FormActions>
            <BaseButton type="button" variant="secondary" @click="handleCancel">
                Cancel
            </BaseButton>

            <BaseButton type="submit" variant="primary" :disabled="processing">
                <i v-if="processing" class="fa-solid fa-spinner fa-spin mr-2" />

                {{ updating ? "Update Classroom" : "Create Classroom" }}
            </BaseButton>
        </FormActions>
    </form>
</template>
