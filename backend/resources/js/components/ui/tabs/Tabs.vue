<script setup>
import { TabsRoot } from 'radix-vue';
const props = defineProps({
    defaultValue: { type: String, default: undefined },
    modelValue: { type: String, default: undefined },
    orientation: { type: String, default: 'horizontal' },
    dir: { type: String, default: undefined },
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <TabsRoot v-bind="props" @update:model-value="emit('update:modelValue', $event)">
        <slot />
    </TabsRoot>
</template>
