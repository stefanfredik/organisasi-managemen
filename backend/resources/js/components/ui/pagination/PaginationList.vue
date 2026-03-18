<script setup>
import { PaginationList } from 'radix-vue';
import { cn } from '@/lib/utils';
const props = defineProps({ class: { type: [String, Array, Object], default: '' } });
</script>

<template>
    <PaginationList v-slot="slotProps" :class="cn('flex flex-row items-center gap-1', props.class)">
        <slot v-bind="slotProps" />
    </PaginationList>
</template>
