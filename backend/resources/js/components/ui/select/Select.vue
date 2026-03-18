<script setup>
import { SelectRoot } from 'radix-vue';

const props = defineProps({
    modelValue: { type: String, default: undefined },
    defaultValue: { type: String, default: undefined },
    open: { type: Boolean, default: undefined },
    defaultOpen: { type: Boolean, default: false },
    dir: { type: String, default: undefined },
    name: { type: String, default: undefined },
    disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue', 'update:open']);
</script>

<template>
    <SelectRoot v-bind="props" @update:model-value="emit('update:modelValue', $event)" @update:open="emit('update:open', $event)">
        <slot />
    </SelectRoot>
</template>
