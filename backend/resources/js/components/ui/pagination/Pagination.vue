<script setup>
import { PaginationRoot } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    page: { type: Number, default: 1 },
    total: { type: Number, default: 0 },
    itemsPerPage: { type: Number, default: 10 },
    siblingCount: { type: Number, default: 1 },
    showEdges: { type: Boolean, default: true },
    defaultPage: { type: Number, default: 1 },
    class: { type: [String, Array, Object], default: '' },
});
const emit = defineEmits(['update:page']);
</script>

<template>
    <PaginationRoot
        v-bind="{ ...props, class: undefined }"
        :class="cn('mx-auto flex w-full justify-center', props.class)"
        @update:page="emit('update:page', $event)"
    >
        <slot />
    </PaginationRoot>
</template>
