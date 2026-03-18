<script setup>
import { DropdownMenuRoot } from 'radix-vue';
const props = defineProps({
    open: { type: Boolean, default: undefined },
    defaultOpen: { type: Boolean, default: false },
    dir: { type: String, default: undefined },
    modal: { type: Boolean, default: true },
});
const emit = defineEmits(['update:open']);
</script>

<template>
    <DropdownMenuRoot v-bind="props" @update:open="emit('update:open', $event)">
        <slot />
    </DropdownMenuRoot>
</template>
