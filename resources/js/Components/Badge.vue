<script setup>
defineProps({
    color: {
        type: String,
        default: 'gray',
    },
    text: {
        type: String,
        required: true,
    },
})
</script>

<template>
    <span :class="[
        'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium capitalize',
        {
            'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': color === 'green',
            'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100': color === 'red',
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100': color === 'yellow',
            'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100': color === 'blue',
            'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100': color === 'purple',
            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100': color === 'gray',
        }
    ]">
        {{ text }}
    </span>
</template>
