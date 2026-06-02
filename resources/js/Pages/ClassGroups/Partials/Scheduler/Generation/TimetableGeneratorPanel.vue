<script setup>
import axios from "axios";
import { ref } from "vue";

const generating = ref(false);

const result = ref(null);

const generate = async () => {

    generating.value = true;

    try {

        const response = await axios.post(
            route("smart-scheduler.generate"),
            {
                subjects: [
                    { id: 1 },
                    { id: 2 },
                ],
            }
        );

        result.value = response.data;

    } finally {

        generating.value = false;

    }
};
</script>

<template>

    <div class="bg-white rounded-xl p-6 shadow">

        <button @click="generate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
            Generate Timetable
        </button>

        <div v-if="result" class="mt-6">

            <pre class="text-xs">
{{ result }}
            </pre>

        </div>

    </div>

</template>