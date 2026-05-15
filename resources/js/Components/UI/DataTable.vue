<script setup>
defineProps({
    columns: {
        type: Array,
        required: true,
    },
    rows: {
        type: Array,
        required: true,
    },
    rowKey: {
        type: String,
        default: "id",
    },
});
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/80">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        class="whitespace-nowrap px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 sm:px-6 sm:py-4"
                    >
                        {{ column.label }}
                    </th>

                    <th
                        v-if="$slots.actions"
                        class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200 sm:px-6 sm:py-4"
                    >
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
                <tr
                    v-for="(row, index) in rows"
                    :key="row[rowKey] ?? index"
                    class="transition hover:bg-gray-50 dark:hover:bg-gray-800/70"
                >
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        class="max-w-[18rem] px-4 py-4 align-middle text-gray-700 dark:text-gray-300 sm:px-6"
                    >
                        <slot
                            :name="`cell-${column.key}`"
                            :row="row"
                            :value="row[column.key]"
                            :column="column"
                        >
                            {{ row[column.key] ?? "-" }}
                        </slot>
                    </td>

                    <td
                        v-if="$slots.actions"
                        class="px-4 py-4 text-center align-middle sm:px-6"
                    >
                        <slot name="actions" :row="row" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
