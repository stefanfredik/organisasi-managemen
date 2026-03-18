<script setup>
import { CheckboxIndicator, CheckboxRoot } from 'radix-vue';
import { Check, Minus } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    checked: { type: [Boolean, String], default: false },
    defaultChecked: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    id: { type: String, default: undefined },
    class: { type: [String, Array, Object], default: '' },
});
const emit = defineEmits(['update:checked']);
</script>

<template>
    <CheckboxRoot
        :id="id"
        :checked="checked"
        :default-checked="defaultChecked"
        :disabled="disabled"
        :class="cn(
            'peer h-4 w-4 shrink-0 rounded-sm border border-primary ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground',
            props.class,
        )"
        @update:checked="emit('update:checked', $event)"
    >
        <CheckboxIndicator class="flex items-center justify-center text-current">
            <Check v-if="checked === true" class="h-4 w-4" />
            <Minus v-if="checked === 'indeterminate'" class="h-4 w-4" />
        </CheckboxIndicator>
    </CheckboxRoot>
</template>
