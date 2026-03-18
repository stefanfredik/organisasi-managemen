<script setup>
import { TooltipRoot } from 'radix-vue';
const props = defineProps({
    delayDuration: { type: Number, default: 300 },
});
</script>

<template>
    <TooltipRoot v-bind="props">
        <slot />
    </TooltipRoot>
</template>
