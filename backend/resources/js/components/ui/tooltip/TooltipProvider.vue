<script setup>
import { TooltipProvider } from 'radix-vue';
const props = defineProps({
    delayDuration: { type: Number, default: 400 },
    skipDelayDuration: { type: Number, default: 300 },
});
</script>

<template>
    <TooltipProvider v-bind="props">
        <slot />
    </TooltipProvider>
</template>
