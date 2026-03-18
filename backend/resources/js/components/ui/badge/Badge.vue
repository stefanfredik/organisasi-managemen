<script setup>
import { cn } from '@/lib/utils';
import { badgeVariants } from './badgeVariants';

const props = defineProps({
    variant: { type: String, default: 'default' },
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <div :class="cn(badgeVariants({ variant }), props.class)">
        <slot />
    </div>
</template>
