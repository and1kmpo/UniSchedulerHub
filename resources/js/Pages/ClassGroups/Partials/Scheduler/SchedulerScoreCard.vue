<script setup>
defineProps({
    score: Object,
});
</script>

<template>

    <div class="rounded-2xl border bg-white p-6 shadow-sm">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-sm text-gray-500">
                    Optimization Score
                </p>

                <h2 class="text-4xl font-bold">
                    {{ score.score }}
                </h2>

            </div>

            <div>

                <span class="rounded-full bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700">
                    {{ score.grade }}
                </span>

            </div>

        </div>

        <div class="mt-4 space-y-2">

            <div v-for="penalty in score.penalties" :key="penalty.message"
                class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                {{ penalty.message }}
            </div>

        </div>

    </div>

</template>