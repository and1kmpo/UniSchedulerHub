<script setup>
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import SortIcon from "@/Components/UI/Table/SortIcon.vue";

const props = defineProps({
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

    sortable: {
        type: Boolean,
        default: false,
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
});

const sortBy = (column) => {

    if (!column.sortable) return;

    const currentSort = props.filters?.sort;
    const currentDirection = props.filters?.direction;

    let direction = "asc";

    if (
        currentSort === column.key &&
        currentDirection === "asc"
    ) {
        direction = "desc";
    }

    router.get(
        route(route().current()),
        {
            ...props.filters,
            sort: column.key,
            direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const columnKey = (column) => String(column.key || "").toLowerCase();

const cellClass = (column) => {
    const key = columnKey(column);

    if (column.cellClass) return column.cellClass;

    if (["student_id", "studentid", "document", "code", "id"].includes(key) || key.endsWith("_code")) {
        return "font-mono text-slate-500 dark:text-slate-400";
    }

    if (["name", "student", "professor", "subject"].includes(key)) {
        return "font-sans font-medium text-ink dark:text-white";
    }

    if (["program", "period", "group", "group_code"].includes(key)) {
        return "font-mono text-slate-600 dark:text-slate-300";
    }

    if (["cgpa", "average", "promedio", "grade", "final_grade"].includes(key)) {
        return "font-mono text-accent dark:text-accent";
    }

    return "text-slate-700 dark:text-zinc-300";
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-border-light bg-slate-50 dark:border-border-dark dark:bg-zinc-900">
                <tr>
                    <th v-for="column in columns" :key="column.key" @click="sortBy(column)" :class="[
                        'whitespace-nowrap px-4 py-3 text-left font-sans text-xs font-semibold uppercase tracking-wider',
                        column.sortable
                            ? 'cursor-pointer select-none text-slate-500 hover:text-brand-600 dark:text-slate-500 dark:hover:text-brand-300'
                            : 'text-slate-500 dark:text-slate-500'
                    ]">

                        <div class="flex items-center gap-2">

                            <span>
                                {{ column.label }}
                            </span>

                            <SortIcon v-if="column.sortable" :active="filters?.sort === column.key"
                                :direction="filters?.direction" />

                        </div>

                    </th>

                    <th v-if="$slots.actions"
                        class="whitespace-nowrap px-4 py-3 text-center font-sans text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-500">
                        Actions
                    </th>

                </tr>
            </thead>

            <tbody class="bg-surface dark:bg-surface-dark">
                <tr v-for="(row, index) in rows" :key="row[rowKey] ?? index"
                    class="border-b border-border-light transition last:border-b-0 hover:bg-slate-50 dark:border-border-dark dark:hover:bg-zinc-900/70">
                    <td v-for="column in columns" :key="column.key"
                        :class="['max-w-[18rem] px-4 py-4 align-middle', cellClass(column)]">
                        <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]" :column="column">
                            {{ row[column.key] ?? "-" }}
                        </slot>
                    </td>

                    <td v-if="$slots.actions" class="px-4 py-4 text-center align-middle">
                        <slot name="actions" :row="row" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
