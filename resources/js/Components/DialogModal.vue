<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="px-6 py-4">
            <div class="text-lg font-semibold text-ink dark:text-white">
                <slot name="title" />
            </div>

            <div class="mt-4 text-sm text-slate-600 dark:text-zinc-300">
                <slot name="content" />
            </div>
        </div>

        <div class="flex flex-row justify-end border-t border-border-light bg-slate-50 px-6 py-4 text-end dark:border-border-dark dark:bg-zinc-950">
            <slot name="footer" />
        </div>
    </Modal>
</template>
