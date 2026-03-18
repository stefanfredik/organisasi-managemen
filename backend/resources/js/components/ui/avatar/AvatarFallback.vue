<script setup>
import { cn } from '@/lib/utils';
const props = defineProps({ class: { type: [String, Array, Object], default: '' } });
</script>

<template>
    <span :class="cn('flex h-full w-full items-center justify-center rounded-full bg-muted font-medium', props.class)">
        <slot />
    </span>
</template>
