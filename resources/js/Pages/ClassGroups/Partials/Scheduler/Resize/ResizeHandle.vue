<script setup>
defineProps({
    active: Boolean,
});
</script>

<template>

    <div class="
            absolute
            bottom-0
            left-0
            right-0
            flex
            h-3
            cursor-ns-resize
            items-center
            justify-center
        ">

        <div class="
                h-1.5
                w-10
                rounded-full
                transition
            " :class="active
                    ? 'bg-indigo-600'
                    : 'bg-gray-400'
                " />

    </div>

</template>