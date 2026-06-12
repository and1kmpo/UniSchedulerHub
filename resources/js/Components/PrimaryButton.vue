<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type"
        class="inline-flex items-center px-4 py-2 bg-[#2563EB] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1D4ED8] focus:bg-[#1D4ED8] active:bg-[#1D4ED8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2 dark:focus:ring-offset-[#09090B] transition ease-in-out duration-150">
        <slot />
    </button>
</template>
