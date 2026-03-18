<script setup>
import { cn } from '@/lib/utils';
const props = defineProps({
    class: { type: [String, Array, Object], default: '' },
    size: { type: String, default: 'default' },
});

const sizeClasses = {
    xs: 'h-6 w-6 text-xs',
    sm: 'h-8 w-8 text-xs',
    default: 'h-10 w-10 text-sm',
    lg: 'h-12 w-12 text-base',
    xl: 'h-16 w-16 text-lg',
};
</script>

<template>
    <span :class="cn('relative flex shrink-0 overflow-hidden rounded-full', sizeClasses[size], props.class)">
        <slot />
    </span>
</template>
